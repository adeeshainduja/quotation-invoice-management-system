<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        if ($request->filled('company_id')) {
            abort_if(! $user->hasCompanyAccess($request->integer('company_id')), 403, 'Unauthorized company access.');
        }

        $companyList = $user->isAdmin()
            ? DB::table('companies')
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get(['id', 'name'])
            : $user->accessibleCompanies()
                ->where('companies.status', 'ACTIVE')
                ->orderBy('name')
                ->get(['companies.id', 'companies.name']);

        $companyId = $request->integer('company_id')
            ?: optional($companyList->first())->id;

        /*
        |--------------------------------------------------------------------------
        | Company Currency
        |--------------------------------------------------------------------------
        */

        $currency = $companyId
            ? DB::table('companies')
                ->where('id', $companyId)
                ->value('currency') ?? 'LKR'
            : 'LKR';

        /*
        |--------------------------------------------------------------------------
        | Quotation Summary
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total' => 0,
            'draft' => 0,
            'sent' => 0,
            'accepted' => 0,
            'rejected' => 0,
            'expired' => 0,
            'converted' => 0,
        ];

        if ($companyId) {

            $base = DB::table('quotations')
                ->where('company_id', $companyId);

            $stats['total'] =
                (clone $base)
                    ->count();

            $stats['draft'] =
                (clone $base)
                    ->where('status', 'DRAFT')
                    ->count();

            $stats['sent'] =
                (clone $base)
                    ->where('status', 'SENT')
                    ->count();

            $stats['accepted'] =
                (clone $base)
                    ->where('status', 'ACCEPTED')
                    ->count();

            $stats['rejected'] =
                (clone $base)
                    ->where('status', 'REJECTED')
                    ->count();

            $stats['expired'] =
                (clone $base)
                    ->where('status', 'EXPIRED')
                    ->count();

            $stats['converted'] =
                (clone $base)
                    ->where('status', 'CONVERTED')
                    ->count();
        }

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        $customerList = $companyId
            ? DB::table('customers')
                ->where('company_id', $companyId)
                ->where('status', 'ACTIVE')
                ->orderBy('business_name')
                ->get()
            : collect();

        /*
        |--------------------------------------------------------------------------
        | Quotations
        |--------------------------------------------------------------------------
        */

        $quotations = DB::table('quotations')
            ->join(
                'customers',
                'quotations.customer_id',
                '=',
                'customers.id'
            )

            ->when(
                $companyId,
                fn ($q) => $q->where(
                    'quotations.company_id',
                    $companyId
                ),
                fn ($q) => $q->whereRaw('1=0')
            )

            ->when(
                $request->search,
                fn ($q, $value) => $q->where(
                    'quotation_number',
                    'like',
                    "%{$value}%"
                )
            )

            ->when(
                $request->status,
                fn ($q, $value) => $q->where(
                    'quotations.status',
                    $value
                )
            )

            ->when(
                $request->customer_id,
                fn ($q, $value) => $q->where(
                    'customer_id',
                    $value
                )
            )

            ->select(
                'quotations.*',
                'customers.business_name'
            )

            ->orderByDesc('quotation_date')

            ->paginate(10)

            ->withQueryString();

        return view('quotations.index', compact(
            'companyList',
            'companyId',
            'currency',
            'stats',
            'customerList',
            'quotations'
        ));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        if ($request->filled('company_id')) {
            abort_if(! $user->hasCompanyAccess($request->integer('company_id')), 403, 'Unauthorized company access.');
        }

        $companyList = $user->isAdmin()
            ? DB::table('companies')
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get(['id', 'name'])
            : $user->accessibleCompanies()
                ->where('companies.status', 'ACTIVE')
                ->orderBy('name')
                ->get(['companies.id', 'companies.name']);

        $companyId = $request->integer('company_id')
            ?: optional($companyList->first())->id;

        $company = null;
        $customers = collect();
        $templates = collect();
        $quotationNumber = '';

        if ($companyId) {

            $company = DB::table('companies')
                ->where('id', $companyId)
                ->where('status', 'ACTIVE')
                ->first();

            if ($company) {

                $customers = DB::table('customers')
                    ->where('company_id', $companyId)
                    ->where('status', 'ACTIVE')
                    ->orderBy('business_name')
                    ->get();

                $templates = DB::table('company_templates')
                    ->where('company_id', $companyId)
                    ->where('document_type', 'QUOTATION')
                    ->orderByDesc('is_default')
                    ->orderBy('template_name')
                    ->get();

                $quotationNumber =
                    rtrim(
                        $company->quotation_prefix,
                        '-'
                    )
                    .'-'
                    .now()->format('Y')
                    .'-'
                    .str_pad(
                        $company->quotation_next_number,
                        4,
                        '0',
                        STR_PAD_LEFT
                    );
            }
        }

        return view('quotations.create', compact(
            'companyList',
            'companyId',
            'company',
            'customers',
            'templates',
            'quotationNumber'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([

            'company_id' => 'required|exists:companies,id',

            'customer_id' => 'required|exists:customers,id',

            'template_id' => 'required|exists:company_templates,id',

            'quotation_date' => 'required|date',

            'expiry_date' => 'nullable|date|after_or_equal:quotation_date',

            'reference' => 'nullable|string|max:255',

            'notes' => 'nullable|string',

            'terms_conditions' => 'nullable|string',

            'items' => 'required|array|min:1',

            'items.*.item_name' => 'required|string|max:255',

            'items.*.description' => 'nullable|string',

            'items.*.quantity' => 'required|numeric|gt:0',

            'items.*.unit_price' => 'required|numeric|min:0',

            'items.*.discount' => 'nullable|numeric|min:0|max:100',

            'items.*.tax' => 'nullable|numeric|min:0|max:100',
        ]);

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($data['company_id']), 403, 'Unauthorized company access.');

        $companyCheck = DB::table('companies')->where('id', $data['company_id'])->first();
        abort_if(! $companyCheck || $companyCheck->status !== 'ACTIVE', 403, 'Cannot create transactions for an inactive company.');

        abort_if(! $user->isAdmin() && ! $user->hasTemplateAccess($data['template_id']), 403, 'Unauthorized template access.');

        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            $company = DB::table('companies')
                ->where(
                    'id',
                    $data['company_id']
                )
                ->where(
                    'status',
                    'ACTIVE'
                )
                ->lockForUpdate()
                ->first();

            abort_if(! $company, 404);

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $customer = DB::table('customers')
                ->where(
                    'id',
                    $data['customer_id']
                )
                ->where(
                    'company_id',
                    $company->id
                )
                ->where(
                    'status',
                    'ACTIVE'
                )
                ->first();

            if (! $customer) {

                throw ValidationException::withMessages([
                    'customer_id' => 'Invalid customer.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Template
            |--------------------------------------------------------------------------
            */

            $template = DB::table('company_templates')
                ->where(
                    'id',
                    $data['template_id']
                )
                ->where(
                    'company_id',
                    $company->id
                )
                ->where(
                    'document_type',
                    'QUOTATION'
                )
                ->first();

            if (! $template) {

                throw ValidationException::withMessages([
                    'template_id' => 'Invalid quotation template.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Quotation Number
            |--------------------------------------------------------------------------
            */

            $number =
                rtrim(
                    $company->quotation_prefix,
                    '-'
                )
                .'-'
                .now()->format('Y')
                .'-'
                .str_pad(
                    $company->quotation_next_number,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            /*
            |--------------------------------------------------------------------------
            | Calculate Items
            |--------------------------------------------------------------------------
            */

            $isVatEnabled = (bool) ($company->vat_enabled ?? $company->vat_registered ?? false);
            $companyVatRate = $isVatEnabled ? (float) ($company->tax_percentage ?? $company->vat_percentage ?? 0) : 0;

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;

            $items = [];

            foreach ($data['items'] as $i => $item) {

                $qty =
                    (float) $item['quantity'];

                $price =
                    (float) $item['unit_price'];

                $discount =
                    (float) (
                        $item['discount'] ?? 0
                    );

                $tax = $isVatEnabled
                    ? (float) ($item['tax'] ?? $companyVatRate)
                    : 0;

                $lineSubtotal =
                    round(
                        $qty * $price,
                        2
                    );

                $discountAmount =
                    round(
                        $lineSubtotal
                        * $discount
                        / 100,
                        2
                    );

                $taxable =
                    $lineSubtotal
                    - $discountAmount;

                $taxAmount =
                    round(
                        $taxable
                        * $tax
                        / 100,
                        2
                    );

                $lineTotal =
                    round(
                        $taxable
                        + $taxAmount,
                        2
                    );

                $subtotal +=
                    $lineSubtotal;

                $discountTotal +=
                    $discountAmount;

                $taxTotal +=
                    $taxAmount;

                $items[] = [

                    'sort_order' => $i + 1,

                    'item_name' => $item['item_name'],

                    'description' => $item['description'] ?? null,

                    'quantity' => $qty,

                    'unit' => null,

                    'unit_price' => $price,

                    'discount_type' => $discount > 0
                            ? 'PERCENTAGE'
                            : 'NONE',

                    'discount_value' => $discount,

                    'discount_amount' => $discountAmount,

                    'tax_percentage' => $tax,

                    'tax_amount' => $taxAmount,

                    'line_total' => $lineTotal,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Grand Total
            |--------------------------------------------------------------------------
            */

            $grandTotal = round(
                $subtotal
                - $discountTotal
                + $taxTotal,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Insert Quotation
            |--------------------------------------------------------------------------
            */

            $quotationId =
                DB::table('quotations')
                    ->insertGetId([

                        'company_id' => $company->id,

                        'customer_id' => $customer->id,

                        'quotation_number' => $number,

                        'quotation_date' => $data['quotation_date'],

                        'expiry_date' => $data['expiry_date'] ?? null,

                        'reference' => $data['reference'] ?? null,

                        'subtotal' => $subtotal,

                        'discount_type' => $discountTotal > 0
                                ? 'FIXED'
                                : 'NONE',

                        'discount_value' => $discountTotal,

                        'discount_amount' => $discountTotal,

                        'tax_percentage' => $isVatEnabled ? $companyVatRate : 0,

                        'tax_amount' => $taxTotal,

                        'vat_enabled' => $isVatEnabled,

                        'vat_percentage' => $isVatEnabled ? $companyVatRate : 0,

                        'vat_amount' => $taxTotal,

                        'additional_charges' => 0,

                        'grand_total' => $grandTotal,

                        'status' => 'DRAFT',

                        'notes' => $data['notes'] ?? null,

                        'terms_conditions' => $data['terms_conditions'] ?? null,

                        'template_id' => $template->id,

                        'company_snapshot' => json_encode(
                            $company
                        ),

                        'customer_snapshot' => json_encode(
                            $customer
                        ),

                        'template_snapshot' => json_encode(
                            $template
                        ),

                        'created_by' => auth()->id(),

                        'created_at' => now(),

                        'updated_at' => now(),
                    ]);

            /*
            |--------------------------------------------------------------------------
            | Insert Items
            |--------------------------------------------------------------------------
            */

            foreach ($items as $item) {

                DB::table('quotation_items')
                    ->insert([

                        'quotation_id' => $quotationId,

                        ...$item,

                        'created_at' => now(),

                        'updated_at' => now(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Increment Quotation Number
            |--------------------------------------------------------------------------
            */

            DB::table('companies')
                ->where(
                    'id',
                    $company->id
                )
                ->update([

                    'quotation_next_number' => $company->quotation_next_number + 1,
                ]);

            ActivityLogger::log(
                'CREATE',
                'Quotation',
                $quotationId,
                $company->id,
                null,
                [
                    'quotation_number' => $number,
                    'grand_total' => $grandTotal,
                    'status' => 'DRAFT',
                ]
            );

            return redirect()
                ->route(
                    'quotations.show',
                    $quotationId
                )
                ->with(
                    'success',
                    'Quotation created successfully.'
                );
        });
    }

    public function show($id)
    {
        $quotation = DB::table('quotations')
            ->leftJoin(
                'users',
                'quotations.created_by',
                '=',
                'users.id'
            )
            ->where(
                'quotations.id',
                $id
            )
            ->select(
                'quotations.*',
                'users.name as created_by_name'
            )
            ->first();

        abort_if(! $quotation, 404);

        $user = auth()->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($quotation->company_id), 403, 'Unauthorized company access.');

        $company = DB::table('companies')
            ->where(
                'id',
                $quotation->company_id
            )
            ->first();

        $customer = DB::table('customers')
            ->where(
                'id',
                $quotation->customer_id
            )
            ->first();

        if (! $customer && ! empty($quotation->customer_snapshot)) {
            $customer = json_decode($quotation->customer_snapshot);
        }
        if (! $company && ! empty($quotation->company_snapshot)) {
            $company = json_decode($quotation->company_snapshot);
        }

        $items = DB::table('quotation_items')
            ->where(
                'quotation_id',
                $id
            )
            ->orderBy('sort_order')
            ->get();

        $template =
            DB::table('company_templates')
                ->where(
                    'id',
                    $quotation->template_id
                )
                ->first();

        if (! $template && ! empty($quotation->template_snapshot)) {
            $template = json_decode($quotation->template_snapshot);
        }

        $convertedInvoice = null;
        if (! empty($quotation->converted_invoice_id)) {
            $convertedInvoice = DB::table('invoices')
                ->where('id', $quotation->converted_invoice_id)
                ->first();
        }

        return view('quotations.show', compact(
            'quotation',
            'company',
            'customer',
            'items',
            'template',
            'convertedInvoice'
        ));
    }

    public function preview(Request $request)
    {
        $data = $request->validate([

            'company_id' => 'required|exists:companies,id',

            'customer_id' => 'required|exists:customers,id',

            'template_id' => 'required|exists:company_templates,id',

            'quotation_date' => 'required|date',

            'expiry_date' => 'nullable|date',

            'reference' => 'nullable|string',

            'notes' => 'nullable|string',

            'terms_conditions' => 'nullable|string',

            'items' => 'required|array|min:1',

            'items.*.item_name' => 'required|string',

            'items.*.description' => 'nullable|string',

            'items.*.quantity' => 'required|numeric',

            'items.*.unit_price' => 'required|numeric',

            'items.*.discount' => 'nullable|numeric',

            'items.*.tax' => 'nullable|numeric',
        ]);

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($data['company_id']), 403, 'Unauthorized company access.');

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company = DB::table('companies')
            ->where(
                'id',
                $data['company_id']
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        */

        $customer = DB::table('customers')
            ->where(
                'id',
                $data['customer_id']
            )
            ->where(
                'company_id',
                $data['company_id']
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Template
        |--------------------------------------------------------------------------
        */

        $template = DB::table('company_templates')
            ->where(
                'id',
                $data['template_id']
            )
            ->where(
                'company_id',
                $data['company_id']
            )
            ->where(
                'document_type',
                'QUOTATION'
            )
            ->first();

        abort_if(
            ! $company
            || ! $customer
            || ! $template,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Calculate Preview
        |--------------------------------------------------------------------------
        */

        $isVatEnabled = (bool) ($company->vat_enabled ?? $company->vat_registered ?? false);
        $companyVatRate = $isVatEnabled ? (float) ($company->tax_percentage ?? $company->vat_percentage ?? 0) : 0;

        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        $items = [];

        foreach ($data['items'] as $item) {

            $qty =
                (float) $item['quantity'];

            $price =
                (float) $item['unit_price'];

            $discount =
                (float) (
                    $item['discount'] ?? 0
                );

            $tax = $isVatEnabled
                ? (float) ($item['tax'] ?? $companyVatRate)
                : 0;

            $lineSubtotal =
                $qty * $price;

            $discountAmount =
                $lineSubtotal
                * $discount
                / 100;

            $taxable =
                $lineSubtotal
                - $discountAmount;

            $taxAmount =
                $taxable
                * $tax
                / 100;

            $lineTotal =
                $taxable
                + $taxAmount;

            $subtotal +=
                $lineSubtotal;

            $discountTotal +=
                $discountAmount;

            $taxTotal +=
                $taxAmount;

            $items[] = (object) [

                'item_name' => $item['item_name'],

                'description' => $item['description'] ?? '',

                'quantity' => $qty,

                'unit_price' => $price,

                'discount' => $discount,

                'tax' => $tax,

                'line_total' => $lineTotal,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Preview Object
        |--------------------------------------------------------------------------
        */

        $quotation = (object) [

            'quotation_number' => 'PREVIEW',

            'quotation_date' => $data['quotation_date'],

            'expiry_date' => $data['expiry_date'] ?? null,

            'reference' => $data['reference'] ?? null,

            'notes' => $data['notes'] ?? null,

            'terms_conditions' => $data['terms_conditions'] ?? null,

            'subtotal' => $subtotal,

            'discount_amount' => $discountTotal,

            'tax_amount' => $taxTotal,

            'vat_enabled' => $isVatEnabled,

            'vat_percentage' => $isVatEnabled ? $companyVatRate : 0,

            'vat_amount' => $taxTotal,

            'grand_total' => $subtotal
                - $discountTotal
                + $taxTotal,
        ];

        return view(
            'quotations.preview',
            compact(
                'company',
                'customer',
                'template',
                'quotation',
                'items'
            )
        );
    }

    public function downloadPdf($id)
    {
        $quotation = DB::table('quotations')
            ->where(
                'id',
                $id
            )
            ->first();

        abort_if(! $quotation, 404);

        $user = auth()->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($quotation->company_id), 403, 'Unauthorized company access.');

        $company = DB::table('companies')
            ->where(
                'id',
                $quotation->company_id
            )
            ->first();

        $customer = DB::table('customers')
            ->where(
                'id',
                $quotation->customer_id
            )
            ->first();

        $items = DB::table('quotation_items')
            ->where(
                'quotation_id',
                $id
            )
            ->orderBy('sort_order')
            ->get();

        $template = DB::table('company_templates')
            ->where(
                'id',
                $quotation->template_id
            )
            ->first();

        $bank = DB::table('company_bank_details')
            ->where(
                'company_id',
                $quotation->company_id
            )
            ->first();

        $isVatEnabled = isset($quotation->vat_enabled)
            ? (bool) $quotation->vat_enabled
            : ((float) ($quotation->vat_amount ?? $quotation->tax_amount ?? 0) > 0);
        $templateView = $isVatEnabled ? 'pdf.quotations.tax-quotation' : 'pdf.quotations.normal';

        if (! $customer && ! empty($quotation->customer_snapshot)) {
            $customer = json_decode($quotation->customer_snapshot);
        }
        if (! $company && ! empty($quotation->company_snapshot)) {
            $company = json_decode($quotation->company_snapshot);
        }
        if (! $template && ! empty($quotation->template_snapshot)) {
            $template = json_decode($quotation->template_snapshot);
        }

        $pdf = Pdf::loadView(
            $templateView,
            compact(
                'quotation',
                'company',
                'customer',
                'items',
                'template',
                'bank'
            )
        );

        return $pdf->download(
            $quotation->quotation_number
            .'.pdf'
        );
    }

    public function edit(Request $request, $id)
    {
        $quotation = DB::table('quotations')->where('id', $id)->first();
        abort_if(! $quotation, 404);

        if ($quotation->status === 'CONVERTED') {
            return redirect()
                ->route('quotations.show', $id)
                ->with('error', 'Cannot edit a quotation that has already been converted to an invoice.');
        }

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($quotation->company_id), 403, 'Unauthorized company access.');

        $companyId = $quotation->company_id;

        $companyList = $user->isAdmin()
            ? DB::table('companies')
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get(['id', 'name'])
            : $user->accessibleCompanies()
                ->where('companies.status', 'ACTIVE')
                ->orderBy('name')
                ->get(['companies.id', 'companies.name']);

        $company = DB::table('companies')->where('id', $companyId)->first();

        $customers = DB::table('customers')
            ->where('company_id', $companyId)
            ->where(function ($query) use ($quotation) {
                $query->where('status', 'ACTIVE')
                    ->orWhere('id', $quotation->customer_id);
            })
            ->orderBy('business_name')
            ->get();

        $templates = DB::table('company_templates')
            ->where('company_id', $companyId)
            ->where('document_type', 'QUOTATION')
            ->where(function ($query) use ($quotation) {
                $query->where('status', 'ACTIVE')
                    ->orWhere('id', $quotation->template_id);
            })
            ->orderBy('name')
            ->get();

        $items = DB::table('quotation_items')
            ->where('quotation_id', $id)
            ->orderBy('sort_order')
            ->get();

        return view('quotations.edit', compact(
            'quotation',
            'company',
            'companyList',
            'companyId',
            'customers',
            'templates',
            'items'
        ));
    }

    public function update(Request $request, $id)
    {
        $quotation = DB::table('quotations')->where('id', $id)->first();
        abort_if(! $quotation, 404);

        if ($quotation->status === 'CONVERTED') {
            return redirect()
                ->route('quotations.show', $id)
                ->with('error', 'Cannot edit a quotation that has already been converted to an invoice.');
        }

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($quotation->company_id), 403, 'Unauthorized company access.');

        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'template_id' => 'required|exists:company_templates,id',
            'quotation_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.tax' => 'nullable|numeric|min:0|max:100',
        ]);

        $company = DB::table('companies')->where('id', $quotation->company_id)->first();
        $customer = DB::table('customers')
            ->where('id', $data['customer_id'])
            ->where('company_id', $quotation->company_id)
            ->first();
        $template = DB::table('company_templates')
            ->where('id', $data['template_id'])
            ->where('company_id', $quotation->company_id)
            ->where('document_type', 'QUOTATION')
            ->first();

        abort_if(! $company || ! $customer || ! $template, 404);

        $isVatEnabled = (bool) ($company->vat_enabled ?? $company->vat_registered ?? false);
        $companyVatRate = $isVatEnabled ? (float) ($company->tax_percentage ?? $company->vat_percentage ?? 0) : 0;

        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;
        $items = [];

        foreach ($data['items'] as $i => $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['unit_price'];
            $discount = (float) ($item['discount'] ?? 0);
            $tax = $isVatEnabled ? (float) ($item['tax'] ?? $companyVatRate) : 0;

            $lineSubtotal = round($qty * $price, 2);
            $discountAmount = round($lineSubtotal * $discount / 100, 2);
            $taxable = $lineSubtotal - $discountAmount;
            $taxAmount = round($taxable * $tax / 100, 2);
            $lineTotal = round($taxable + $taxAmount, 2);

            $subtotal += $lineSubtotal;
            $discountTotal += $discountAmount;
            $taxTotal += $taxAmount;

            $items[] = [
                'sort_order' => $i + 1,
                'item_name' => $item['item_name'],
                'description' => $item['description'] ?? null,
                'quantity' => $qty,
                'unit' => null,
                'unit_price' => $price,
                'discount_type' => $discount > 0 ? 'PERCENTAGE' : 'NONE',
                'discount_value' => $discount,
                'discount_amount' => $discountAmount,
                'tax_percentage' => $tax,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal,
            ];
        }

        $grandTotal = round($subtotal - $discountTotal + $taxTotal, 2);

        DB::transaction(function () use ($id, $data, $customer, $template, $subtotal, $discountTotal, $taxTotal, $grandTotal, $isVatEnabled, $companyVatRate, $items, $quotation) {
            DB::table('quotations')
                ->where('id', $id)
                ->update([
                    'customer_id' => $customer->id,
                    'quotation_date' => $data['quotation_date'],
                    'expiry_date' => $data['expiry_date'] ?? null,
                    'reference' => $data['reference'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_type' => $discountTotal > 0 ? 'FIXED' : 'NONE',
                    'discount_value' => $discountTotal,
                    'discount_amount' => $discountTotal,
                    'tax_percentage' => $isVatEnabled ? $companyVatRate : 0,
                    'tax_amount' => $taxTotal,
                    'vat_enabled' => $isVatEnabled,
                    'vat_percentage' => $isVatEnabled ? $companyVatRate : 0,
                    'vat_amount' => $taxTotal,
                    'grand_total' => $grandTotal,
                    'notes' => $data['notes'] ?? null,
                    'terms_conditions' => $data['terms_conditions'] ?? null,
                    'template_id' => $template->id,
                    'customer_snapshot' => json_encode($customer, JSON_UNESCAPED_UNICODE),
                    'template_snapshot' => json_encode($template, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);

            DB::table('quotation_items')->where('quotation_id', $id)->delete();

            foreach ($items as $item) {
                DB::table('quotation_items')->insert([
                    'quotation_id' => $id,
                    ...$item,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            ActivityLogger::log(
                'UPDATE',
                'Quotation',
                $id,
                $quotation->company_id,
                ['grand_total' => $quotation->grand_total, 'customer_id' => $quotation->customer_id],
                ['grand_total' => $grandTotal, 'customer_id' => $customer->id]
            );
        });

        return redirect()
            ->route('quotations.show', $id)
            ->with('success', 'Quotation updated successfully.');
    }

    public function clone(Request $request, $id)
    {
        $quotation = DB::table('quotations')->where('id', $id)->first();
        abort_if(! $quotation, 404);

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($quotation->company_id), 403, 'Unauthorized company access.');

        $company = DB::table('companies')->where('id', $quotation->company_id)->first();
        abort_if(! $company, 404);

        $items = DB::table('quotation_items')
            ->where('quotation_id', $id)
            ->orderBy('sort_order')
            ->get();

        $newQuotationId = DB::transaction(function () use ($quotation, $company, $items) {
            $number = rtrim($company->quotation_prefix, '-')
                .'-'
                .now()->format('Y')
                .'-'
                .str_pad(
                    $company->quotation_next_number,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $newId = DB::table('quotations')->insertGetId([
                'company_id' => $quotation->company_id,
                'customer_id' => $quotation->customer_id,
                'quotation_number' => $number,
                'quotation_date' => now()->toDateString(),
                'expiry_date' => $quotation->expiry_date ? now()->addDays(30)->toDateString() : null,
                'reference' => null,
                'subtotal' => $quotation->subtotal,
                'discount_type' => $quotation->discount_type,
                'discount_value' => $quotation->discount_value,
                'discount_amount' => $quotation->discount_amount,
                'tax_percentage' => $quotation->tax_percentage,
                'tax_amount' => $quotation->tax_amount,
                'vat_enabled' => $quotation->vat_enabled,
                'vat_percentage' => $quotation->vat_percentage,
                'vat_amount' => $quotation->vat_amount,
                'additional_charges' => $quotation->additional_charges ?? 0,
                'grand_total' => $quotation->grand_total,
                'status' => 'DRAFT',
                'notes' => $quotation->notes,
                'terms_conditions' => $quotation->terms_conditions,
                'template_id' => $quotation->template_id,
                'company_snapshot' => $quotation->company_snapshot,
                'customer_snapshot' => $quotation->customer_snapshot,
                'template_snapshot' => $quotation->template_snapshot,
                'converted_invoice_id' => null,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $item) {
                DB::table('quotation_items')->insert([
                    'quotation_id' => $newId,
                    'sort_order' => $item->sort_order,
                    'item_name' => $item->item_name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'discount_type' => $item->discount_type,
                    'discount_value' => $item->discount_value,
                    'discount_amount' => $item->discount_amount,
                    'tax_percentage' => $item->tax_percentage,
                    'tax_amount' => $item->tax_amount,
                    'line_total' => $item->line_total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('companies')
                ->where('id', $company->id)
                ->update([
                    'quotation_next_number' => $company->quotation_next_number + 1,
                ]);

            ActivityLogger::log(
                'CLONE',
                'Quotation',
                $newId,
                $quotation->company_id,
                ['cloned_from_id' => $quotation->id, 'cloned_from_number' => $quotation->quotation_number],
                ['quotation_number' => $number, 'status' => 'DRAFT']
            );

            return $newId;
        });

        return redirect()
            ->route('quotations.show', $newQuotationId)
            ->with('success', 'Quotation cloned successfully as DRAFT.');
    }

    public function markAsSent(Request $request, $id)
    {
        $quotation = DB::table('quotations')->where('id', $id)->first();
        abort_if(! $quotation, 404);

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($quotation->company_id), 403, 'Unauthorized company access.');

        if ($quotation->status === 'CONVERTED') {
            return back()->with('error', 'Cannot change status of a converted quotation.');
        }

        DB::table('quotations')
            ->where('id', $id)
            ->update([
                'status' => 'SENT',
                'updated_at' => now(),
            ]);

        ActivityLogger::log(
            'SEND',
            'Quotation',
            $id,
            $quotation->company_id,
            ['status' => $quotation->status],
            ['status' => 'SENT']
        );

        return back()->with('success', 'Quotation marked as sent.');
    }

    public function accept(Request $request, $id)
    {
        $quotation = DB::table('quotations')->where('id', $id)->first();
        abort_if(! $quotation, 404);

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($quotation->company_id), 403, 'Unauthorized company access.');

        if ($quotation->status === 'CONVERTED') {
            return back()->with('error', 'Cannot change status of a converted quotation.');
        }

        DB::table('quotations')
            ->where('id', $id)
            ->update([
                'status' => 'ACCEPTED',
                'updated_at' => now(),
            ]);

        ActivityLogger::log(
            'ACCEPT',
            'Quotation',
            $id,
            $quotation->company_id,
            ['status' => $quotation->status],
            ['status' => 'ACCEPTED']
        );

        return redirect()
            ->route('invoices.create', ['quotation_id' => $id])
            ->with('success', 'Quotation accepted. Review and create the invoice below.');
    }

    public function reject(Request $request, $id)
    {
        $quotation = DB::table('quotations')->where('id', $id)->first();
        abort_if(! $quotation, 404);

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($quotation->company_id), 403, 'Unauthorized company access.');

        if ($quotation->status === 'CONVERTED') {
            return back()->with('error', 'Cannot change status of a converted quotation.');
        }

        DB::table('quotations')
            ->where('id', $id)
            ->update([
                'status' => 'REJECTED',
                'updated_at' => now(),
            ]);

        ActivityLogger::log(
            'REJECT',
            'Quotation',
            $id,
            $quotation->company_id,
            ['status' => $quotation->status],
            ['status' => 'REJECTED']
        );

        return back()->with('success', 'Quotation marked as rejected.');
    }

    public function convertToInvoice(Request $request, $id)
    {
        $quotation = DB::table('quotations')->where('id', $id)->first();
        abort_if(! $quotation, 404);

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($quotation->company_id), 403, 'Unauthorized company access.');

        if ($quotation->status !== 'ACCEPTED') {
            return redirect()
                ->route('quotations.show', $id)
                ->with('error', 'Only accepted quotations can be converted to invoices.');
        }

        if (! empty($quotation->converted_invoice_id)) {
            return redirect()
                ->route('invoices.show', $quotation->converted_invoice_id)
                ->with('info', 'This quotation has already been converted to an invoice.');
        }

        return redirect()
            ->route('invoices.create', ['quotation_id' => $id])
            ->with('success', 'Review and create the invoice below.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $companyList = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

        $companyId = $request->integer('company_id')
            ?: optional($companyList->first())->id;

        $customerList = $companyId
            ? DB::table('customers')
                ->where('company_id', $companyId)
                ->where('status', 'ACTIVE')
                ->orderBy('business_name')
                ->get()
            : collect();

        $quotations = DB::table('quotations')
            ->join(
                'customers',
                'quotations.customer_id',
                '=',
                'customers.id'
            )
            ->when(
                $companyId,
                fn ($q) =>
                    $q->where('quotations.company_id', $companyId),
                fn ($q) =>
                    $q->whereRaw('1 = 0')
            )
            ->when(
                $request->search,
                fn ($q, $search) =>
                    $q->where(
                        'quotations.quotation_number',
                        'like',
                        "%{$search}%"
                    )
            )
            ->when(
                $request->status,
                fn ($q, $status) =>
                    $q->where('quotations.status', $status)
            )
            ->when(
                $request->customer_id,
                fn ($q, $customerId) =>
                    $q->where(
                        'quotations.customer_id',
                        $customerId
                    )
            )
            ->when(
                $request->from_date,
                fn ($q, $date) =>
                    $q->whereDate(
                        'quotations.quotation_date',
                        '>=',
                        $date
                    )
            )
            ->when(
                $request->to_date,
                fn ($q, $date) =>
                    $q->whereDate(
                        'quotations.quotation_date',
                        '<=',
                        $date
                    )
            )
            ->select(
                'quotations.*',
                'customers.business_name'
            )
            ->orderByDesc('quotations.quotation_date')
            ->paginate(10)
            ->withQueryString();

        return view('quotations.index', compact(
            'companyList',
            'companyId',
            'customerList',
            'quotations'
        ));
    }


    public function create(Request $request)
    {
        $companyList = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

        /*
         * If no company is in URL,
         * automatically load first active company.
         */
        if (
            !$request->filled('company_id')
            && $companyList->isNotEmpty()
        ) {
            return redirect()->route('quotations.create', [
                'company_id' => $companyList->first()->id
            ]);
        }

        $companyId = $request->integer('company_id');

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
                    ->where('company_id', $company->id)
                    ->where('status', 'ACTIVE')
                    ->orderBy('business_name')
                    ->get();

                $templates = DB::table('company_templates')
                    ->where('company_id', $company->id)
                    ->where('document_type', 'QUOTATION')
                    ->orderByDesc('is_default')
                    ->orderBy('template_name')
                    ->get();

                $quotationNumber =
                    rtrim($company->quotation_prefix, '-')
                    . '-'
                    . now()->format('Y')
                    . '-'
                    . str_pad(
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


    public function preview(Request $request)
    {
        $data = $request->validate([
            'company_id' =>
                'required|exists:companies,id',

            'customer_id' =>
                'required|exists:customers,id',

            'template_id' =>
                'required|exists:company_templates,id',

            'quotation_date' =>
                'required|date',

            'expiry_date' =>
                'required|date|after_or_equal:quotation_date',

            'reference' =>
                'nullable|string|max:255',

            'notes' =>
                'nullable|string',

            'terms_conditions' =>
                'nullable|string',

            'items' =>
                'required|array|min:1',

            'items.*.item_name' =>
                'required|string|max:255',

            'items.*.description' =>
                'nullable|string',

            'items.*.quantity' =>
                'required|numeric|gt:0',

            'items.*.unit_price' =>
                'required|numeric|min:0',

            'items.*.discount' =>
                'nullable|numeric|min:0|max:100',

            'items.*.tax' =>
                'nullable|numeric|min:0|max:100',
        ]);

        $company = DB::table('companies')
            ->where('id', $data['company_id'])
            ->where('status', 'ACTIVE')
            ->first();

        if (!$company) {
            return back()
                ->withInput()
                ->withErrors([
                    'company_id' =>
                        'Selected company is not active.'
                ]);
        }

        $customer = DB::table('customers')
            ->where('id', $data['customer_id'])
            ->where('company_id', $company->id)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$customer) {
            return back()
                ->withInput()
                ->withErrors([
                    'customer_id' =>
                        'Selected customer is invalid.'
                ]);
        }

        $template = DB::table('company_templates')
            ->where('id', $data['template_id'])
            ->where('company_id', $company->id)
            ->where('document_type', 'QUOTATION')
            ->first();

        if (!$template) {
            return back()
                ->withInput()
                ->withErrors([
                    'template_id' =>
                        'Selected quotation template is invalid.'
                ]);
        }

        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        $items = [];

        foreach ($data['items'] as $item) {

            $quantity =
                (float) $item['quantity'];

            $unitPrice =
                (float) $item['unit_price'];

            $discount =
                (float) ($item['discount'] ?? 0);

            $tax =
                (float) ($item['tax'] ?? 0);

            $lineSubtotal = round(
                $quantity * $unitPrice,
                2
            );

            $discountAmount = round(
                $lineSubtotal * $discount / 100,
                2
            );

            $taxableAmount =
                $lineSubtotal - $discountAmount;

            $taxAmount = round(
                $taxableAmount * $tax / 100,
                2
            );

            $lineTotal = round(
                $taxableAmount + $taxAmount,
                2
            );

            $subtotal += $lineSubtotal;
            $discountTotal += $discountAmount;
            $taxTotal += $taxAmount;

            $items[] = (object) [
                'item_name' =>
                    $item['item_name'],

                'description' =>
                    $item['description'] ?? null,

                'quantity' =>
                    $quantity,

                'unit_price' =>
                    $unitPrice,

                'discount' =>
                    $discount,

                'tax' =>
                    $tax,

                'line_total' =>
                    $lineTotal,
            ];
        }

        $subtotal =
            round($subtotal, 2);

        $discountTotal =
            round($discountTotal, 2);

        $taxTotal =
            round($taxTotal, 2);

        $grandTotal = round(
            $subtotal
            - $discountTotal
            + $taxTotal,
            2
        );

        $quotationNumber =
            rtrim($company->quotation_prefix, '-')
            . '-'
            . now()->format('Y')
            . '-'
            . str_pad(
                $company->quotation_next_number,
                4,
                '0',
                STR_PAD_LEFT
            );

        $quotation = (object) [
            'quotation_number' =>
                $quotationNumber,

            'quotation_date' =>
                $data['quotation_date'],

            'expiry_date' =>
                $data['expiry_date'],

            'reference' =>
                $data['reference'] ?? null,

            'notes' =>
                $data['notes'] ?? null,

            'terms_conditions' =>
                $data['terms_conditions'] ?? null,

            'subtotal' =>
                $subtotal,

            'discount_amount' =>
                $discountTotal,

            'tax_amount' =>
                $taxTotal,

            'additional_charges' =>
                0,

            'grand_total' =>
                $grandTotal,
        ];

        return view('quotations.preview', compact(
            'quotation',
            'company',
            'customer',
            'template',
            'items'
        ));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' =>
                'required|exists:companies,id',

            'customer_id' =>
                'required|exists:customers,id',

            'template_id' =>
                'required|exists:company_templates,id',

            'quotation_date' =>
                'required|date',

            'expiry_date' =>
                'nullable|date|after_or_equal:quotation_date',

            'reference' =>
                'nullable|string|max:255',

            'notes' =>
                'nullable|string',

            'terms_conditions' =>
                'nullable|string',

            'items' =>
                'required|array|min:1',

            'items.*.item_name' =>
                'required|string|max:255',

            'items.*.description' =>
                'nullable|string',

            'items.*.quantity' =>
                'required|numeric|gt:0',

            'items.*.unit_price' =>
                'required|numeric|min:0',

            'items.*.discount' =>
                'nullable|numeric|min:0|max:100',

            'items.*.tax' =>
                'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($data) {

            $company = DB::table('companies')
                ->where('id', $data['company_id'])
                ->where('status', 'ACTIVE')
                ->lockForUpdate()
                ->first();

            if (!$company) {
                throw ValidationException::withMessages([
                    'company_id' =>
                        'Selected company is not available.'
                ]);
            }

            $customer = DB::table('customers')
                ->where('id', $data['customer_id'])
                ->where('company_id', $company->id)
                ->where('status', 'ACTIVE')
                ->first();

            if (!$customer) {
                throw ValidationException::withMessages([
                    'customer_id' =>
                        'Selected customer is invalid.'
                ]);
            }

            $template = DB::table('company_templates')
                ->where('id', $data['template_id'])
                ->where('company_id', $company->id)
                ->where('document_type', 'QUOTATION')
                ->first();

            if (!$template) {
                throw ValidationException::withMessages([
                    'template_id' =>
                        'Selected quotation template is invalid.'
                ]);
            }

            $quotationNumber =
                rtrim($company->quotation_prefix, '-')
                . '-'
                . now()->format('Y')
                . '-'
                . str_pad(
                    $company->quotation_next_number,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;

            $itemRows = [];

            foreach ($data['items'] as $index => $item) {

                $quantity =
                    (float) $item['quantity'];

                $unitPrice =
                    (float) $item['unit_price'];

                $discount =
                    (float) ($item['discount'] ?? 0);

                $tax =
                    (float) ($item['tax'] ?? 0);

                $lineSubtotal = round(
                    $quantity * $unitPrice,
                    2
                );

                $discountAmount = round(
                    $lineSubtotal * $discount / 100,
                    2
                );

                $taxableAmount =
                    $lineSubtotal - $discountAmount;

                $taxAmount = round(
                    $taxableAmount * $tax / 100,
                    2
                );

                $lineTotal = round(
                    $taxableAmount + $taxAmount,
                    2
                );

                $subtotal += $lineSubtotal;
                $discountTotal += $discountAmount;
                $taxTotal += $taxAmount;

                $itemRows[] = [
                    'sort_order' =>
                        $index + 1,

                    'item_name' =>
                        $item['item_name'],

                    'description' =>
                        $item['description'] ?? null,

                    'quantity' =>
                        $quantity,

                    'unit' =>
                        null,

                    'unit_price' =>
                        $unitPrice,

                    'discount_type' =>
                        $discount > 0
                            ? 'PERCENTAGE'
                            : 'NONE',

                    'discount_value' =>
                        $discount,

                    'discount_amount' =>
                        $discountAmount,

                    'tax_percentage' =>
                        $tax,

                    'tax_amount' =>
                        $taxAmount,

                    'line_total' =>
                        $lineTotal,
                ];
            }

            $subtotal =
                round($subtotal, 2);

            $discountTotal =
                round($discountTotal, 2);

            $taxTotal =
                round($taxTotal, 2);

            $grandTotal = round(
                $subtotal
                - $discountTotal
                + $taxTotal,
                2
            );

            $quotationId =
                DB::table('quotations')
                    ->insertGetId([

                        'company_id' =>
                            $company->id,

                        'customer_id' =>
                            $customer->id,

                        'quotation_number' =>
                            $quotationNumber,

                        'quotation_date' =>
                            $data['quotation_date'],

                        'expiry_date' =>
                            $data['expiry_date'] ?? null,

                        'reference' =>
                            $data['reference'] ?? null,

                        'subtotal' =>
                            $subtotal,

                        'discount_type' =>
                            $discountTotal > 0
                                ? 'FIXED'
                                : 'NONE',

                        'discount_value' =>
                            $discountTotal,

                        'discount_amount' =>
                            $discountTotal,

                        'tax_percentage' =>
                            0,

                        'tax_amount' =>
                            $taxTotal,

                        'additional_charges' =>
                            0,

                        'grand_total' =>
                            $grandTotal,

                        'status' =>
                            'DRAFT',

                        'notes' =>
                            $data['notes'] ?? null,

                        'terms_conditions' =>
                            $data['terms_conditions'] ?? null,

                        'template_id' =>
                            $template->id,

                        'company_snapshot' =>
                            json_encode(
                                $company,
                                JSON_UNESCAPED_UNICODE
                            ),

                        'customer_snapshot' =>
                            json_encode(
                                $customer,
                                JSON_UNESCAPED_UNICODE
                            ),

                        'template_snapshot' =>
                            json_encode(
                                $template,
                                JSON_UNESCAPED_UNICODE
                            ),

                        'created_by' =>
                            auth()->id(),

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);

            foreach ($itemRows as $row) {

                DB::table('quotation_items')
                    ->insert([
                        'quotation_id' =>
                            $quotationId,

                        ...$row,

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);
            }

            DB::table('companies')
                ->where('id', $company->id)
                ->update([
                    'quotation_next_number' =>
                        $company->quotation_next_number + 1,

                    'updated_at' =>
                        now(),
                ]);

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
            ->where('quotations.id', $id)
            ->select(
                'quotations.*',
                'users.name as created_by_name'
            )
            ->first();

        abort_if(!$quotation, 404);

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
                $quotation->id
            )
            ->orderBy('sort_order')
            ->get();

        $template = DB::table('company_templates')
            ->where(
                'id',
                $quotation->template_id
            )
            ->first();

        return view('quotations.show', compact(
            'quotation',
            'company',
            'customer',
            'items',
            'template'
        ));
    }


    public function downloadPdf($id)
    {
        $quotation = DB::table('quotations')
            ->where('id', $id)
            ->first();

        abort_if(!$quotation, 404);

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
                $quotation->id
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

        $pdf = Pdf::loadView(
            'quotations.pdf',
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
            $quotation->quotation_number . '.pdf'
        );
    }
}
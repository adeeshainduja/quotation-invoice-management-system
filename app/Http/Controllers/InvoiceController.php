<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
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

        $companyCurrency = $companyId
            ? DB::table('companies')
                ->where('id', $companyId)
                ->value('currency') ?? 'LKR'
            : 'LKR';

        $customerList = $companyId
            ? DB::table('customers')
                ->where('company_id', $companyId)
                ->where('status', 'ACTIVE')
                ->orderBy('business_name')
                ->get(['id', 'business_name'])
            : collect();

        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $stats = [
            'month_count' => 0,
            'month_total' => 0,
            'paid' => 0,
            'unpaid' => 0,
            'outstanding' => 0,
            'to_send' => 0,
            'overdue' => 0,
        ];

        if ($companyId) {
            $base = DB::table('invoices')
                ->where('company_id', $companyId);

            $stats['month_count'] = (clone $base)
                ->whereBetween('invoice_date', [$start, $end])
                ->where('status', '!=', 'CANCELLED')
                ->count();

            $stats['month_total'] = (clone $base)
                ->whereBetween('invoice_date', [$start, $end])
                ->where('status', '!=', 'CANCELLED')
                ->sum('grand_total');

            $stats['paid'] = (clone $base)
                ->where('status', 'PAID')
                ->count();

            $stats['unpaid'] = (clone $base)
                ->where('balance_amount', '>', 0)
                ->whereNotIn('status', ['DRAFT', 'CANCELLED'])
                ->count();

            $stats['outstanding'] = (clone $base)
                ->where('balance_amount', '>', 0)
                ->whereNotIn('status', ['DRAFT', 'CANCELLED'])
                ->sum('balance_amount');

            $stats['to_send'] = (clone $base)
                ->whereBetween('invoice_date', [$start, $end])
                ->where('status', 'DRAFT')
                ->count();

            $stats['overdue'] = (clone $base)
                ->whereDate('due_date', '<', today())
                ->where('balance_amount', '>', 0)
                ->whereNotIn('status', ['PAID', 'CANCELLED'])
                ->count();
        }

        $invoices = DB::table('invoices')
            ->join(
                'customers',
                'invoices.customer_id',
                '=',
                'customers.id'
            )
            ->when(
                $companyId,
                fn ($q) => $q->where(
                    'invoices.company_id',
                    $companyId
                ),
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query
                        ->where(
                            'invoices.invoice_number',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'customers.business_name',
                            'like',
                            "%{$search}%"
                        );
                });
            })
            ->when(
                $request->status,
                fn ($q, $status) => $q->where('invoices.status', $status)
            )
            ->when(
                $request->customer_id,
                fn ($q, $id) => $q->where('invoices.customer_id', $id)
            )
            ->when(
                $request->from_date,
                fn ($q, $date) => $q->whereDate(
                    'invoices.invoice_date',
                    '>=',
                    $date
                )
            )
            ->when(
                $request->to_date,
                fn ($q, $date) => $q->whereDate(
                    'invoices.invoice_date',
                    '<=',
                    $date
                )
            )
            ->select(
                'invoices.*',
                'customers.business_name'
            )
            ->orderByDesc('invoices.invoice_date')
            ->orderByDesc('invoices.id')
            ->paginate(10)
            ->withQueryString();

        return view('invoices.index', compact(
            'companyList',
            'companyId',
            'companyCurrency',
            'customerList',
            'stats',
            'invoices'
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
        $invoiceNumber = '';

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
                    ->where('document_type', 'INVOICE')
                    ->orderByDesc('is_default')
                    ->orderBy('template_name')
                    ->get();

                $invoiceNumber =
                    rtrim($company->invoice_prefix, '-')
                    .'-'
                    .now()->format('Y')
                    .'-'
                    .str_pad(
                        $company->invoice_next_number,
                        4,
                        '0',
                        STR_PAD_LEFT
                    );
            }
        }

        return view('invoices.create', compact(
            'companyList',
            'companyId',
            'company',
            'customers',
            'templates',
            'invoiceNumber'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',

            'customer_id' => 'required|exists:customers,id',

            'template_id' => 'required|exists:company_templates,id',

            'invoice_date' => 'required|date',

            'due_date' => 'nullable|date|after_or_equal:invoice_date',

            'subject' => 'nullable|string|max:255',

            'reference' => 'nullable|string|max:255',

            'additional_charges' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',

            'terms_conditions' => 'nullable|string',

            'payment_method' => 'required|in:CASH,BANK_TRANSFER,CARD,CHEQUE,OTHER',

            'payment_date' => 'required|date',

            'payment_amount' => 'required|numeric|gt:0',

            'payment_reference' => 'nullable|string|max:255',

            'payment_notes' => 'nullable|string',

            'schedule_enabled' => 'nullable|boolean',

            'schedule_frequency' => 'nullable|required_if:schedule_enabled,1|in:WEEKLY,MONTHLY,QUARTERLY,YEARLY',

            'schedule_start_date' => 'nullable|required_if:schedule_enabled,1|date|after:invoice_date',

            'schedule_end_date' => 'nullable|date|after_or_equal:schedule_start_date',

            'items' => 'required|array|min:1',

            'items.*.item_name' => 'required|string|max:255',

            'items.*.description' => 'nullable|string',

            'items.*.quantity' => 'required|numeric|gt:0',

            'items.*.unit' => 'nullable|string|max:50',

            'items.*.unit_price' => 'required|numeric|min:0',

            'items.*.discount_type' => 'required|in:NONE,PERCENTAGE,FIXED',

            'items.*.discount_value' => 'required|numeric|min:0',

            'items.*.tax_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($data['company_id']), 403, 'Unauthorized company access.');

        $companyCheck = DB::table('companies')->where('id', $data['company_id'])->first();
        abort_if(! $companyCheck || $companyCheck->status !== 'ACTIVE', 403, 'Cannot create transactions for an inactive company.');

        abort_if(! $user->isAdmin() && ! $user->hasTemplateAccess($data['template_id']), 403, 'Unauthorized template access.');

        return DB::transaction(function () use ($data) {

            $company = DB::table('companies')
                ->where('id', $data['company_id'])
                ->where('status', 'ACTIVE')
                ->lockForUpdate()
                ->first();

            if (! $company) {
                throw ValidationException::withMessages([
                    'company_id' => 'Selected company is not available.',
                ]);
            }

            $customer = DB::table('customers')
                ->where('id', $data['customer_id'])
                ->where('company_id', $company->id)
                ->where('status', 'ACTIVE')
                ->first();

            if (! $customer) {
                throw ValidationException::withMessages([
                    'customer_id' => 'Selected customer does not belong to this company.',
                ]);
            }

            $template = DB::table('company_templates')
                ->where('id', $data['template_id'])
                ->where('company_id', $company->id)
                ->where('document_type', 'INVOICE')
                ->first();

            if (! $template) {
                throw ValidationException::withMessages([
                    'template_id' => 'Selected invoice template is invalid.',
                ]);
            }

            $invoiceNumber =
                rtrim($company->invoice_prefix, '-')
                .'-'
                .now()->format('Y')
                .'-'
                .str_pad(
                    $company->invoice_next_number,
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;
            $items = [];

            $isVatEnabled = (bool) ($company->vat_enabled ?? $company->vat_registered ?? false);
            $companyVatRate = $isVatEnabled ? (float) ($company->tax_percentage ?? $company->vat_percentage ?? 0) : 0;

            foreach ($data['items'] as $index => $item) {

                $quantity =
                    $this->decimalToInteger(
                        $item['quantity']
                    );

                $unitPrice =
                    $this->decimalToInteger(
                        $item['unit_price']
                    );

                $lineSubtotal = (int) round(
                    ($quantity * $unitPrice) / 100
                );

                $discountType =
                    $item['discount_type'];

                $discountValue =
                    $this->decimalToInteger(
                        $item['discount_value']
                    );

                $discountAmount = 0;

                if ($discountType === 'PERCENTAGE') {

                    if ($discountValue > 10000) {
                        throw ValidationException::withMessages([
                            "items.$index.discount_value" => 'Percentage discount cannot exceed 100%.',
                        ]);
                    }

                    $discountAmount = (int) round(
                        ($lineSubtotal * $discountValue)
                        / 10000
                    );

                } elseif ($discountType === 'FIXED') {

                    $discountAmount = $discountValue;

                    if ($discountAmount > $lineSubtotal) {
                        throw ValidationException::withMessages([
                            "items.$index.discount_value" => 'Fixed discount cannot exceed subtotal.',
                        ]);
                    }
                }

                $taxable =
                    $lineSubtotal - $discountAmount;

                $taxPercentage = $isVatEnabled
                    ? $this->decimalToInteger(
                        $item['tax_percentage'] ?? $companyVatRate
                    )
                    : 0;

                $taxAmount = $isVatEnabled
                    ? (int) round(
                        ($taxable * $taxPercentage)
                        / 10000
                    )
                    : 0;

                $lineTotal =
                    $taxable + $taxAmount;

                $subtotal += $lineSubtotal;
                $discountTotal += $discountAmount;
                $taxTotal += $taxAmount;

                $items[] = [
                    'sort_order' => $index + 1,

                    'item_name' => $item['item_name'],

                    'description' => $item['description'] ?? null,

                    'quantity' => $this->integerToDecimal(
                        $quantity
                    ),

                    'unit' => $item['unit'] ?? null,

                    'unit_price' => $this->integerToDecimal(
                        $unitPrice
                    ),

                    'discount_type' => $discountType,

                    'discount_value' => $this->integerToDecimal(
                        $discountValue
                    ),

                    'discount_amount' => $this->integerToDecimal(
                        $discountAmount
                    ),

                    'tax_percentage' => $this->integerToDecimal(
                        $taxPercentage
                    ),

                    'tax_amount' => $this->integerToDecimal(
                        $taxAmount
                    ),

                    'line_total' => $this->integerToDecimal(
                        $lineTotal
                    ),
                ];
            }

            $additionalCharges =
                $this->decimalToInteger(
                    $data['additional_charges'] ?? 0
                );

            $grandTotal =
                $subtotal
                - $discountTotal
                + $taxTotal
                + $additionalCharges;

            $paymentAmount =
                $this->decimalToInteger(
                    $data['payment_amount']
                );

            if ($paymentAmount > $grandTotal) {
                throw ValidationException::withMessages([
                    'payment_amount' => 'Payment amount cannot exceed invoice total.',
                ]);
            }

            $balanceAmount =
                $grandTotal - $paymentAmount;

            $status =
                $balanceAmount <= 0
                    ? 'PAID'
                    : 'PARTIALLY_PAID';

            $invoiceId = DB::table('invoices')
                ->insertGetId([

                    'company_id' => $company->id,

                    'customer_id' => $customer->id,

                    'quotation_id' => null,

                    'invoice_number' => $invoiceNumber,

                    'invoice_date' => $data['invoice_date'],

                    'due_date' => $data['due_date'] ?? null,

                    'subject' => $data['subject'] ?? null,

                    'reference' => $data['reference'] ?? null,

                    'subtotal' => $this->integerToDecimal(
                        $subtotal
                    ),

                    'discount_type' => $discountTotal > 0
                            ? 'FIXED'
                            : 'NONE',

                    'discount_value' => $this->integerToDecimal(
                        $discountTotal
                    ),

                    'discount_amount' => $this->integerToDecimal(
                        $discountTotal
                    ),

                    'tax_percentage' => $this->integerToDecimal(
                        $isVatEnabled ? $this->decimalToInteger($companyVatRate) : 0
                    ),

                    'tax_amount' => $this->integerToDecimal(
                        $taxTotal
                    ),

                    'vat_enabled' => $isVatEnabled,

                    'vat_percentage' => $this->integerToDecimal(
                        $isVatEnabled ? $this->decimalToInteger($companyVatRate) : 0
                    ),

                    'vat_amount' => $this->integerToDecimal(
                        $taxTotal
                    ),

                    'additional_charges' => $this->integerToDecimal(
                        $additionalCharges
                    ),

                    'grand_total' => $this->integerToDecimal(
                        $grandTotal
                    ),

                    'amount_paid' => $this->integerToDecimal(
                        $paymentAmount
                    ),

                    'balance_amount' => $this->integerToDecimal(
                        $balanceAmount
                    ),

                    'status' => $status,

                    'notes' => $data['notes'] ?? null,

                    'terms_conditions' => $data['terms_conditions'] ?? null,

                    'template_id' => $template->id,

                    'company_snapshot' => json_encode(
                        $company,
                        JSON_UNESCAPED_UNICODE
                    ),

                    'customer_snapshot' => json_encode(
                        $customer,
                        JSON_UNESCAPED_UNICODE
                    ),

                    'template_snapshot' => json_encode(
                        $template,
                        JSON_UNESCAPED_UNICODE
                    ),

                    'created_by' => auth()->id(),

                    'created_at' => now(),

                    'updated_at' => now(),
                ]);

            foreach ($items as $item) {

                DB::table('invoice_items')
                    ->insert([
                        'invoice_id' => $invoiceId,

                        'source_quotation_item_id' => null,

                        ...$item,

                        'created_at' => now(),

                        'updated_at' => now(),
                    ]);
            }

            DB::table('payments')->insert([
                'invoice_id' => $invoiceId,

                'payment_date' => $data['payment_date'],

                'amount' => $this->integerToDecimal(
                    $paymentAmount
                ),

                'payment_method' => $data['payment_method'],

                'reference' => $data['payment_reference'] ?? null,

                'notes' => $data['payment_notes'] ?? null,

                'created_by' => auth()->id(),

                'created_at' => now(),

                'updated_at' => now(),
            ]);

            if (! empty($data['schedule_enabled'])) {

                $invoiceDate =
                    Carbon::parse(
                        $data['invoice_date']
                    );

                $dueDays = ! empty($data['due_date'])
                    ? (int) $invoiceDate->diffInDays(
                        Carbon::parse(
                            $data['due_date']
                        )
                    )
                    : 30;

                DB::table(
                    'recurring_invoice_schedules'
                )->insert([

                    'company_id' => $company->id,

                    'customer_id' => $customer->id,

                    'template_id' => $template->id,

                    'frequency' => $data['schedule_frequency'],

                    'next_run_date' => $data['schedule_start_date'],

                    'end_date' => $data['schedule_end_date'] ?? null,

                    'due_days' => $dueDays,

                    'invoice_data' => json_encode([
                        'subject' => $data['subject'] ?? null,

                        'reference' => $data['reference'] ?? null,

                        'additional_charges' => $data['additional_charges'] ?? 0,

                        'notes' => $data['notes'] ?? null,

                        'terms_conditions' => $data['terms_conditions'] ?? null,

                        'items' => $data['items'],
                    ], JSON_UNESCAPED_UNICODE),

                    'status' => 'ACTIVE',

                    'last_run_date' => null,

                    'last_invoice_id' => null,

                    'created_by' => auth()->id(),

                    'created_at' => now(),

                    'updated_at' => now(),
                ]);
            }

            DB::table('companies')
                ->where('id', $company->id)
                ->update([
                    'invoice_next_number' => $company->invoice_next_number + 1,

                    'updated_at' => now(),
                ]);

            return redirect()
                ->route('invoices.index', [
                    'company_id' => $company->id,
                ])
                ->with(
                    'success',
                    'Invoice created successfully.'
                );
        });
    }

    public function preview(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',

            'customer_id' => 'required|exists:customers,id',

            'template_id' => 'required|exists:company_templates,id',

            'invoice_date' => 'required|date',

            'due_date' => 'nullable|date|after_or_equal:invoice_date',

            'subject' => 'nullable|string|max:255',

            'reference' => 'nullable|string|max:255',

            'additional_charges' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',

            'terms_conditions' => 'nullable|string',

            'payment_method' => 'required|in:CASH,BANK_TRANSFER,CARD,CHEQUE,OTHER',

            'payment_date' => 'required|date',

            'payment_amount' => 'required|numeric|gt:0',

            'payment_reference' => 'nullable|string|max:255',

            'payment_notes' => 'nullable|string',

            'items' => 'required|array|min:1',

            'items.*.item_name' => 'required|string|max:255',

            'items.*.description' => 'nullable|string',

            'items.*.quantity' => 'required|numeric|gt:0',

            'items.*.unit' => 'nullable|string|max:50',

            'items.*.unit_price' => 'required|numeric|min:0',

            'items.*.discount_type' => 'required|in:NONE,PERCENTAGE,FIXED',

            'items.*.discount_value' => 'required|numeric|min:0',

            'items.*.tax_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($data['company_id']), 403, 'Unauthorized company access.');

        $company = DB::table('companies')
            ->where('id', $data['company_id'])
            ->first();

        $customer = DB::table('customers')
            ->where('id', $data['customer_id'])
            ->where('company_id', $data['company_id'])
            ->first();

        $template = DB::table('company_templates')
            ->where('id', $data['template_id'])
            ->where('company_id', $data['company_id'])
            ->where('document_type', 'INVOICE')
            ->first();

        abort_if(
            ! $company || ! $customer || ! $template,
            404
        );

        $bank = DB::table('company_bank_details')
            ->where('company_id', $company->id)
            ->first();

        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;
        $items = [];

        $isVatEnabled = (bool) ($company->vat_enabled ?? $company->vat_registered ?? false);
        $companyVatRate = $isVatEnabled ? (float) ($company->tax_percentage ?? $company->vat_percentage ?? 0) : 0;

        foreach ($data['items'] as $item) {

            $qty =
                (float) $item['quantity'];

            $price =
                (float) $item['unit_price'];

            $lineSubtotal =
                $qty * $price;

            $discountType =
                $item['discount_type'];

            $discountValue =
                (float) $item['discount_value'];

            $discountAmount = 0;

            if ($discountType === 'PERCENTAGE') {
                $discountAmount =
                    $lineSubtotal *
                    $discountValue /
                    100;
            }

            if ($discountType === 'FIXED') {
                $discountAmount =
                    min(
                        $discountValue,
                        $lineSubtotal
                    );
            }

            $taxable =
                $lineSubtotal - $discountAmount;

            $taxPercentage = $isVatEnabled
                ? (float) ($item['tax_percentage'] ?? $companyVatRate)
                : 0;

            $taxAmount = $isVatEnabled
                ? ($taxable * $taxPercentage / 100)
                : 0;

            $lineTotal =
                $taxable + $taxAmount;

            $subtotal += $lineSubtotal;
            $discountTotal += $discountAmount;
            $taxTotal += $taxAmount;

            $items[] = (object) [
                'item_name' => $item['item_name'],

                'description' => $item['description'] ?? null,

                'quantity' => $qty,

                'unit' => $item['unit'] ?? null,

                'unit_price' => $price,

                'discount_type' => $discountType,

                'discount_value' => $discountValue,

                'discount_amount' => $discountAmount,

                'tax_percentage' => $taxPercentage,

                'tax_amount' => $taxAmount,

                'line_total' => $lineTotal,
            ];
        }

        $additionalCharges =
            (float) (
                $data['additional_charges'] ?? 0
            );

        $grandTotal =
            $subtotal
            - $discountTotal
            + $taxTotal
            + $additionalCharges;

        $paymentAmount =
            (float) $data['payment_amount'];

        if ($paymentAmount > $grandTotal) {
            throw ValidationException::withMessages([
                'payment_amount' => 'Payment amount cannot exceed invoice total.',
            ]);
        }

        $balance =
            $grandTotal - $paymentAmount;

        $invoiceNumber =
            rtrim($company->invoice_prefix, '-')
            .'-'
            .now()->format('Y')
            .'-'
            .str_pad(
                $company->invoice_next_number,
                4,
                '0',
                STR_PAD_LEFT
            );

        $invoice = (object) [
            'invoice_number' => $invoiceNumber,

            'invoice_date' => $data['invoice_date'],

            'due_date' => $data['due_date'] ?? null,

            'subject' => $data['subject'] ?? null,

            'reference' => $data['reference'] ?? null,

            'subtotal' => $subtotal,

            'discount_amount' => $discountTotal,

            'tax_amount' => $taxTotal,

            'vat_enabled' => (bool) ($company->vat_enabled ?? $company->vat_registered ?? false),

            'vat_percentage' => (bool) ($company->vat_enabled ?? $company->vat_registered ?? false)
                ? (float) ($company->tax_percentage ?? $company->vat_percentage ?? 0)
                : 0,

            'vat_amount' => $taxTotal,

            'additional_charges' => $additionalCharges,

            'grand_total' => $grandTotal,

            'amount_paid' => $paymentAmount,

            'balance_amount' => $balance,

            'status' => $balance <= 0
                    ? 'PAID'
                    : 'PARTIALLY_PAID',

            'notes' => $data['notes'] ?? null,

            'terms_conditions' => $data['terms_conditions'] ?? null,
        ];

        $payments = collect([
            (object) [
                'payment_date' => $data['payment_date'],

                'amount' => $paymentAmount,

                'payment_method' => $data['payment_method'],

                'reference' => $data['payment_reference'] ?? null,

                'notes' => $data['payment_notes'] ?? null,
            ],
        ]);

        return view('invoices.pdf', compact(
            'invoice',
            'company',
            'customer',
            'items',
            'template',
            'bank',
            'payments'
        ));
    }

    public function downloadPdf($id)
    {
        $invoice = DB::table('invoices')
            ->where('id', $id)
            ->first();

        abort_if(! $invoice, 404);

        $user = auth()->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');
        abort_if(! $user->hasCompanyAccess($invoice->company_id), 403, 'Unauthorized company access.');

        $company =
            $invoice->company_snapshot
                ? json_decode(
                    $invoice->company_snapshot
                )
                : DB::table('companies')
                    ->where(
                        'id',
                        $invoice->company_id
                    )
                    ->first();

        $customer =
            $invoice->customer_snapshot
                ? json_decode(
                    $invoice->customer_snapshot
                )
                : DB::table('customers')
                    ->where(
                        'id',
                        $invoice->customer_id
                    )
                    ->first();

        $template =
            $invoice->template_snapshot
                ? json_decode(
                    $invoice->template_snapshot
                )
                : DB::table('company_templates')
                    ->where(
                        'id',
                        $invoice->template_id
                    )
                    ->first();

        $items = DB::table('invoice_items')
            ->where('invoice_id', $id)
            ->orderBy('sort_order')
            ->get();

        $payments = DB::table('payments')
            ->where('invoice_id', $id)
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $bank = DB::table('company_bank_details')
            ->where(
                'company_id',
                $invoice->company_id
            )
            ->first();

        $isVatEnabled = isset($invoice->vat_enabled)
            ? (bool) $invoice->vat_enabled
            : ((float) ($invoice->vat_amount ?? $invoice->tax_amount ?? 0) > 0);
        $templateView = $isVatEnabled ? 'pdf.invoices.tax-invoice' : 'pdf.invoices.normal';

        $pdf = Pdf::loadView(
            $templateView,
            compact(
                'invoice',
                'company',
                'customer',
                'items',
                'template',
                'bank',
                'payments'
            )
        );

        return $pdf->download(
            $invoice->invoice_number.'.pdf'
        );
    }

    private function decimalToInteger(
        mixed $value,
        int $scale = 2
    ): int {
        $value =
            trim((string) $value);

        $negative =
            str_starts_with(
                $value,
                '-'
            );

        if ($negative) {
            $value =
                substr($value, 1);
        }

        [$whole, $fraction] =
            array_pad(
                explode('.', $value, 2),
                2,
                ''
            );

        $whole =
            preg_replace(
                '/[^0-9]/',
                '',
                $whole
            );

        $fraction =
            preg_replace(
                '/[^0-9]/',
                '',
                $fraction
            );

        $fraction =
            substr(
                str_pad(
                    $fraction,
                    $scale,
                    '0'
                ),
                0,
                $scale
            );

        $multiplier =
            10 ** $scale;

        $result =
            ((int) ($whole ?: 0) * $multiplier)
            + (int) ($fraction ?: 0);

        return $negative
            ? -$result
            : $result;
    }

    private function integerToDecimal(
        int $value,
        int $scale = 2
    ): string {
        $negative =
            $value < 0;

        $value =
            abs($value);

        $multiplier =
            10 ** $scale;

        $whole =
            intdiv(
                $value,
                $multiplier
            );

        $fraction =
            str_pad(
                (string) (
                    $value % $multiplier
                ),
                $scale,
                '0',
                STR_PAD_LEFT
            );

        return
            ($negative ? '-' : '')
            .$whole
            .'.'
            .$fraction;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
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
                ->get(['id', 'business_name'])
            : collect();

        $invoices = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->when(
                $companyId,
                fn ($query) => $query->where('invoices.company_id', $companyId),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->when($request->search, function ($query, $search) {
                $query->where('invoices.invoice_number', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('invoices.status', $status);
            })
            ->when($request->customer_id, function ($query, $customerId) {
                $query->where('invoices.customer_id', $customerId);
            })
            ->select(
                'invoices.id',
                'invoices.invoice_number',
                'invoices.invoice_date',
                'invoices.due_date',
                'invoices.grand_total',
                'invoices.amount_paid',
                'invoices.balance_amount',
                'invoices.status',
                'customers.business_name'
            )
            ->orderByDesc('invoices.invoice_date')
            ->paginate(10)
            ->withQueryString();

        return view('invoices.index', compact(
            'companyList',
            'companyId',
            'customerList',
            'invoices'
        ));
    }


    public function create(Request $request)
    {
        $companyList = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

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
                    . '-'
                    . now()->format('Y')
                    . '-'
                    . str_pad(
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

            'items' => 'required|array|min:1',

            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',

            'items.*.quantity' => 'required|numeric|gt:0',

            'items.*.unit' => 'nullable|string|max:50',

            'items.*.unit_price' => 'required|numeric|min:0',

            'items.*.discount_type' =>
                'required|in:NONE,PERCENTAGE,FIXED',

            'items.*.discount_value' =>
                'required|numeric|min:0',

            'items.*.tax_percentage' =>
                'required|numeric|min:0|max:100',
        ]);


        return DB::transaction(function () use ($data) {

            $company = DB::table('companies')
                ->where('id', $data['company_id'])
                ->where('status', 'ACTIVE')
                ->lockForUpdate()
                ->first();

            if (!$company) {
                throw ValidationException::withMessages([
                    'company_id' => 'Selected company is not available.',
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
                        'Selected customer does not belong to this company.',
                ]);
            }


            $template = DB::table('company_templates')
                ->where('id', $data['template_id'])
                ->where('company_id', $company->id)
                ->where('document_type', 'INVOICE')
                ->first();

            if (!$template) {
                throw ValidationException::withMessages([
                    'template_id' =>
                        'Selected invoice template is invalid.',
                ]);
            }


            $invoiceNumber =
                rtrim($company->invoice_prefix, '-')
                . '-'
                . now()->format('Y')
                . '-'
                . str_pad(
                    $company->invoice_next_number,
                    4,
                    '0',
                    STR_PAD_LEFT
                );


            $subtotalCents = 0;
            $discountTotalCents = 0;
            $taxTotalCents = 0;

            $itemRows = [];


            foreach ($data['items'] as $index => $item) {

                $quantity = $this->decimalToInteger(
                    $item['quantity'],
                    2
                );

                $unitPrice = $this->decimalToInteger(
                    $item['unit_price'],
                    2
                );


                /*
                 * quantity scale = 100
                 * money scale = 100
                 */
                $lineSubtotal = (int) round(
                    ($quantity * $unitPrice) / 100
                );


                $discountType =
                    $item['discount_type'];

                $discountValue =
                    $this->decimalToInteger(
                        $item['discount_value'],
                        2
                    );


                $discountAmount = 0;


                if ($discountType === 'PERCENTAGE') {

                    if ($discountValue > 10000) {
                        throw ValidationException::withMessages([
                            "items.$index.discount_value" =>
                                'Percentage discount cannot exceed 100%.',
                        ]);
                    }

                    $discountAmount = (int) round(
                        ($lineSubtotal * $discountValue) / 10000
                    );

                } elseif ($discountType === 'FIXED') {

                    $discountAmount = $discountValue;

                    if ($discountAmount > $lineSubtotal) {
                        throw ValidationException::withMessages([
                            "items.$index.discount_value" =>
                                'Fixed discount cannot exceed the line subtotal.',
                        ]);
                    }
                }


                $taxableAmount =
                    $lineSubtotal - $discountAmount;


                $taxPercentage =
                    $this->decimalToInteger(
                        $item['tax_percentage'],
                        2
                    );


                $taxAmount = (int) round(
                    ($taxableAmount * $taxPercentage) / 10000
                );


                $lineTotal =
                    $taxableAmount + $taxAmount;


                $subtotalCents += $lineSubtotal;

                $discountTotalCents += $discountAmount;

                $taxTotalCents += $taxAmount;


                $itemRows[] = [
                    'sort_order' => $index + 1,

                    'item_name' =>
                        $item['item_name'],

                    'description' =>
                        $item['description'] ?? null,

                    'quantity' =>
                        $this->integerToDecimal($quantity, 2),

                    'unit' =>
                        $item['unit'] ?? null,

                    'unit_price' =>
                        $this->integerToDecimal($unitPrice, 2),

                    'discount_type' =>
                        $discountType,

                    'discount_value' =>
                        $this->integerToDecimal(
                            $discountValue,
                            2
                        ),

                    'discount_amount' =>
                        $this->integerToDecimal(
                            $discountAmount,
                            2
                        ),

                    'tax_percentage' =>
                        $this->integerToDecimal(
                            $taxPercentage,
                            2
                        ),

                    'tax_amount' =>
                        $this->integerToDecimal(
                            $taxAmount,
                            2
                        ),

                    'line_total' =>
                        $this->integerToDecimal(
                            $lineTotal,
                            2
                        ),
                ];
            }


            $additionalChargesCents =
                $this->decimalToInteger(
                    $data['additional_charges'] ?? 0,
                    2
                );


            $grandTotalCents =
                $subtotalCents
                - $discountTotalCents
                + $taxTotalCents
                + $additionalChargesCents;


            $invoiceId = DB::table('invoices')
                ->insertGetId([

                    'company_id' =>
                        $company->id,

                    'customer_id' =>
                        $customer->id,

                    'quotation_id' =>
                        null,

                    'invoice_number' =>
                        $invoiceNumber,

                    'invoice_date' =>
                        $data['invoice_date'],

                    'due_date' =>
                        $data['due_date'] ?? null,

                    'subject' =>
                        $data['subject'] ?? null,

                    'reference' =>
                        $data['reference'] ?? null,

                    'subtotal' =>
                        $this->integerToDecimal(
                            $subtotalCents,
                            2
                        ),

                    /*
                     * Header discount represents
                     * total item discount.
                     */
                    'discount_type' =>
                        $discountTotalCents > 0
                            ? 'FIXED'
                            : 'NONE',

                    'discount_value' =>
                        $this->integerToDecimal(
                            $discountTotalCents,
                            2
                        ),

                    'discount_amount' =>
                        $this->integerToDecimal(
                            $discountTotalCents,
                            2
                        ),

                    'tax_percentage' =>
                        '0.00',

                    'tax_amount' =>
                        $this->integerToDecimal(
                            $taxTotalCents,
                            2
                        ),

                    'additional_charges' =>
                        $this->integerToDecimal(
                            $additionalChargesCents,
                            2
                        ),

                    'grand_total' =>
                        $this->integerToDecimal(
                            $grandTotalCents,
                            2
                        ),

                    'amount_paid' =>
                        '0.00',

                    'balance_amount' =>
                        $this->integerToDecimal(
                            $grandTotalCents,
                            2
                        ),

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

                DB::table('invoice_items')->insert([
                    'invoice_id' =>
                        $invoiceId,

                    'source_quotation_item_id' =>
                        null,

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
                    'invoice_next_number' =>
                        $company->invoice_next_number + 1,

                    'updated_at' =>
                        now(),
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


    private function decimalToInteger(
        mixed $value,
        int $scale = 2
    ): int
    {
        $value = trim((string) $value);

        $negative =
            str_starts_with($value, '-');

        if ($negative) {
            $value = substr($value, 1);
        }

        [$whole, $fraction] =
            array_pad(
                explode('.', $value, 2),
                2,
                ''
            );

        $whole =
            preg_replace('/[^0-9]/', '', $whole);

        $fraction =
            preg_replace('/[^0-9]/', '', $fraction);

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
    ): string
    {
        $negative =
            $value < 0;

        $value =
            abs($value);

        $multiplier =
            10 ** $scale;

        $whole =
            intdiv($value, $multiplier);

        $fraction =
            str_pad(
                (string) ($value % $multiplier),
                $scale,
                '0',
                STR_PAD_LEFT
            );

        return
            ($negative ? '-' : '')
            . $whole
            . '.'
            . $fraction;
    }
}
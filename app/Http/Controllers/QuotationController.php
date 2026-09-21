<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $customerList = DB::table('customers')
            ->where('company_id', $companyId)
            ->orderBy('business_name')
            ->get(['id', 'business_name']);

        $quotations = DB::table('quotations')
            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
            ->where('quotations.company_id', $companyId)

            ->when($request->search, function ($query, $search) {
                $query->where('quotations.quotation_number', 'like', "%{$search}%");
            })

            ->when($request->status, function ($query, $status) {
                $query->where('quotations.status', $status);
            })

            ->when($request->customer_id, function ($query, $customerId) {
                $query->where('quotations.customer_id', $customerId);
            })

            ->when($request->from_date, function ($query, $date) {
                $query->whereDate('quotations.quotation_date', '>=', $date);
            })

            ->when($request->to_date, function ($query, $date) {
                $query->whereDate('quotations.quotation_date', '<=', $date);
            })

            ->select(
                'quotations.id',
                'quotations.quotation_number',
                'quotations.quotation_date',
                'quotations.expiry_date',
                'quotations.grand_total',
                'quotations.status',
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

    $companyId = $request->integer('company_id')
        ?: optional($companyList->first())->id;

    $company = DB::table('companies')
        ->where('id', $companyId)
        ->first();

    abort_if(!$company, 404);

    $customers = DB::table('customers')
        ->where('company_id', $companyId)
        ->where('status', 'ACTIVE')
        ->orderBy('business_name')
        ->get();

    $templates = DB::table('company_templates')
        ->where('company_id', $companyId)
        ->where('document_type', 'QUOTATION')
        ->orderByDesc('is_default')
        ->get();

    $quotationNumber =
        rtrim($company->quotation_prefix, '-') . '-' .
        now()->format('Y') . '-' .
        str_pad($company->quotation_next_number, 4, '0', STR_PAD_LEFT);

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
            'expiry_date' => 'required|date|after_or_equal:quotation_date',

            'reference' => 'nullable|string|max:255',
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

        DB::transaction(function () use ($data) {

            $company = DB::table('companies')
                ->where('id', $data['company_id'])
                ->lockForUpdate()
                ->first();

            $customer = DB::table('customers')
                ->where('id', $data['customer_id'])
                ->where('company_id', $company->id)
                ->first();

            abort_if(!$customer, 422);

            $template = DB::table('company_templates')
                ->where('id', $data['template_id'])
                ->where('company_id', $company->id)
                ->where('document_type', 'QUOTATION')
                ->first();

            abort_if(!$template, 422);

            $quotationNumber =
                rtrim($company->quotation_prefix, '-') . '-' .
                now()->format('Y') . '-' .
                str_pad($company->quotation_next_number, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $discountTotal = 0;
            $taxTotal = 0;
            $grandTotal = 0;

            $items = [];

            foreach ($data['items'] as $index => $item) {

                $quantity = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $discountPercent = (float) ($item['discount'] ?? 0);
                $taxPercent = (float) ($item['tax'] ?? 0);

                $lineSubtotal = $quantity * $unitPrice;

                $discountAmount =
                    $lineSubtotal * ($discountPercent / 100);

                $taxableAmount =
                    $lineSubtotal - $discountAmount;

                $taxAmount =
                    $taxableAmount * ($taxPercent / 100);

                $lineTotal =
                    $taxableAmount + $taxAmount;

                $subtotal += $lineSubtotal;
                $discountTotal += $discountAmount;
                $taxTotal += $taxAmount;
                $grandTotal += $lineTotal;

                $items[] = [
                    'sort_order' => $index + 1,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $quantity,
                    'unit' => 'unit',
                    'unit_price' => $unitPrice,

                    'discount_type' =>
                        $discountPercent > 0
                            ? 'PERCENTAGE'
                            : 'NONE',

                    'discount_value' => $discountPercent,
                    'discount_amount' => $discountAmount,

                    'tax_percentage' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ];
            }

            $quotationId = DB::table('quotations')->insertGetId([
                'company_id' => $company->id,
                'customer_id' => $customer->id,

                'quotation_number' => $quotationNumber,
                'quotation_date' => $data['quotation_date'],
                'expiry_date' => $data['expiry_date'],

                'reference' => $data['reference'] ?? null,

                'subtotal' => $subtotal,

                'discount_type' => 'NONE',
                'discount_value' => 0,
                'discount_amount' => $discountTotal,

                'tax_percentage' => 0,
                'tax_amount' => $taxTotal,

                'additional_charges' => 0,
                'grand_total' => $grandTotal,

                'status' => 'DRAFT',

                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,

                'template_id' => $template->id,

                'company_snapshot' => json_encode($company),
                'customer_snapshot' => json_encode($customer),
                'template_snapshot' => json_encode($template),

                'created_by' => auth()->id(),

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $item) {

                DB::table('quotation_items')->insert([
                    'quotation_id' => $quotationId,
                    ...$item,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('companies')
                ->where('id', $company->id)
                ->increment('quotation_next_number');
        });

        return redirect()
            ->route('quotations.index', [
                'company_id' => $data['company_id']
            ])
            ->with('success', 'Quotation created successfully.');
    }
}
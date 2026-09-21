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
                fn ($q) =>
                    $q->where(
                        'quotations.company_id',
                        $companyId
                    ),
                fn ($q) =>
                    $q->whereRaw('1=0')
            )

            ->when(
                $request->search,
                fn ($q, $value) =>
                    $q->where(
                        'quotation_number',
                        'like',
                        "%{$value}%"
                    )
            )

            ->when(
                $request->status,
                fn ($q, $value) =>
                    $q->where(
                        'quotations.status',
                        $value
                    )
            )

            ->when(
                $request->customer_id,
                fn ($q, $value) =>
                    $q->where(
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
        $companyList = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

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


            abort_if(!$company, 404);


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


            if (!$customer) {

                throw ValidationException::withMessages([
                    'customer_id' =>
                        'Invalid customer.',
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


            if (!$template) {

                throw ValidationException::withMessages([
                    'template_id' =>
                        'Invalid quotation template.',
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
                . '-'
                . now()->format('Y')
                . '-'
                . str_pad(
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

                $tax =
                    (float) (
                        $item['tax'] ?? 0
                    );


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

                    'sort_order' =>
                        $i + 1,

                    'item_name' =>
                        $item['item_name'],

                    'description' =>
                        $item['description'] ?? null,

                    'quantity' =>
                        $qty,

                    'unit' =>
                        null,

                    'unit_price' =>
                        $price,

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

                        'company_id' =>
                            $company->id,

                        'customer_id' =>
                            $customer->id,

                        'quotation_number' =>
                            $number,

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
                                $company
                            ),

                        'customer_snapshot' =>
                            json_encode(
                                $customer
                            ),

                        'template_snapshot' =>
                            json_encode(
                                $template
                            ),

                        'created_by' =>
                            auth()->id(),

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);


            /*
            |--------------------------------------------------------------------------
            | Insert Items
            |--------------------------------------------------------------------------
            */

            foreach ($items as $item) {

                DB::table('quotation_items')
                    ->insert([

                        'quotation_id' =>
                            $quotationId,

                        ...$item,

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
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

                    'quotation_next_number' =>
                        $company->quotation_next_number + 1,
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
            ->where(
                'quotations.id',
                $id
            )
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


        return view('quotations.show', compact(
            'quotation',
            'company',
            'customer',
            'items',
            'template'
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
                'nullable|date',

            'reference' =>
                'nullable|string',

            'notes' =>
                'nullable|string',

            'terms_conditions' =>
                'nullable|string',

            'items' =>
                'required|array|min:1',

            'items.*.item_name' =>
                'required|string',

            'items.*.description' =>
                'nullable|string',

            'items.*.quantity' =>
                'required|numeric',

            'items.*.unit_price' =>
                'required|numeric',

            'items.*.discount' =>
                'nullable|numeric',

            'items.*.tax' =>
                'nullable|numeric',
        ]);


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
            !$company
            || !$customer
            || !$template,
            404
        );


        /*
        |--------------------------------------------------------------------------
        | Calculate Preview
        |--------------------------------------------------------------------------
        */

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

            $tax =
                (float) (
                    $item['tax'] ?? 0
                );


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

                'item_name' =>
                    $item['item_name'],

                'description' =>
                    $item['description'] ?? '',

                'quantity' =>
                    $qty,

                'unit_price' =>
                    $price,

                'discount' =>
                    $discount,

                'tax' =>
                    $tax,

                'line_total' =>
                    $lineTotal,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | Preview Object
        |--------------------------------------------------------------------------
        */

        $quotation = (object) [

            'quotation_number' =>
                'PREVIEW',

            'quotation_date' =>
                $data['quotation_date'],

            'expiry_date' =>
                $data['expiry_date'] ?? null,

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

            'grand_total' =>
                $subtotal
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
            $quotation->quotation_number
            . '.pdf'
        );
    }
}
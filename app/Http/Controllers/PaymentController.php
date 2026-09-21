<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $companyList = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

        $companyId = $request->integer('company_id')
            ?: optional($companyList->first())->id;


        $payments = DB::table('payments')
            ->join(
                'invoices',
                'payments.invoice_id',
                '=',
                'invoices.id'
            )
            ->join(
                'customers',
                'invoices.customer_id',
                '=',
                'customers.id'
            )
            ->when(
                $companyId,
                function ($query) use ($companyId) {
                    $query->where(
                        'invoices.company_id',
                        $companyId
                    );
                },
                function ($query) {
                    $query->whereRaw('1 = 0');
                }
            )
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {

                    $search = trim($request->search);

                    $query->where(function ($query) use ($search) {

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
                            )
                            ->orWhere(
                                'payments.reference',
                                'like',
                                "%{$search}%"
                            );

                    });
                }
            )
            ->when(
                $request->filled('payment_method'),
                function ($query) use ($request) {

                    $query->where(
                        'payments.payment_method',
                        $request->payment_method
                    );
                }
            )
            ->select(
                'payments.id',
                'payments.invoice_id',
                'payments.payment_date',
                'payments.amount',
                'payments.payment_method',
                'payments.reference',
                'payments.notes',
                'payments.created_at',

                'invoices.invoice_number',
                'invoices.grand_total',
                'invoices.amount_paid',
                'invoices.balance_amount',

                'customers.business_name'
            )
            ->orderByDesc('payments.payment_date')
            ->orderByDesc('payments.id')
            ->paginate(10)
            ->withQueryString();


        return view('payments.index', compact(
            'companyList',
            'companyId',
            'payments'
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

        $invoices = collect();

        $selectedInvoiceId =
            $request->integer('invoice_id') ?: null;


        if ($companyId) {

            $company = DB::table('companies')
                ->where('id', $companyId)
                ->where('status', 'ACTIVE')
                ->first();


            if ($company) {

                $invoices = DB::table('invoices')
                    ->join(
                        'customers',
                        'invoices.customer_id',
                        '=',
                        'customers.id'
                    )
                    ->where(
                        'invoices.company_id',
                        $companyId
                    )
                    ->where(
                        'invoices.status',
                        '!=',
                        'CANCELLED'
                    )
                    ->where(
                        'invoices.balance_amount',
                        '>',
                        0
                    )
                    ->select(
                        'invoices.id',
                        'invoices.invoice_number',
                        'invoices.invoice_date',
                        'invoices.grand_total',
                        'invoices.amount_paid',
                        'invoices.balance_amount',
                        'invoices.status',

                        'customers.business_name'
                    )
                    ->orderByDesc(
                        'invoices.invoice_date'
                    )
                    ->get();
            }
        }


        return view('payments.create', compact(
            'companyList',
            'companyId',
            'company',
            'invoices',
            'selectedInvoiceId'
        ));
    }


    public function store(Request $request)
    {
        $data = $request->validate([

            'company_id' =>
                'required|exists:companies,id',

            'invoice_id' =>
                'required|exists:invoices,id',

            'payment_date' =>
                'required|date',

            'amount' =>
                'required|numeric|gt:0',

            'payment_method' =>
                'required|in:CASH,BANK_TRANSFER,CARD,CHEQUE,OTHER',

            'reference' =>
                'nullable|string|max:255',

            'notes' =>
                'nullable|string',
        ]);


        return DB::transaction(function () use ($data) {

            /*
             * Lock invoice so two payments cannot
             * incorrectly update the same balance
             * simultaneously.
             */
            $invoice = DB::table('invoices')
                ->where('id', $data['invoice_id'])
                ->where(
                    'company_id',
                    $data['company_id']
                )
                ->lockForUpdate()
                ->first();


            if (!$invoice) {

                throw ValidationException::withMessages([
                    'invoice_id' =>
                        'Selected invoice is invalid.'
                ]);
            }


            if ($invoice->status === 'CANCELLED') {

                throw ValidationException::withMessages([
                    'invoice_id' =>
                        'Payments cannot be recorded against a cancelled invoice.'
                ]);
            }


            $paymentCents =
                $this->decimalToCents(
                    $data['amount']
                );


            $balanceCents =
                $this->decimalToCents(
                    $invoice->balance_amount
                );


            if ($paymentCents <= 0) {

                throw ValidationException::withMessages([
                    'amount' =>
                        'Payment amount must be greater than zero.'
                ]);
            }


            if ($paymentCents > $balanceCents) {

                throw ValidationException::withMessages([
                    'amount' =>
                        'Payment amount cannot exceed the outstanding invoice balance.'
                ]);
            }


            DB::table('payments')->insert([

                'invoice_id' =>
                    $invoice->id,

                'payment_date' =>
                    $data['payment_date'],

                'amount' =>
                    $this->centsToDecimal(
                        $paymentCents
                    ),

                'payment_method' =>
                    $data['payment_method'],

                'reference' =>
                    $data['reference'] ?? null,

                'notes' =>
                    $data['notes'] ?? null,

                'created_by' =>
                    auth()->id(),

                'created_at' =>
                    now(),

                'updated_at' =>
                    now(),
            ]);


            /*
             * Recalculate payment total from DB.
             * Do not trust a browser-calculated total.
             */
            $totalPaid = DB::table('payments')
                ->where(
                    'invoice_id',
                    $invoice->id
                )
                ->selectRaw(
                    'COALESCE(SUM(amount), 0) AS total_paid'
                )
                ->value('total_paid');


            $totalPaidCents =
                $this->decimalToCents(
                    $totalPaid
                );


            $grandTotalCents =
                $this->decimalToCents(
                    $invoice->grand_total
                );


            $newBalanceCents =
                max(
                    0,
                    $grandTotalCents
                    - $totalPaidCents
                );


            if ($newBalanceCents === 0) {

                $newStatus = 'PAID';

            } elseif ($totalPaidCents > 0) {

                $newStatus = 'PARTIALLY_PAID';

            } else {

                $newStatus = $invoice->status;
            }


            DB::table('invoices')
                ->where('id', $invoice->id)
                ->update([

                    'amount_paid' =>
                        $this->centsToDecimal(
                            $totalPaidCents
                        ),

                    'balance_amount' =>
                        $this->centsToDecimal(
                            $newBalanceCents
                        ),

                    'status' =>
                        $newStatus,

                    'updated_at' =>
                        now(),
                ]);


            return redirect()
                ->route(
                    'payments.index',
                    [
                        'company_id' =>
                            $invoice->company_id
                    ]
                )
                ->with(
                    'success',
                    'Payment recorded successfully.'
                );
        });
    }


    private function decimalToCents(
        mixed $value
    ): int
    {
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
                explode(
                    '.',
                    $value,
                    2
                ),
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
                    2,
                    '0'
                ),
                0,
                2
            );


        $cents =
            ((int) ($whole ?: 0) * 100)
            + (int) ($fraction ?: 0);


        return $negative
            ? -$cents
            : $cents;
    }


    private function centsToDecimal(
        int $cents
    ): string
    {
        $negative =
            $cents < 0;


        $cents =
            abs($cents);


        return
            ($negative ? '-' : '')
            . intdiv($cents, 100)
            . '.'
            . str_pad(
                (string) ($cents % 100),
                2,
                '0',
                STR_PAD_LEFT
            );
    }
}
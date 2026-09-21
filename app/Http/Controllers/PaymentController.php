<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $currency = $companyId
            ? DB::table('companies')
                ->where('id', $companyId)
                ->value('currency') ?? 'LKR'
            : 'LKR';

        $stats = [
            'outstanding_amount' => 0,
            'outstanding_count' => 0,

            'total_received' => 0,
            'payment_count' => 0,

            'completed_count' => 0,
            'completed_amount' => 0,

            'overdue_count' => 0,
            'overdue_amount' => 0,

            'pending_count' => 0,
            'pending_amount' => 0,

            'month_received' => 0,
            'month_count' => 0,
        ];

        if ($companyId) {

            /*
            |--------------------------------------------------------------------------
            | Outstanding
            |--------------------------------------------------------------------------
            */

            $outstanding = DB::table('invoices')
                ->where('company_id', $companyId)
                ->where('balance_amount', '>', 0)
                ->whereNotIn('status', [
                    'DRAFT',
                    'CANCELLED'
                ]);

            $stats['outstanding_amount'] =
                (clone $outstanding)
                    ->sum('balance_amount');

            $stats['outstanding_count'] =
                (clone $outstanding)
                    ->count();


            /*
            |--------------------------------------------------------------------------
            | Total Payments Received
            |--------------------------------------------------------------------------
            */

            $received = DB::table('payments')
                ->join(
                    'invoices',
                    'payments.invoice_id',
                    '=',
                    'invoices.id'
                )
                ->where(
                    'invoices.company_id',
                    $companyId
                );

            $stats['total_received'] =
                (clone $received)
                    ->sum('payments.amount');

            $stats['payment_count'] =
                (clone $received)
                    ->count();


            /*
            |--------------------------------------------------------------------------
            | Completed
            |--------------------------------------------------------------------------
            */

            $completed = DB::table('invoices')
                ->where('company_id', $companyId)
                ->where('status', 'PAID');

            $stats['completed_count'] =
                (clone $completed)
                    ->count();

            $stats['completed_amount'] =
                (clone $completed)
                    ->sum('grand_total');


            /*
            |--------------------------------------------------------------------------
            | Overdue
            |--------------------------------------------------------------------------
            */

            $overdue = DB::table('invoices')
                ->where('company_id', $companyId)
                ->whereDate('due_date', '<', today())
                ->where('balance_amount', '>', 0)
                ->whereNotIn('status', [
                    'DRAFT',
                    'PAID',
                    'CANCELLED'
                ]);

            $stats['overdue_count'] =
                (clone $overdue)
                    ->count();

            $stats['overdue_amount'] =
                (clone $overdue)
                    ->sum('balance_amount');


            /*
            |--------------------------------------------------------------------------
            | Pending
            |--------------------------------------------------------------------------
            */

            $pending = DB::table('invoices')
                ->where('company_id', $companyId)
                ->where('balance_amount', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('due_date')
                        ->orWhereDate(
                            'due_date',
                            '>=',
                            today()
                        );
                })
                ->whereNotIn('status', [
                    'DRAFT',
                    'PAID',
                    'CANCELLED'
                ]);

            $stats['pending_count'] =
                (clone $pending)
                    ->count();

            $stats['pending_amount'] =
                (clone $pending)
                    ->sum('balance_amount');


            /*
            |--------------------------------------------------------------------------
            | Received This Month
            |--------------------------------------------------------------------------
            */

            $monthStart =
                now()->startOfMonth()->toDateString();

            $monthEnd =
                now()->endOfMonth()->toDateString();

            $monthPayments = DB::table('payments')
                ->join(
                    'invoices',
                    'payments.invoice_id',
                    '=',
                    'invoices.id'
                )
                ->where(
                    'invoices.company_id',
                    $companyId
                )
                ->whereBetween(
                    'payments.payment_date',
                    [$monthStart, $monthEnd]
                );

            $stats['month_received'] =
                (clone $monthPayments)
                    ->sum('payments.amount');

            $stats['month_count'] =
                (clone $monthPayments)
                    ->count();
        }


        /*
        |--------------------------------------------------------------------------
        | Payment History
        |--------------------------------------------------------------------------
        */

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
            ->leftJoin(
                'users',
                'payments.created_by',
                '=',
                'users.id'
            )
            ->when(
                $companyId,
                fn ($q) =>
                    $q->where(
                        'invoices.company_id',
                        $companyId
                    ),
                fn ($q) =>
                    $q->whereRaw('1 = 0')
            )
            ->when(
                $request->search,
                function ($q, $search) {

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
                $request->payment_method,
                fn ($q, $method) =>
                    $q->where(
                        'payments.payment_method',
                        $method
                    )
            )
            ->when(
                $request->status,
                fn ($q, $status) =>
                    $q->where(
                        'invoices.status',
                        $status
                    )
            )
            ->when(
                $request->from_date,
                fn ($q, $date) =>
                    $q->whereDate(
                        'payments.payment_date',
                        '>=',
                        $date
                    )
            )
            ->when(
                $request->to_date,
                fn ($q, $date) =>
                    $q->whereDate(
                        'payments.payment_date',
                        '<=',
                        $date
                    )
            )
            ->select(
                'payments.id',
                'payments.invoice_id',
                'payments.payment_date',
                'payments.amount',
                'payments.payment_method',
                'payments.reference',
                'payments.notes',

                'invoices.invoice_number',
                'invoices.invoice_date',
                'invoices.due_date',
                'invoices.grand_total',
                'invoices.amount_paid',
                'invoices.balance_amount',
                'invoices.status',

                'customers.business_name',
                'customers.customer_name',

                'users.name as recorded_by'
            )
            ->orderByDesc('payments.payment_date')
            ->orderByDesc('payments.id')
            ->paginate(15)
            ->withQueryString();


        return view('payments.index', compact(
            'companyList',
            'companyId',
            'currency',
            'stats',
            'payments'
        ));
    }
}
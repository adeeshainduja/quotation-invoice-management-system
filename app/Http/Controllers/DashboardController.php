<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $companies = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

        $companyId = $request->integer('company_id')
            ?: optional($companies->first())->id;

        $company = $companies->firstWhere('id', $companyId);

        $query = fn ($table) => DB::table($table)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId));

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $previousStart = now()->subMonth()->startOfMonth();
        $previousEnd = now()->subMonth()->endOfMonth();

        $growth = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }

            return round((($current - $previous) / $previous) * 100);
        };

        // Cards
        $companyCount = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->count();

        $customerCount = $query('customers')->count();

        $quotationCount = $query('quotations')
            ->whereBetween('quotation_date', [$start, $end])
            ->count();

        $invoiceCount = $query('invoices')
            ->whereBetween('invoice_date', [$start, $end])
            ->count();

        $quotationPrevious = $query('quotations')
            ->whereBetween('quotation_date', [$previousStart, $previousEnd])
            ->count();

        $invoicePrevious = $query('invoices')
            ->whereBetween('invoice_date', [$previousStart, $previousEnd])
            ->count();

        // Monthly chart
        $monthlyLabels = [];
        $monthlyQuotations = [];
        $monthlyInvoices = [];

        for ($i = 8; $i >= 0; $i--) {

            $month = now()->subMonths($i);

            $monthlyLabels[] = $month->format('M');

            $monthlyQuotations[] = $query('quotations')
                ->whereBetween('quotation_date', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth()
                ])
                ->count();

            $monthlyInvoices[] = $query('invoices')
                ->whereBetween('invoice_date', [
                    $month->copy()->startOfMonth(),
                    $month->copy()->endOfMonth()
                ])
                ->count();
        }

        // Invoice status chart
        $invoiceStatus = $query('invoices')
            ->selectRaw("
                SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END) as paid,
                SUM(CASE WHEN status = 'PARTIALLY_PAID' THEN 1 ELSE 0 END) as partial,
                SUM(CASE WHEN status NOT IN ('PAID','PARTIALLY_PAID','CANCELLED') THEN 1 ELSE 0 END) as unpaid
            ")
            ->first();

        // Recent quotations
        $recentQuotations = DB::table('quotations')
            ->join('customers', 'quotations.customer_id', '=', 'customers.id')
            ->when($companyId, fn ($q) =>
                $q->where('quotations.company_id', $companyId)
            )
            ->select(
                'quotations.quotation_number',
                'quotations.quotation_date',
                'quotations.expiry_date',
                'quotations.grand_total',
                'quotations.status',
                'customers.business_name'
            )
            ->orderByDesc('quotations.quotation_date')
            ->limit(5)
            ->get();

        // Recent invoices
        $recentInvoices = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->when($companyId, fn ($q) =>
                $q->where('invoices.company_id', $companyId)
            )
            ->select(
                'invoices.invoice_number',
                'invoices.invoice_date',
                'invoices.grand_total',
                'invoices.status',
                'customers.business_name'
            )
            ->orderByDesc('invoices.invoice_date')
            ->limit(5)
            ->get();

        // Outstanding
        $outstandingQuery = $query('invoices')
            ->whereNotIn('status', ['PAID', 'CANCELLED'])
            ->where('balance_amount', '>', 0);

        $outstandingAmount = (clone $outstandingQuery)->sum('balance_amount');
        $outstandingCount = (clone $outstandingQuery)->count();

        // Overdue
        $overdueQuery = $query('invoices')
            ->whereNotIn('status', ['PAID', 'CANCELLED'])
            ->where('balance_amount', '>', 0)
            ->where('due_date', '<', now()->toDateString());

        $overdueAmount = (clone $overdueQuery)->sum('balance_amount');
        $overdueCount = (clone $overdueQuery)->count();

        // Payments this month
        $payments = DB::table('payments')
            ->join('invoices', 'payments.invoice_id', '=', 'invoices.id')
            ->when($companyId, fn ($q) =>
                $q->where('invoices.company_id', $companyId)
            )
            ->whereBetween('payments.payment_date', [$start, $end])
            ->sum('payments.amount');

        return view('dashboard.index', [
            'companies' => $companies,
            'company' => $company,
            'companyId' => $companyId,

            'companyCount' => $companyCount,
            'customerCount' => $customerCount,
            'quotationCount' => $quotationCount,
            'invoiceCount' => $invoiceCount,

            'quotationGrowth' => $growth($quotationCount, $quotationPrevious),
            'invoiceGrowth' => $growth($invoiceCount, $invoicePrevious),

            'monthlyLabels' => $monthlyLabels,
            'monthlyQuotations' => $monthlyQuotations,
            'monthlyInvoices' => $monthlyInvoices,

            'invoiceStatus' => $invoiceStatus,

            'recentQuotations' => $recentQuotations,
            'recentInvoices' => $recentInvoices,

            'outstandingAmount' => $outstandingAmount,
            'outstandingCount' => $outstandingCount,

            'overdueAmount' => $overdueAmount,
            'overdueCount' => $overdueCount,

            'payments' => $payments,
        ]);
    }
}
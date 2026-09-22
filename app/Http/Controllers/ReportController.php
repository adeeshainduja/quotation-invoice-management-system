<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display a summary of system reports.
     */
    public function index(Request $request): View
    {
        $companies = Company::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name', 'currency']);

        $companyId = $request->filled('company_id')
            ? $request->integer('company_id')
            : optional($companies->first())->id;

        $selectedCompany = $companies->firstWhere('id', $companyId);
        $currency = $selectedCompany->currency ?? 'LKR';

        // Base queries using existing models and relationships
        $invoiceQuery = Invoice::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId));

        $totalInvoicesCount = (clone $invoiceQuery)
            ->where('status', '!=', 'CANCELLED')
            ->count();

        $totalInvoiceValue = (clone $invoiceQuery)
            ->where('status', '!=', 'CANCELLED')
            ->sum('grand_total');

        $outstandingInvoiceAmount = (clone $invoiceQuery)
            ->whereNotIn('status', ['PAID', 'CANCELLED'])
            ->where('balance_amount', '>', 0)
            ->sum('balance_amount');

        $totalPaymentsReceived = Payment::query()
            ->when($companyId, function ($query) use ($companyId) {
                $query->whereHas('invoice', fn ($invoiceQuery) => $invoiceQuery->where('company_id', $companyId));
            })
            ->sum('amount');

        $totalQuotationsCount = Quotation::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->count();

        $totalCustomersCount = Customer::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->count();

        return view('reports.index', [
            'companies' => $companies,
            'companyId' => $companyId,
            'selectedCompany' => $selectedCompany,
            'currency' => $currency,
            'totalInvoicesCount' => $totalInvoicesCount,
            'totalInvoiceValue' => (float) $totalInvoiceValue,
            'totalPaymentsReceived' => (float) $totalPaymentsReceived,
            'outstandingInvoiceAmount' => (float) $outstandingInvoiceAmount,
            'totalQuotationsCount' => $totalQuotationsCount,
            'totalCustomersCount' => $totalCustomersCount,
        ]);
    }

    /**
     * Display the detailed Invoice Report.
     */
    public function invoiceReport(Request $request): View
    {
        $companies = Company::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name', 'currency']);

        $companyId = $request->filled('company_id')
            ? $request->integer('company_id')
            : null;

        $selectedCompany = $companyId ? $companies->firstWhere('id', $companyId) : null;
        $currency = $selectedCompany->currency ?? ($companies->first()->currency ?? 'LKR');

        $from = $request->query('from') ?: $request->query('from_date');
        $to = $request->query('to') ?: $request->query('to_date');
        $customerId = $request->filled('customer_id') ? $request->integer('customer_id') : null;
        $status = $request->query('status');

        $customers = Customer::when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'customer_name', 'company_id']);

        // Build filtered query using existing Invoice model
        $query = Invoice::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($from, fn ($q) => $q->whereDate('invoice_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('invoice_date', '<=', $to));

        if ($status) {
            if ($status === 'OVERDUE') {
                $query->whereNotIn('status', ['PAID', 'CANCELLED'])
                    ->where('balance_amount', '>', 0)
                    ->whereDate('due_date', '<', now()->toDateString());
            } elseif ($status === 'PARTIAL' || $status === 'PARTIALLY_PAID') {
                $query->whereIn('status', ['PARTIAL', 'PARTIALLY_PAID']);
            } elseif ($status === 'PENDING') {
                $query->whereNotIn('status', ['PAID', 'CANCELLED'])
                    ->where('balance_amount', '>', 0);
            } else {
                $query->where('status', $status);
            }
        }

        // Calculate Summary Cards
        $totalInvoices = (clone $query)->count();
        $totalInvoiceAmount = (float) (clone $query)->sum('grand_total');
        $paidAmount = (float) (clone $query)->sum('amount_paid');
        $outstandingAmount = (float) (clone $query)->sum('balance_amount');

        $paidInvoices = (clone $query)
            ->where(function ($q) {
                $q->where('status', 'PAID')
                    ->orWhere(function ($sub) {
                        $sub->where('balance_amount', '<=', 0)
                            ->where('status', '!=', 'CANCELLED');
                    });
            })
            ->count();

        $pendingInvoices = (clone $query)
            ->whereNotIn('status', ['PAID', 'CANCELLED'])
            ->where('balance_amount', '>', 0)
            ->count();

        $overdueInvoices = (clone $query)
            ->whereNotIn('status', ['PAID', 'CANCELLED'])
            ->where('balance_amount', '>', 0)
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        $invoices = (clone $query)
            ->with(['customer', 'company'])
            ->orderByDesc('invoice_date')
            ->paginate(15)
            ->withQueryString();

        return view('reports.invoices', [
            'invoices' => $invoices,
            'companies' => $companies,
            'customers' => $customers,
            'companyId' => $companyId,
            'customerId' => $customerId,
            'from' => $from,
            'to' => $to,
            'status' => $status,
            'currency' => $currency,
            'totalInvoices' => $totalInvoices,
            'totalInvoiceAmount' => $totalInvoiceAmount,
            'paidAmount' => $paidAmount,
            'outstandingAmount' => $outstandingAmount,
            'paidInvoices' => $paidInvoices,
            'pendingInvoices' => $pendingInvoices,
            'overdueInvoices' => $overdueInvoices,
        ]);
    }
}

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
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        if ($request->filled('company_id')) {
            abort_if(! $user->hasCompanyAccess($request->integer('company_id')), 403, 'Unauthorized company access.');
        }

        $companies = $user->isAdmin()
            ? Company::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'currency'])
            : $user->accessibleCompanies()->where('companies.status', 'ACTIVE')->orderBy('name')->get(['companies.id', 'companies.name', 'companies.currency']);

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
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        if ($request->filled('company_id')) {
            abort_if(! $user->hasCompanyAccess($request->integer('company_id')), 403, 'Unauthorized company access.');
        }

        $companies = $user->isAdmin()
            ? Company::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'currency'])
            : $user->accessibleCompanies()->where('companies.status', 'ACTIVE')->orderBy('name')->get(['companies.id', 'companies.name', 'companies.currency']);

        $companyId = $request->filled('company_id')
            ? $request->integer('company_id')
            : ($user->isAdmin() ? null : optional($companies->first())->id);

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

    /**
     * Display the detailed Payment Report.
     */
    public function paymentReport(Request $request): View
    {
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        if ($request->filled('company_id')) {
            abort_if(! $user->hasCompanyAccess($request->integer('company_id')), 403, 'Unauthorized company access.');
        }

        $companies = $user->isAdmin()
            ? Company::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'currency'])
            : $user->accessibleCompanies()->where('companies.status', 'ACTIVE')->orderBy('name')->get(['companies.id', 'companies.name', 'companies.currency']);

        $companyId = $request->filled('company_id')
            ? $request->integer('company_id')
            : ($user->isAdmin() ? null : optional($companies->first())->id);

        $selectedCompany = $companyId ? $companies->firstWhere('id', $companyId) : null;
        $currency = $selectedCompany->currency ?? ($companies->first()->currency ?? 'LKR');

        $from = $request->query('from') ?: $request->query('from_date');
        $to = $request->query('to') ?: $request->query('to_date');
        $customerId = $request->filled('customer_id') ? $request->integer('customer_id') : null;
        $paymentMethod = $request->query('payment_method');

        $customers = Customer::when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'customer_name', 'company_id']);

        // Build filtered query using existing Payment model
        $query = Payment::query()
            ->when($companyId, function ($q) use ($companyId) {
                $q->whereHas('invoice', fn ($iq) => $iq->where('company_id', $companyId));
            })
            ->when($customerId, function ($q) use ($customerId) {
                $q->whereHas('invoice', fn ($iq) => $iq->where('customer_id', $customerId));
            })
            ->when($from, fn ($q) => $q->whereDate('payment_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('payment_date', '<=', $to))
            ->when($paymentMethod, fn ($q) => $q->where('payment_method', $paymentMethod));

        // Calculate Summary Cards
        $totalPaymentsReceived = (float) (clone $query)->sum('amount');
        $numberOfPayments = (clone $query)->count();

        $thisMonthPayments = (float) (clone $query)
            ->whereBetween('payment_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');

        $cashPayments = (float) (clone $query)
            ->where('payment_method', 'CASH')
            ->sum('amount');

        $bankPayments = (float) (clone $query)
            ->where('payment_method', 'BANK_TRANSFER')
            ->sum('amount');

        $cardPayments = (float) (clone $query)
            ->where('payment_method', 'CARD')
            ->sum('amount');

        $outstandingInvoiceAmount = (float) Invoice::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->whereNotIn('status', ['PAID', 'CANCELLED'])
            ->where('balance_amount', '>', 0)
            ->sum('balance_amount');

        $payments = (clone $query)
            ->with(['invoice.customer', 'invoice.company'])
            ->orderByDesc('payment_date')
            ->paginate(15)
            ->withQueryString();

        return view('reports.payments', [
            'payments' => $payments,
            'companies' => $companies,
            'customers' => $customers,
            'companyId' => $companyId,
            'customerId' => $customerId,
            'from' => $from,
            'to' => $to,
            'paymentMethod' => $paymentMethod,
            'currency' => $currency,
            'totalPaymentsReceived' => $totalPaymentsReceived,
            'numberOfPayments' => $numberOfPayments,
            'thisMonthPayments' => $thisMonthPayments,
            'cashPayments' => $cashPayments,
            'bankPayments' => $bankPayments,
            'cardPayments' => $cardPayments,
            'outstandingInvoiceAmount' => $outstandingInvoiceAmount,
        ]);
    }

    /**
     * Display the detailed Quotation Report.
     */
    public function quotationReport(Request $request): View
    {
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        if ($request->filled('company_id')) {
            abort_if(! $user->hasCompanyAccess($request->integer('company_id')), 403, 'Unauthorized company access.');
        }

        $companies = $user->isAdmin()
            ? Company::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'currency'])
            : $user->accessibleCompanies()->where('companies.status', 'ACTIVE')->orderBy('name')->get(['companies.id', 'companies.name', 'companies.currency']);

        $companyId = $request->filled('company_id')
            ? $request->integer('company_id')
            : ($user->isAdmin() ? null : optional($companies->first())->id);

        $selectedCompany = $companyId ? $companies->firstWhere('id', $companyId) : null;
        $currency = $selectedCompany->currency ?? ($companies->first()->currency ?? 'LKR');

        $from = $request->query('from') ?: $request->query('from_date');
        $to = $request->query('to') ?: $request->query('to_date');
        $customerId = $request->filled('customer_id') ? $request->integer('customer_id') : null;
        $status = $request->query('status');

        $customers = Customer::when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'customer_name', 'company_id']);

        // Build filtered query using existing Quotation model
        $query = Quotation::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($from, fn ($q) => $q->whereDate('quotation_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('quotation_date', '<=', $to))
            ->when($status, fn ($q) => $q->where('status', $status));

        // Calculate Summary Cards
        $totalQuotations = (clone $query)->count();
        $totalQuotationValue = (float) (clone $query)->sum('grand_total');

        $acceptedQuotations = (clone $query)->where('status', 'ACCEPTED')->count();
        $rejectedQuotations = (clone $query)->where('status', 'REJECTED')->count();

        $pendingQuotations = (clone $query)
            ->whereIn('status', ['DRAFT', 'SENT'])
            ->count();

        $expiredQuotations = (clone $query)->where('status', 'EXPIRED')->count();

        $convertedQuotations = (clone $query)
            ->where(function ($q) {
                $q->where('status', 'CONVERTED')
                    ->orWhereHas('invoice');
            })
            ->count();

        $quotations = (clone $query)
            ->with(['customer', 'company', 'invoice'])
            ->orderByDesc('quotation_date')
            ->paginate(15)
            ->withQueryString();

        return view('reports.quotations', [
            'quotations' => $quotations,
            'companies' => $companies,
            'customers' => $customers,
            'companyId' => $companyId,
            'customerId' => $customerId,
            'from' => $from,
            'to' => $to,
            'status' => $status,
            'currency' => $currency,
            'totalQuotations' => $totalQuotations,
            'totalQuotationValue' => $totalQuotationValue,
            'acceptedQuotations' => $acceptedQuotations,
            'rejectedQuotations' => $rejectedQuotations,
            'pendingQuotations' => $pendingQuotations,
            'expiredQuotations' => $expiredQuotations,
            'convertedQuotations' => $convertedQuotations,
        ]);
    }

    /**
     * Display the detailed Customer Report.
     */
    public function customerReport(Request $request): View
    {
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        if ($request->filled('company_id')) {
            abort_if(! $user->hasCompanyAccess($request->integer('company_id')), 403, 'Unauthorized company access.');
        }

        $companies = $user->isAdmin()
            ? Company::where('status', 'ACTIVE')->orderBy('name')->get(['id', 'name', 'currency'])
            : $user->accessibleCompanies()->where('companies.status', 'ACTIVE')->orderBy('name')->get(['companies.id', 'companies.name', 'companies.currency']);

        $companyId = $request->filled('company_id')
            ? $request->integer('company_id')
            : ($user->isAdmin() ? null : optional($companies->first())->id);

        $selectedCompany = $companyId ? $companies->firstWhere('id', $companyId) : null;
        $currency = $selectedCompany->currency ?? ($companies->first()->currency ?? 'LKR');

        $from = $request->query('from') ?: $request->query('from_date');
        $to = $request->query('to') ?: $request->query('to_date');
        $customerId = $request->filled('customer_id') ? $request->integer('customer_id') : null;

        // Build customer base query
        $customerQuery = Customer::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($customerId, fn ($q) => $q->where('id', $customerId));

        // Build invoice query scoped to filters for aggregated metrics
        $invoiceQuery = Invoice::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($from, fn ($q) => $q->whereDate('invoice_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('invoice_date', '<=', $to))
            ->where('status', '!=', 'CANCELLED');

        // Build payment query scoped to filters
        $paymentQuery = Payment::query()
            ->when($companyId, fn ($q) => $q->whereHas('invoice', fn ($iq) => $iq->where('company_id', $companyId)))
            ->when($customerId, fn ($q) => $q->whereHas('invoice', fn ($iq) => $iq->where('customer_id', $customerId)))
            ->when($from, fn ($q) => $q->whereDate('payment_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('payment_date', '<=', $to));

        // --- Summary Cards ---
        $totalCustomers = (clone $customerQuery)->count();
        $activeCustomers = (clone $customerQuery)->where('status', 'ACTIVE')->count();
        $totalCustomerRevenue = (float) (clone $invoiceQuery)->sum('grand_total');
        $totalPaymentsReceived = (float) (clone $paymentQuery)->sum('amount');

        $outstandingBalance = (float) Invoice::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->whereNotIn('status', ['PAID', 'CANCELLED'])
            ->where('balance_amount', '>', 0)
            ->sum('balance_amount');

        $totalQuotationsGenerated = Quotation::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->count();

        $averageCustomerValue = $totalCustomers > 0
            ? round($totalCustomerRevenue / $totalCustomers, 2)
            : 0.0;

        // --- Customer Detail Table ---
        $customers = (clone $customerQuery)
            ->with('company')
            ->withCount([
                'invoices as invoice_count' => fn ($q) => $q
                    ->when($from, fn ($iq) => $iq->whereDate('invoice_date', '>=', $from))
                    ->when($to, fn ($iq) => $iq->whereDate('invoice_date', '<=', $to))
                    ->where('status', '!=', 'CANCELLED'),
                'quotations as quotation_count' => fn ($q) => $q
                    ->when($from, fn ($qq) => $qq->whereDate('quotation_date', '>=', $from))
                    ->when($to, fn ($qq) => $qq->whereDate('quotation_date', '<=', $to)),
            ])
            ->withSum(
                ['invoices as total_purchase_value' => fn ($q) => $q
                    ->when($from, fn ($iq) => $iq->whereDate('invoice_date', '>=', $from))
                    ->when($to, fn ($iq) => $iq->whereDate('invoice_date', '<=', $to))
                    ->where('status', '!=', 'CANCELLED'),
                ],
                'grand_total'
            )
            ->withSum(
                ['invoices as total_amount_paid' => fn ($q) => $q
                    ->when($from, fn ($iq) => $iq->whereDate('invoice_date', '>=', $from))
                    ->when($to, fn ($iq) => $iq->whereDate('invoice_date', '<=', $to))
                    ->where('status', '!=', 'CANCELLED'),
                ],
                'amount_paid'
            )
            ->withSum(
                ['invoices as total_balance' => fn ($q) => $q
                    ->whereNotIn('status', ['PAID', 'CANCELLED'])
                    ->where('balance_amount', '>', 0),
                ],
                'balance_amount'
            )
            ->withMax(
                ['invoices as last_invoice_date' => fn ($q) => $q
                    ->where('status', '!=', 'CANCELLED'),
                ],
                'invoice_date'
            )
            ->orderByDesc('total_purchase_value')
            ->paginate(15)
            ->withQueryString();

        // --- Top Customers by Revenue (unfiltered by date for overview) ---
        $topCustomers = Customer::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->with('company')
            ->withSum(
                ['invoices as total_revenue' => fn ($q) => $q->where('status', '!=', 'CANCELLED')],
                'grand_total'
            )
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // --- Customers With Outstanding Balances ---
        $outstandingCustomers = Customer::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->with('company')
            ->whereHas('invoices', fn ($q) => $q
                ->whereNotIn('status', ['PAID', 'CANCELLED'])
                ->where('balance_amount', '>', 0)
            )
            ->withSum(
                ['invoices as outstanding_amount' => fn ($q) => $q
                    ->whereNotIn('status', ['PAID', 'CANCELLED'])
                    ->where('balance_amount', '>', 0),
                ],
                'balance_amount'
            )
            ->orderByDesc('outstanding_amount')
            ->limit(5)
            ->get();

        $allCustomers = Customer::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'customer_name', 'company_id']);

        return view('reports.customers', [
            'customers' => $customers,
            'topCustomers' => $topCustomers,
            'outstandingCustomers' => $outstandingCustomers,
            'allCustomers' => $allCustomers,
            'companies' => $companies,
            'companyId' => $companyId,
            'customerId' => $customerId,
            'from' => $from,
            'to' => $to,
            'currency' => $currency,
            'totalCustomers' => $totalCustomers,
            'activeCustomers' => $activeCustomers,
            'totalCustomerRevenue' => $totalCustomerRevenue,
            'totalPaymentsReceived' => $totalPaymentsReceived,
            'outstandingBalance' => $outstandingBalance,
            'totalQuotationsGenerated' => $totalQuotationsGenerated,
            'averageCustomerValue' => $averageCustomerValue,
        ]);
    }
}

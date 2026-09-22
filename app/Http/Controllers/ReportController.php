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
}

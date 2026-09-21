<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                ->orderBy('business_name')
                ->get(['id', 'business_name'])
            : collect();

        $invoices = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')

            ->when($companyId, function ($query) use ($companyId) {
                $query->where('invoices.company_id', $companyId);
            })

            ->when(!$companyId, function ($query) {
                $query->whereRaw('1 = 0');
            })

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
                'invoices.paid_amount',
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
}
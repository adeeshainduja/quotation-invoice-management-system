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
}
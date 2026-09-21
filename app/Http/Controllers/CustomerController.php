<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $companyList = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id', 'name']);

        $companyId = $request->integer('company_id')
            ?: optional($companyList->first())->id;

        $customers = DB::table('customers')
            ->where('company_id', $companyId)

            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                      ->orWhere('business_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })

            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })

            ->when($request->city, function ($query, $city) {
                $query->where('city', $city);
            })

            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $cities = DB::table('customers')
            ->where('company_id', $companyId)
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $selectedCompany = $companyList->firstWhere('id', $companyId);

        return view('customers.index', compact(
            'customers',
            'companyList',
            'companyId',
            'selectedCompany',
            'cities'
        ));
    }
}
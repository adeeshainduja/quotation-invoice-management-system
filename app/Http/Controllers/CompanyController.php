<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $companies = DB::table('companies')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('registration_number', 'like', "%{$search}%")
                      ->orWhere('tin_number', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(
                $request->sort === 'oldest',
                fn ($q) => $q->orderBy('created_at'),
                fn ($q) => $q->orderByDesc('created_at')
            )
            ->paginate(10)
            ->withQueryString();

        return view('companies.index', [
            'companies' => $companies,

            'totalCompanies' => DB::table('companies')->count(),

            'activeCompanies' => DB::table('companies')
                ->where('status', 'ACTIVE')
                ->count(),

            'inactiveCompanies' => DB::table('companies')
                ->where('status', 'INACTIVE')
                ->count(),

            'totalUsers' => DB::table('users')->count(),

            'companyList' => DB::table('companies')
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }
}
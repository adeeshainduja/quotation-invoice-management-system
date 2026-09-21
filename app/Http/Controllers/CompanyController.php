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
                      ->orWhere('tin', 'like', "%{$search}%");
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

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:100',
            'tin' => 'nullable|string|max:100',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'logo_path' => 'nullable|image|max:2048',
            'signature_path' => 'nullable|image|max:2048',
            'stamp_path' => 'nullable|image|max:2048',
            'vat_registered' => 'nullable|boolean',
            'vat_number' => 'nullable|string|max:100',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'quotation_prefix' => 'required|string|max:50',
            'quotation_next_number' => 'nullable|integer|min:1',
            'invoice_prefix' => 'required|string|max:50',
            'invoice_next_number' => 'nullable|integer|min:1',
            'currency' => 'required|string|max:10',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        foreach (['logo_path', 'signature_path', 'stamp_path'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('company-assets', 'public');
            }
        }

        $data['logo_path'] = $data['logo_path'] ?? '';
        $data['website'] = $data['website'] ?? '';
        $data['vat_registered'] = $request->boolean('vat_registered');
        $data['quotation_next_number'] = $data['quotation_next_number'] ?? 1;
        $data['invoice_next_number'] = $data['invoice_next_number'] ?? 1;

        DB::table('companies')->insert([
            ...$data,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }
}

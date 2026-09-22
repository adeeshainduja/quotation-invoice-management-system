<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user && $user->isAdmin();

        $companies = DB::table('companies')
            ->when(! $isAdmin, function ($q) use ($user) {
                $q->join('company_user', 'companies.id', '=', 'company_user.company_id')
                    ->where('company_user.user_id', $user->id)
                    ->select('companies.*');
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('companies.name', 'like', "%{$search}%")
                        ->orWhere('companies.registration_number', 'like', "%{$search}%")
                        ->orWhere('companies.tin_number', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('companies.status', $status);
            })
            ->when(
                $request->sort === 'oldest',
                fn ($q) => $q->orderBy('companies.created_at'),
                fn ($q) => $q->orderByDesc('companies.created_at')
            )
            ->paginate(10)
            ->withQueryString();

        $baseCountQuery = DB::table('companies')
            ->when(! $isAdmin, function ($q) use ($user) {
                $q->join('company_user', 'companies.id', '=', 'company_user.company_id')
                    ->where('company_user.user_id', $user->id);
            });

        return view('companies.index', [
            'companies' => $companies,

            'totalCompanies' => (clone $baseCountQuery)->count(),

            'activeCompanies' => (clone $baseCountQuery)
                ->where('companies.status', 'ACTIVE')
                ->count(),

            'inactiveCompanies' => (clone $baseCountQuery)
                ->where('companies.status', 'INACTIVE')
                ->count(),

            'totalUsers' => DB::table('users')->count(),

            'companyList' => (clone $baseCountQuery)
                ->where('companies.status', 'ACTIVE')
                ->orderBy('name')
                ->get(['companies.id', 'companies.name']),
        ]);
    }

    public function create()
    {
        abort_if(! auth()->user() || ! auth()->user()->isAdmin(), 403, 'Administrator access required.');

        return view('companies.create');
    }

    public function store(Request $request)
    {
        abort_if(! auth()->user() || ! auth()->user()->isAdmin(), 403, 'Administrator access required.');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:100',
            'tin' => 'nullable|string|max:100',
            'tin_number' => 'nullable|string|max:100',
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
            'vat_enabled' => 'nullable',
            'vat_registered' => 'nullable',
            'vat_number' => 'nullable|string|max:100',
            'tax_registration_number' => 'nullable|string|max:100',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
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

        $tin = $request->input('tin_number', $request->input('tin'));
        $data['tin_number'] = $tin;
        unset($data['tin']);

        $vatEnabled = $request->boolean('vat_enabled') || $request->boolean('vat_registered');
        $data['vat_enabled'] = $vatEnabled;
        $data['vat_registered'] = $vatEnabled;

        $taxPct = $request->input('tax_percentage', $request->input('vat_percentage'));
        $data['tax_percentage'] = $taxPct;
        $data['vat_percentage'] = $taxPct;

        $data['logo_path'] = $data['logo_path'] ?? '';
        $data['website'] = $data['website'] ?? '';
        $data['quotation_next_number'] = $data['quotation_next_number'] ?? 1;
        $data['invoice_next_number'] = $data['invoice_next_number'] ?? 1;

        DB::table('companies')->insert([
            ...$data,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    public function edit($id)
    {
        abort_if(! auth()->user() || ! auth()->user()->isAdmin(), 403, 'Administrator access required.');

        $company = DB::table('companies')->where('id', $id)->first();
        abort_if(! $company, 404);

        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, $id)
    {
        abort_if(! auth()->user() || ! auth()->user()->isAdmin(), 403, 'Administrator access required.');

        $company = DB::table('companies')->where('id', $id)->first();
        abort_if(! $company, 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:100',
            'tin' => 'nullable|string|max:100',
            'tin_number' => 'nullable|string|max:100',
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
            'vat_enabled' => 'nullable',
            'vat_registered' => 'nullable',
            'vat_number' => 'nullable|string|max:100',
            'tax_registration_number' => 'nullable|string|max:100',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
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
            } else {
                unset($data[$field]);
            }
        }

        $tin = $request->input('tin_number', $request->input('tin'));
        $data['tin_number'] = $tin;
        unset($data['tin']);

        $vatEnabled = $request->boolean('vat_enabled') || $request->boolean('vat_registered');
        $data['vat_enabled'] = $vatEnabled;
        $data['vat_registered'] = $vatEnabled;

        $taxPct = $request->input('tax_percentage', $request->input('vat_percentage'));
        $data['tax_percentage'] = $taxPct;
        $data['vat_percentage'] = $taxPct;

        $data['website'] = $data['website'] ?? '';

        DB::table('companies')->where('id', $id)->update([
            ...$data,
            'updated_at' => now(),
        ]);

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    public function toggleStatus(Request $request, $id)
    {
        abort_if(! auth()->user() || ! auth()->user()->isAdmin(), 403, 'Administrator access required.');

        $company = DB::table('companies')->where('id', $id)->first();
        abort_if(! $company, 404);

        $newStatus = $company->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';

        DB::table('companies')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

        $message = $newStatus === 'ACTIVE'
            ? "Company '{$company->name}' activated successfully."
            : "Company '{$company->name}' deactivated successfully.";

        return back()->with('success', $message);
    }
}

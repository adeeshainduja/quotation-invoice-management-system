<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        if ($request->filled('company_id')) {
            abort_if(! $user->hasCompanyAccess($request->integer('company_id')), 403, 'Unauthorized company access.');
        }

        $companyList = $user->isAdmin()
            ? DB::table('companies')
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get(['id', 'name'])
            : $user->accessibleCompanies()
                ->where('companies.status', 'ACTIVE')
                ->orderBy('name')
                ->get(['companies.id', 'companies.name']);

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

    public function show(Request $request, $id)
    {
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        $customer = DB::table('customers')
            ->where('id', $id)
            ->when($request->integer('company_id'), fn ($query, $companyId) => $query->where('company_id', $companyId))
            ->first();

        abort_if(! $customer, 404);
        abort_if(! $user->hasCompanyAccess($customer->company_id), 403, 'Unauthorized company access.');

        $company = DB::table('companies')
            ->where('id', $customer->company_id)
            ->first();

        $totalQuotations = DB::table('quotations')
            ->where('customer_id', $id)
            ->count();

        $totalInvoices = DB::table('invoices')
            ->where('customer_id', $id)
            ->count();

        $outstandingAmount = DB::table('invoices')
            ->where('customer_id', $id)
            ->whereNotIn('status', ['PAID', 'CANCELLED'])
            ->sum('balance_amount');

        $recentQuotations = DB::table('quotations')
            ->where('customer_id', $id)
            ->orderByDesc('quotation_date')
            ->limit(3)
            ->get();

        return view('customers.show', compact(
            'customer',
            'company',
            'totalQuotations',
            'totalInvoices',
            'outstandingAmount',
            'recentQuotations'
        ));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        if ($request->filled('company_id')) {
            abort_if(! $user->hasCompanyAccess($request->integer('company_id')), 403, 'Unauthorized company access.');
        }

        $companyList = $user->isAdmin()
            ? DB::table('companies')
                ->where('status', 'ACTIVE')
                ->orderBy('name')
                ->get(['id', 'name'])
            : $user->accessibleCompanies()
                ->where('companies.status', 'ACTIVE')
                ->orderBy('name')
                ->get(['companies.id', 'companies.name']);

        $companyId = $request->integer('company_id') ?: optional($companyList->first())->id;

        return view('customers.create', compact(
            'companyList',
            'companyId'
        ));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_if(! $user || $user->status !== 'ACTIVE', 403, 'Your account is deactivated. Please contact admin.');

        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'customer_name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        abort_if(! $user->hasCompanyAccess($data['company_id']), 403, 'Unauthorized company access.');

        $company = DB::table('companies')->where('id', $data['company_id'])->first();
        abort_if(! $company || $company->status !== 'ACTIVE', 403, 'Cannot create transactions for an inactive company.');

        $id = DB::table('customers')->insertGetId([
            ...$data,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log(
            'CREATE',
            'Customer',
            $id,
            $data['company_id'],
            null,
            ['customer_name' => $data['customer_name'], 'business_name' => $data['business_name']]
        );

        return redirect()
            ->route('customers.show', ['id' => $id, 'company_id' => $data['company_id']])
            ->with('success', 'Customer created successfully.');
    }
}

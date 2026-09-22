<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::withCount('permissions')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $companies = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        $invoiceTemplates = DB::table('company_templates')
            ->where('document_type', 'INVOICE')
            ->orderBy('template_name')
            ->get();

        $quotationTemplates = DB::table('company_templates')
            ->where('document_type', 'QUOTATION')
            ->orderBy('template_name')
            ->get();

        return view('users.create', compact('permissions', 'companies', 'invoiceTemplates', 'quotationTemplates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:ACTIVE,INACTIVE',

            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',

            'companies' => 'nullable|array',
            'companies.*' => 'integer|exists:companies,id',

            'templates' => 'nullable|array',
            'templates.*' => 'integer|exists:company_templates,id',
        ]);

        $user = DB::transaction(function () use ($data) {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'USER',
                'status' => $data['status'],
            ]);

            $user->permissions()->sync(
                $data['permissions'] ?? []
            );

            $user->companies()->sync(
                $data['companies'] ?? []
            );

            $user->templates()->sync(
                $data['templates'] ?? []
            );

            return $user;
        });

        ActivityLogger::log(
            'CREATE',
            'User',
            $user->id,
            null,
            null,
            ['name' => $user->name, 'email' => $user->email, 'role' => 'USER', 'status' => $data['status']]
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::with(['permissions', 'companies', 'templates'])
            ->findOrFail($id);

        if ($user->isAdmin()) {
            abort(403, 'Administrator account cannot be edited here.');
        }

        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $companies = DB::table('companies')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        $invoiceTemplates = DB::table('company_templates')
            ->where('document_type', 'INVOICE')
            ->orderBy('template_name')
            ->get();

        $quotationTemplates = DB::table('company_templates')
            ->where('document_type', 'QUOTATION')
            ->orderBy('template_name')
            ->get();

        $selectedPermissions = $user->permissions
            ->pluck('id')
            ->toArray();

        $selectedCompanies = $user->companies
            ->pluck('id')
            ->toArray();

        $selectedTemplates = $user->templates
            ->pluck('id')
            ->toArray();

        return view(
            'users.edit',
            compact(
                'user',
                'permissions',
                'companies',
                'invoiceTemplates',
                'quotationTemplates',
                'selectedPermissions',
                'selectedCompanies',
                'selectedTemplates'
            )
        );
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            abort(403, 'Administrator account cannot be edited here.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:150',

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'password' => 'nullable|string|min:8|confirmed',

            'status' => 'required|in:ACTIVE,INACTIVE',

            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',

            'companies' => 'nullable|array',
            'companies.*' => 'integer|exists:companies,id',

            'templates' => 'nullable|array',
            'templates.*' => 'integer|exists:company_templates,id',
        ]);

        $oldName = $user->name;
        $oldEmail = $user->email;
        $oldStatus = $user->status;
        $oldPermissions = $user->permissions->pluck('id')->map(fn ($v) => (int) $v)->sort()->values()->toArray();
        $oldCompanies = $user->companies->pluck('id')->map(fn ($v) => (int) $v)->sort()->values()->toArray();

        $newPermissions = collect($data['permissions'] ?? [])->map(fn ($v) => (int) $v)->sort()->values()->toArray();
        $newCompanies = collect($data['companies'] ?? [])->map(fn ($v) => (int) $v)->sort()->values()->toArray();

        DB::transaction(function () use ($data, $user) {

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->status = $data['status'];

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->save();

            $user->permissions()->sync(
                $data['permissions'] ?? []
            );

            $user->companies()->sync(
                $data['companies'] ?? []
            );

            $user->templates()->sync(
                $data['templates'] ?? []
            );
        });

        ActivityLogger::log(
            'UPDATE',
            'User',
            $user->id,
            null,
            ['name' => $oldName, 'email' => $oldEmail, 'status' => $oldStatus],
            ['name' => $data['name'], 'email' => $data['email'], 'status' => $data['status']]
        );

        if ($oldPermissions !== $newPermissions) {
            ActivityLogger::log(
                'PERMISSION CHANGE',
                'User',
                $user->id,
                null,
                ['permissions' => $oldPermissions],
                ['permissions' => $newPermissions]
            );
        }

        if ($oldCompanies !== $newCompanies) {
            ActivityLogger::log(
                'COMPANY ACCESS CHANGE',
                'User',
                $user->id,
                null,
                ['companies' => $oldCompanies],
                ['companies' => $newCompanies]
            );
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own administrator account.');
        }

        if ($user->isAdmin()) {
            return back()->with('error', 'Administrator accounts cannot be deactivated.');
        }

        $oldStatus = $user->status;
        $user->status = $user->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $user->save();

        if ($user->status === 'INACTIVE') {
            ActivityLogger::log(
                'DEACTIVATE',
                'User',
                $user->id,
                null,
                ['status' => 'ACTIVE'],
                ['status' => 'INACTIVE']
            );
        } else {
            ActivityLogger::log(
                'UPDATE',
                'User',
                $user->id,
                null,
                ['status' => 'INACTIVE'],
                ['status' => 'ACTIVE']
            );
        }

        $message = $user->status === 'ACTIVE'
            ? "User '{$user->name}' activated successfully."
            : "User '{$user->name}' deactivated successfully.";

        return back()->with('success', $message);
    }
}

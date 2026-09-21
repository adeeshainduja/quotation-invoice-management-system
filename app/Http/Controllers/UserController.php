<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
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

        return view('users.create', compact('permissions'));
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
        ]);


        DB::transaction(function () use ($data) {

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
        });


        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }


    public function edit($id)
    {
        $user = User::with('permissions')
            ->findOrFail($id);

        if ($user->isAdmin()) {
            abort(403, 'Administrator account cannot be edited here.');
        }

        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        $selectedPermissions = $user->permissions
            ->pluck('id')
            ->toArray();

        return view(
            'users.edit',
            compact(
                'user',
                'permissions',
                'selectedPermissions'
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
        ]);


        DB::transaction(function () use ($data, $user) {

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->status = $data['status'];

            if (!empty($data['password'])) {
                $user->password = $data['password'];
            }

            $user->save();

            $user->permissions()->sync(
                $data['permissions'] ?? []
            );
        });


        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }
}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Users & Permissions</title>

    <link
        rel="stylesheet"
        href="{{ asset('css/dashboard.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/users.css') }}"
    >

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

@include('partials.sidebar')


<div class="page content-wrapper">
    @include('partials.topbar')

    <main>

        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span>›</span>
            Users & Permissions
        </div>


        <div class="page-heading">

            <div>
                <h1>Users & Permissions</h1>

                <p>
                    Create users and control what they can access
                </p>
            </div>


            <a
                href="{{ route('users.create') }}"
                class="primary-btn"
            >
                <i data-lucide="user-plus"></i>
                Create User
            </a>

        </div>


        @if(session('success'))

            <div class="success-message">
                {{ session('success') }}
            </div>

        @endif


        <form
            method="GET"
            action="{{ route('users.index') }}"
            class="user-filter"
        >

            <div class="search-box">

                <i data-lucide="search"></i>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search name or email"
                >

            </div>


            <button
                type="submit"
                class="search-btn"
            >
                Search
            </button>


            <a
                href="{{ route('users.index') }}"
                class="clear-btn"
            >
                Clear
            </a>

        </form>


        <div class="table-card">

            <div class="table-wrap">

                <table>

                    <thead>

                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Access</th>
                        <th>Actions</th>
                    </tr>

                    </thead>


                    <tbody>

                    @forelse($users as $user)

                        <tr>

                            <td>

                                <div class="user-info">

                                    <div class="user-avatar">
                                        {{ strtoupper(
                                            substr($user->name, 0, 2)
                                        ) }}
                                    </div>

                                    <strong>
                                        {{ $user->name }}
                                    </strong>

                                </div>

                            </td>


                            <td>
                                {{ $user->email }}
                            </td>


                            <td>

                                <span
                                    class="role-badge {{ strtolower($user->role) }}"
                                >
                                    {{ $user->role }}
                                </span>

                            </td>


                            <td>

                                <span
                                    class="status-badge {{ strtolower($user->status) }}"
                                >
                                    {{ $user->status }}
                                </span>

                            </td>


                            <td>

                                @if($user->isAdmin())

                                    <span class="full-access">
                                        Full Access
                                    </span>

                                @else

                                    {{ $user->permissions_count }}
                                    Permissions

                                @endif

                            </td>


                            <td>

                                @if(!$user->isAdmin())

                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <a
                                            href="{{ route(
                                                'users.edit',
                                                $user->id
                                            ) }}"
                                            class="edit-btn"
                                        >
                                            <i data-lucide="settings-2"></i>
                                            Manage
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('users.toggle-status', $user->id) }}"
                                            onsubmit="return confirm('Are you sure you want to {{ $user->status === 'ACTIVE' ? 'deactivate' : 'activate' }} this user?')"
                                            style="display: inline;"
                                        >
                                            @csrf
                                            @if($user->status === 'ACTIVE')
                                                <button
                                                    type="submit"
                                                    style="padding: 6px 12px; font-size: 12px; font-weight: 500; background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"
                                                >
                                                    <i data-lucide="user-x" style="width: 14px; height: 14px;"></i>
                                                    Deactivate
                                                </button>
                                            @else
                                                <button
                                                    type="submit"
                                                    style="padding: 6px 12px; font-size: 12px; font-weight: 500; background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"
                                                >
                                                    <i data-lucide="user-check" style="width: 14px; height: 14px;"></i>
                                                    Activate
                                                </button>
                                            @endif
                                        </form>
                                    </div>

                                @else

                                    <span class="admin-text">
                                        Administrator
                                    </span>

                                @endif

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="empty"
                            >
                                No users found.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            <div class="table-footer">

                <span>
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                </span>

            </div>

        </div>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>
</html>
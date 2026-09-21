@php
    $companyParam = request('company_id')
        ? ['company_id' => request('company_id')]
        : [];

    $user = auth()->user();
@endphp


<aside class="sidebar">

    <div class="logo">

        <i data-lucide="file-text"></i>

        <div>
            <strong>Quotation & Invoice</strong>
            <small>Management System</small>
        </div>

    </div>


    <nav>

        {{-- DASHBOARD --}}
        @if($user->hasPermission('dashboard.view'))

            <a
                href="{{ route('dashboard', $companyParam) }}"
                class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
            >
                <i data-lucide="house"></i>
                Dashboard
            </a>

        @endif


        {{-- COMPANIES --}}
        @if($user->hasPermission('companies.view'))

            <a
                href="{{ route('companies.index') }}"
                class="{{ request()->routeIs('companies.*') ? 'active' : '' }}"
            >
                <i data-lucide="building-2"></i>
                Companies
            </a>

        @endif


        {{-- CUSTOMERS --}}
        @if($user->hasPermission('customers.view'))

            <a
                href="{{ route('customers.index', $companyParam) }}"
                class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"
            >
                <i data-lucide="users"></i>
                Customers
            </a>

        @endif


        {{-- QUOTATIONS --}}
        @if($user->hasPermission('quotations.view'))

            <a
                href="{{ route('quotations.index', $companyParam) }}"
                class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}"
            >
                <i data-lucide="file-text"></i>
                Quotations
            </a>

        @endif


        {{-- INVOICES --}}
        @if($user->hasPermission('invoices.view'))

            <a
                href="{{ route('invoices.index', $companyParam) }}"
                class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}"
            >
                <i data-lucide="receipt-text"></i>
                Invoices
            </a>

        @endif


        {{-- PAYMENTS --}}
        @if($user->hasPermission('payments.view'))

            <a
                href="{{ route('payments.index', $companyParam) }}"
                class="{{ request()->routeIs('payments.*') ? 'active' : '' }}"
            >
                <i data-lucide="credit-card"></i>
                Payments
            </a>

        @endif


        {{-- TEMPLATES --}}
        @if($user->hasPermission('templates.view'))

            <a
                href="{{ route('templates.index', $companyParam) }}"
                class="{{ request()->routeIs('templates.*') ? 'active' : '' }}"
            >
                <i data-lucide="notebook-tabs"></i>
                Templates
            </a>

        @endif


        {{-- ACTIVITY LOGS --}}
        @if(
            Route::has('activity-logs.index') &&
            $user->hasPermission('activity_logs.view')
        )

            <a
                href="{{ route('activity-logs.index') }}"
                class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}"
            >
                <i data-lucide="history"></i>
                Activity Logs
            </a>

        @endif


        {{-- ADMIN ONLY --}}
        @if($user->isAdmin())

            <div class="nav-line"></div>

            <a
                href="{{ route('users.index') }}"
                class="{{ request()->routeIs('users.*') ? 'active' : '' }}"
            >
                <i data-lucide="user-cog"></i>
                Users & Permissions
            </a>

        @endif


        {{-- SETTINGS --}}
        @if(
            Route::has('settings.index') &&
            $user->hasPermission('settings.view')
        )

            <a
                href="{{ route('settings.index') }}"
                class="{{ request()->routeIs('settings.*') ? 'active' : '' }}"
            >
                <i data-lucide="settings"></i>
                Settings
            </a>

        @endif


        {{-- LOGOUT --}}
        <div class="nav-line"></div>

        <form
            method="POST"
            action="{{ route('logout') }}"
            class="logout-form"
        >

            @csrf

            <button
                type="submit"
                class="sidebar-logout"
            >
                <i data-lucide="log-out"></i>
                Logout
            </button>

        </form>

    </nav>

</aside>
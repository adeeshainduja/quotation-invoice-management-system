@php
    $companyParam = request()->filled('company_id')
        ? ['company_id' => request('company_id')]
        : [];
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

        <a href="{{ route('dashboard', $companyParam) }}"
           class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="house"></i>
            Dashboard
        </a>

        <a href="{{ route('companies.index') }}"
           class="{{ request()->routeIs('companies.*') ? 'active' : '' }}">
            <i data-lucide="building-2"></i>
            Companies
        </a>

        <a href="{{ route('customers.index', $companyParam) }}"
           class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i data-lucide="users"></i>
            Customers
        </a>

        <a href="{{ route('quotations.index', $companyParam) }}"
           class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}">
            <i data-lucide="file-text"></i>
            Quotations
        </a>


        @if(Route::has('invoices.index'))
            <a href="{{ route('invoices.index', $companyParam) }}"
               class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i data-lucide="receipt-text"></i>
                Invoices
            </a>
        @else
            <a href="javascript:void(0)" class="disabled">
                <i data-lucide="receipt-text"></i>
                Invoices
            </a>
        @endif


        @if(Route::has('payments.index'))
            <a href="{{ route('payments.index', $companyParam) }}"
               class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
                <i data-lucide="credit-card"></i>
                Payments
            </a>
        @else
            <a href="javascript:void(0)" class="disabled">
                <i data-lucide="credit-card"></i>
                Payments
            </a>
        @endif


        @if(Route::has('templates.index'))
            <a href="{{ route('templates.index', $companyParam) }}"
               class="{{ request()->routeIs('templates.*') ? 'active' : '' }}">
                <i data-lucide="notebook-tabs"></i>
                Templates
            </a>
        @else
            <a href="javascript:void(0)" class="disabled">
                <i data-lucide="notebook-tabs"></i>
                Templates
            </a>
        @endif


        @if(Route::has('activity-logs.index'))
            <a href="{{ route('activity-logs.index') }}"
               class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                <i data-lucide="history"></i>
                Activity Logs
            </a>
        @else
            <a href="javascript:void(0)" class="disabled">
                <i data-lucide="history"></i>
                Activity Logs
            </a>
        @endif


        <div class="nav-line"></div>


        @if(Route::has('settings.index'))
            <a href="{{ route('settings.index') }}"
               class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i data-lucide="settings"></i>
                Settings
            </a>
        @else
            <a href="javascript:void(0)" class="disabled">
                <i data-lucide="settings"></i>
                Settings
            </a>
        @endif

    </nav>

</aside>
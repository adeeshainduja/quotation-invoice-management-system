<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Quotations</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quotations.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

<aside class="sidebar">

    <div class="logo">
        <i data-lucide="file-text"></i>

        <div>
            <strong>Quotation & Invoice</strong>
            <small>Management System</small>
        </div>
    </div>

    <nav>

        <a href="{{ route('dashboard') }}">
            <i data-lucide="house"></i>
            Dashboard
        </a>

        <a href="{{ route('companies.index') }}">
            <i data-lucide="building-2"></i>
            Companies
        </a>

        <a href="{{ route('customers.index') }}">
            <i data-lucide="users"></i>
            Customers
        </a>

        <a href="{{ route('quotations.index') }}" class="active">
            <i data-lucide="file-text"></i>
            Quotations
        </a>

        <a href="#">
            <i data-lucide="receipt-text"></i>
            Invoices
        </a>

        <a href="#">
            <i data-lucide="wallet"></i>
            Payments
        </a>

        <a href="#">
            <i data-lucide="files"></i>
            Templates
        </a>

        <a href="#">
            <i data-lucide="bar-chart-3"></i>
            Reports
        </a>

        <a href="#">
            <i data-lucide="settings"></i>
            Settings
        </a>

    </nav>

</aside>


<div class="page">

    <header class="topbar">

        <form method="GET" action="{{ route('quotations.index') }}">

            <select
                class="company-select"
                name="company_id"
                onchange="this.form.submit()"
            >

                @foreach($companyList as $company)

                    <option
                        value="{{ $company->id }}"
                        {{ $companyId == $company->id ? 'selected' : '' }}
                    >
                        {{ $company->name }}
                    </option>

                @endforeach

            </select>

        </form>


        <div class="topbar-right">

            <div class="notification">
                <i data-lucide="bell"></i>
                <span></span>
            </div>

            <div class="user">

                <div class="avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>

                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>Administrator</small>
                </div>

            </div>

        </div>

    </header>


    <main>

        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span>›</span>
            Quotations
        </div>


        <div class="quotation-heading">

            <div>
                <h1>Quotations</h1>
                <p>Create and manage your quotations</p>
            </div>

            <a href="#" class="new-quotation">
                <i data-lucide="plus"></i>
                New Quotation
            </a>

        </div>


        <form
            method="GET"
            action="{{ route('quotations.index') }}"
            class="quotation-filter"
        >

            <input
                type="hidden"
                name="company_id"
                value="{{ $companyId }}"
            >


            <div class="search-box">

                <i data-lucide="search"></i>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search quotations"
                >

            </div>


            <select name="status">

                <option value="">All Status</option>

                @foreach([
                    'DRAFT',
                    'SENT',
                    'ACCEPTED',
                    'REJECTED',
                    'EXPIRED',
                    'CONVERTED'
                ] as $status)

                    <option
                        value="{{ $status }}"
                        {{ request('status') === $status ? 'selected' : '' }}
                    >
                        {{ ucfirst(strtolower($status)) }}
                    </option>

                @endforeach

            </select>


            <select name="customer_id">

                <option value="">All Customers</option>

                @foreach($customerList as $customer)

                    <option
                        value="{{ $customer->id }}"
                        {{ request('customer_id') == $customer->id ? 'selected' : '' }}
                    >
                        {{ $customer->business_name }}
                    </option>

                @endforeach

            </select>


            <div class="date-filter">

                <input
                    type="date"
                    name="from_date"
                    value="{{ request('from_date') }}"
                >

                <span>→</span>

                <input
                    type="date"
                    name="to_date"
                    value="{{ request('to_date') }}"
                >

            </div>


            <button type="submit" class="filter-button">
                Search
            </button>


            <a
                class="clear-button"
                href="{{ route('quotations.index', ['company_id' => $companyId]) }}"
            >
                Clear Filters
            </a>

        </form>


        <section class="quotation-table">

            <div class="table-wrap">

                <table>

                    <thead>

                    <tr>
                        <th>#</th>
                        <th>Quotation Number</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Valid Until</th>
                        <th>Amount (LKR)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                    </thead>


                    <tbody>

                    @forelse($quotations as $quotation)

                        <tr>

                            <td>
                                {{ $quotations->firstItem() + $loop->index }}
                            </td>

                            <td>
                                {{ $quotation->quotation_number }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('Y-m-d') }}
                            </td>

                            <td>
                                {{ $quotation->business_name }}
                            </td>

                            <td>
                                {{ $quotation->expiry_date
                                    ? \Carbon\Carbon::parse($quotation->expiry_date)->format('Y-m-d')
                                    : '-' }}
                            </td>

                            <td>
                                {{ number_format($quotation->grand_total, 2) }}
                            </td>

                            <td>

                                <span class="quotation-status {{ strtolower($quotation->status) }}">
                                    {{ $quotation->status }}
                                </span>

                            </td>

                            <td>

                                <div class="actions">

                                    <a href="#">
                                        <i data-lucide="eye"></i>
                                        View
                                    </a>

                                    @if(!in_array($quotation->status, ['CONVERTED']))

                                        <a href="#">
                                            <i data-lucide="pencil"></i>
                                            Edit
                                        </a>

                                    @endif

                                    <button type="button">
                                        <i data-lucide="more-horizontal"></i>
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="empty">
                                No quotations found.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            <div class="table-footer">

                <span>
                    Showing
                    {{ $quotations->firstItem() ?? 0 }}
                    to
                    {{ $quotations->lastItem() ?? 0 }}
                    of
                    {{ $quotations->total() }}
                    quotations
                </span>

                {{ $quotations->links() }}

            </div>

        </section>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>
</html>
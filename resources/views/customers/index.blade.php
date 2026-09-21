<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customers</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customers.css') }}">

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

        <a href="{{ route('dashboard', ['company_id' => $companyId]) }}">
            <i data-lucide="house"></i>
            Dashboard
        </a>

        <a href="{{ route('companies.index') }}">
            <i data-lucide="building-2"></i>
            Companies
        </a>

        <a href="{{ route('customers.index') }}" class="active">
            <i data-lucide="users"></i>
            Customers
        </a>

        <a href="{{ route('quotations.index', ['company_id' => $companyId]) }}">
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
            <i data-lucide="settings"></i>
            Settings
        </a>

    </nav>

</aside>


<div class="page">

    <header class="topbar">

        <button class="menu">
            <i data-lucide="menu"></i>
        </button>

        <div class="topbar-right">

            <form method="GET" action="{{ route('customers.index') }}">

                <select
                    name="company_id"
                    class="company-select"
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
            Customers
        </div>


        <div class="customer-heading">

            <div>
                <h1>Customers</h1>

                <p>
                    Manage your customers for the selected company
                </p>
            </div>

            <a href="{{ route('customers.create') }}" class="add-customer">
                <i data-lucide="plus"></i>
                Add Customer
            </a>

        </div>


        <form
            method="GET"
            action="{{ route('customers.index') }}"
            class="customer-filter"
        >

            <input
                type="hidden"
                name="company_id"
                value="{{ $companyId }}"
            >


            <div class="search-input">

                <i data-lucide="search"></i>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search customers by name, business name, email or phone..."
                >

            </div>


            <select name="status">

                <option value="">All Status</option>

                <option
                    value="ACTIVE"
                    {{ request('status') === 'ACTIVE' ? 'selected' : '' }}
                >
                    Active
                </option>

                <option
                    value="INACTIVE"
                    {{ request('status') === 'INACTIVE' ? 'selected' : '' }}
                >
                    Inactive
                </option>

            </select>


            <select name="city">

                <option value="">All Cities</option>

                @foreach($cities as $city)

                    <option
                        value="{{ $city }}"
                        {{ request('city') === $city ? 'selected' : '' }}
                    >
                        {{ $city }}
                    </option>

                @endforeach

            </select>


            <button class="search-btn">
                Search
            </button>


            <a
                href="{{ route('customers.index', ['company_id' => $companyId]) }}"
                class="clear-btn"
            >
                Clear Filters
            </a>

        </form>


        <section class="customer-table-card">

            <div class="table-wrap">

                <table>

                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Business Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($customers as $customer)

                        <tr>

                            <td>
                                {{ $customers->firstItem() + $loop->index }}
                            </td>

                            <td>
                                {{ $customer->customer_name }}
                            </td>

                            <td>
                                {{ $customer->business_name }}
                            </td>

                            <td>
                                {{ $customer->email ?: '-' }}
                            </td>

                            <td>
                                {{ $customer->phone ?: '-' }}
                            </td>

                            <td>
                                {{ $customer->city }}
                            </td>

                            <td>

                                <span class="customer-status {{ strtolower($customer->status) }}">

                                    {{ $customer->status }}

                                </span>

                            </td>

                            <td>

                                <div class="actions">

                                    <a href="#" title="View">
                                        <i data-lucide="eye"></i>
                                    </a>

                                    <a href="#" title="Edit">
                                        <i data-lucide="pencil"></i>
                                    </a>

                                    <button type="button">
                                        <i data-lucide="more-horizontal"></i>
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="empty">
                                No customers found.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            <div class="table-footer">

                <span>
                    Showing
                    {{ $customers->firstItem() ?? 0 }}
                    to
                    {{ $customers->lastItem() ?? 0 }}
                    of
                    {{ $customers->total() }}
                    customers
                </span>

                {{ $customers->links() }}

            </div>

        </section>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>
</html>

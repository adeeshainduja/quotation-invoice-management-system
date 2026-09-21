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

@include('partials.sidebar')


<div class="page">

    {{-- TOP BAR --}}
    <header class="topbar">

        <button class="menu" type="button">
            <i data-lucide="menu"></i>
        </button>


        <div class="topbar-right">

            <form method="GET" action="{{ route('customers.index') }}">

                <select
                    name="company_id"
                    class="company-select"
                    onchange="this.form.submit()"
                >

                    <option value="">
                        Select Company
                    </option>

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

        {{-- BREADCRUMB --}}
        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>

            Customers

        </div>


        {{-- PAGE HEADER --}}
        <div class="customer-heading">

            <div>
                <h1>Customers</h1>

                <p>
                    Manage your customers for the selected company
                </p>
            </div>


            <a
                href="{{ $companyId
                    ? route('customers.create', ['company_id' => $companyId])
                    : route('customers.create') }}"
                class="add-customer"
            >
                <i data-lucide="plus"></i>

                Add Customer
            </a>

        </div>


        {{-- MESSAGES --}}
        @if(session('success'))

            <div class="alert-success">
                {{ session('success') }}
            </div>

        @endif


        @if(session('error'))

            <div class="alert-error">
                {{ session('error') }}
            </div>

        @endif


        {{-- FILTERS --}}
        <form
            method="GET"
            action="{{ route('customers.index') }}"
            class="customer-filter"
        >

            @if($companyId)

                <input
                    type="hidden"
                    name="company_id"
                    value="{{ $companyId }}"
                >

            @endif


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

                <option value="">
                    All Status
                </option>

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

                <option value="">
                    All Cities
                </option>

                @foreach($cities as $city)

                    <option
                        value="{{ $city }}"
                        {{ request('city') === $city ? 'selected' : '' }}
                    >
                        {{ $city }}
                    </option>

                @endforeach

            </select>


            <button
                type="submit"
                class="search-btn"
            >
                <i data-lucide="search"></i>
                Search
            </button>


            <a
                href="{{ $companyId
                    ? route('customers.index', ['company_id' => $companyId])
                    : route('customers.index') }}"
                class="clear-btn"
            >
                Clear Filters
            </a>

        </form>


        {{-- CUSTOMER TABLE --}}
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
                                {{ $customer->city ?: '-' }}
                            </td>


                            <td>

                                <span class="customer-status {{ strtolower($customer->status) }}">
                                    {{ ucfirst(strtolower($customer->status)) }}
                                </span>

                            </td>


                            <td>

                                <div class="actions">

                                    {{-- VIEW --}}
                                    <a
                                        href="{{ route('customers.show', $customer->id) }}"
                                        title="View"
                                    >
                                        <i data-lucide="eye"></i>
                                    </a>


                                    {{-- EDIT --}}
                                    @if(Route::has('customers.edit'))

                                        <a
                                            href="{{ route('customers.edit', $customer->id) }}"
                                            title="Edit"
                                        >
                                            <i data-lucide="pencil"></i>
                                        </a>

                                    @else

                                        <button
                                            type="button"
                                            title="Edit not available yet"
                                            disabled
                                        >
                                            <i data-lucide="pencil"></i>
                                        </button>

                                    @endif


                                    <button
                                        type="button"
                                        title="More"
                                    >
                                        <i data-lucide="more-horizontal"></i>
                                    </button>

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="empty"
                            >

                                @if(!$companyId)

                                    No company selected.

                                @else

                                    No customers found.

                                @endif

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}
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
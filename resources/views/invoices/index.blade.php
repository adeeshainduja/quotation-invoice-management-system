<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Invoices</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/invoices.css') }}">

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

            <form method="GET" action="{{ route('invoices.index') }}">

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

            Invoices

        </div>


        {{-- PAGE HEADING --}}
        <div class="invoice-heading">

            <div>

                <h1>Invoices</h1>

                <p>
                    Create and manage customer invoices
                </p>

            </div>


            <a
                href="{{ $companyId
                    ? route('invoices.create', ['company_id' => $companyId])
                    : route('invoices.create') }}"
                class="new-invoice"
            >

                <i data-lucide="plus"></i>

                New Invoice

            </a>

        </div>


        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))

            <div class="alert-success">
                {{ session('success') }}
            </div>

        @endif


        {{-- FILTERS --}}
        <form
            method="GET"
            action="{{ route('invoices.index') }}"
            class="invoice-filter"
        >

            @if($companyId)

                <input
                    type="hidden"
                    name="company_id"
                    value="{{ $companyId }}"
                >

            @endif


            <div class="search-box">

                <i data-lucide="search"></i>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search invoice number"
                >

            </div>


            <select name="status">

                <option value="">
                    All Status
                </option>


                @foreach([
                    'DRAFT',
                    'ISSUED',
                    'PARTIALLY_PAID',
                    'PAID',
                    'OVERDUE',
                    'CANCELLED'
                ] as $status)

                    <option
                        value="{{ $status }}"
                        {{ request('status') === $status ? 'selected' : '' }}
                    >

                        {{ ucwords(
                            strtolower(
                                str_replace('_', ' ', $status)
                            )
                        ) }}

                    </option>

                @endforeach

            </select>


            <select name="customer_id">

                <option value="">
                    All Customers
                </option>


                @foreach($customerList as $customer)

                    <option
                        value="{{ $customer->id }}"
                        {{ request('customer_id') == $customer->id ? 'selected' : '' }}
                    >
                        {{ $customer->business_name }}
                    </option>

                @endforeach

            </select>


            <button
                type="submit"
                class="filter-button"
            >
                <i data-lucide="search"></i>
                Search
            </button>


            <a
                href="{{ $companyId
                    ? route('invoices.index', ['company_id' => $companyId])
                    : route('invoices.index') }}"
                class="clear-button"
            >
                Clear Filters
            </a>

        </form>


        {{-- INVOICE TABLE --}}
        <section class="invoice-table">

            <div class="table-wrap">

                <table>

                    <thead>

                    <tr>
                        <th>#</th>
                        <th>Invoice Number</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Due Date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                    </thead>


                    <tbody>

                    @forelse($invoices as $invoice)

                        <tr>

                            <td>
                                {{ $invoices->firstItem() + $loop->index }}
                            </td>


                            <td>
                                <strong>
                                    {{ $invoice->invoice_number }}
                                </strong>
                            </td>


                            <td>
                                {{ $invoice->invoice_date
                                    ? \Carbon\Carbon::parse(
                                        $invoice->invoice_date
                                      )->format('d M Y')
                                    : '-' }}
                            </td>


                            <td>
                                {{ $invoice->business_name ?: '-' }}
                            </td>


                            <td>
                                {{ $invoice->due_date
                                    ? \Carbon\Carbon::parse(
                                        $invoice->due_date
                                      )->format('d M Y')
                                    : '-' }}
                            </td>


                            <td>
                                {{ number_format(
                                    (float) $invoice->grand_total,
                                    2
                                ) }}
                            </td>


                            <td>
                                {{ number_format(
                                    (float) $invoice->amount_paid,
                                    2
                                ) }}
                            </td>


                            <td>
                                {{ number_format(
                                    (float) $invoice->balance_amount,
                                    2
                                ) }}
                            </td>


                            <td>

                                <span
                                    class="invoice-status {{
                                        strtolower(
                                            str_replace(
                                                '_',
                                                '-',
                                                $invoice->status
                                            )
                                        )
                                    }}"
                                >

                                    {{ ucwords(
                                        strtolower(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $invoice->status
                                            )
                                        )
                                    ) }}

                                </span>

                            </td>


                            <td>

                                <div class="actions">

                                    @if(Route::has('invoices.show'))

                                        <a
                                            href="{{ route(
                                                'invoices.show',
                                                $invoice->id
                                            ) }}"
                                            title="View"
                                        >
                                            <i data-lucide="eye"></i>
                                        </a>

                                    @endif


                                    @if(Route::has('invoices.edit'))

                                        <a
                                            href="{{ route(
                                                'invoices.edit',
                                                $invoice->id
                                            ) }}"
                                            title="Edit"
                                        >
                                            <i data-lucide="pencil"></i>
                                        </a>

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
                                colspan="10"
                                class="empty"
                            >

                                @if(!$companyId)

                                    Select a company to view invoices.

                                @else

                                    No invoices found.

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
                    {{ $invoices->firstItem() ?? 0 }}

                    to
                    {{ $invoices->lastItem() ?? 0 }}

                    of
                    {{ $invoices->total() }}

                    invoices

                </span>


                {{ $invoices->links() }}

            </div>

        </section>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>

</html>
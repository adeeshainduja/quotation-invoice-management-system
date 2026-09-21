<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Payments</title>

    <link
        rel="stylesheet"
        href="{{ asset('css/dashboard.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/payments.css') }}"
    >

    <script src="https://unpkg.com/lucide@latest"></script>
</head>


<body>


@include('partials.sidebar')


<div class="page">


    <header class="topbar">

        <button
            class="menu"
            type="button"
        >
            <i data-lucide="menu"></i>
        </button>


        <div class="topbar-right">


            <form
                method="GET"
                action="{{ route('payments.index') }}"
            >

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
                            {{ $companyId == $company->id
                                ? 'selected'
                                : '' }}
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
                    {{ strtoupper(
                        substr(
                            auth()->user()->name,
                            0,
                            2
                        )
                    ) }}
                </div>


                <div>

                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <small>
                        Administrator
                    </small>

                </div>

            </div>

        </div>

    </header>



    <main>


        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>

            Payments

        </div>



        <div class="payments-heading">

            <div>

                <h1>
                    Payments
                </h1>

                <p>
                    Record and manage invoice payments
                </p>

            </div>


            <a
                href="{{ $companyId
                    ? route(
                        'payments.create',
                        ['company_id' => $companyId]
                    )
                    : route('payments.create') }}"
                class="new-payment-btn"
            >

                <i data-lucide="plus"></i>

                Record Payment

            </a>

        </div>



        @if(session('success'))

            <div class="alert-success">

                {{ session('success') }}

            </div>

        @endif



        <form
            method="GET"
            action="{{ route('payments.index') }}"
            class="payment-filter"
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
                    placeholder="Invoice, customer or reference"
                >

            </div>



            <select name="payment_method">

                <option value="">
                    All Payment Methods
                </option>


                <option
                    value="CASH"
                    {{ request('payment_method') === 'CASH'
                        ? 'selected'
                        : '' }}
                >
                    Cash
                </option>


                <option
                    value="BANK_TRANSFER"
                    {{ request('payment_method') === 'BANK_TRANSFER'
                        ? 'selected'
                        : '' }}
                >
                    Bank Transfer
                </option>


                <option
                    value="CARD"
                    {{ request('payment_method') === 'CARD'
                        ? 'selected'
                        : '' }}
                >
                    Card
                </option>


                <option
                    value="CHEQUE"
                    {{ request('payment_method') === 'CHEQUE'
                        ? 'selected'
                        : '' }}
                >
                    Cheque
                </option>


                <option
                    value="OTHER"
                    {{ request('payment_method') === 'OTHER'
                        ? 'selected'
                        : '' }}
                >
                    Other
                </option>

            </select>



            <button
                type="submit"
                class="filter-btn"
            >

                <i data-lucide="search"></i>

                Search

            </button>



            <a
                href="{{ $companyId
                    ? route(
                        'payments.index',
                        ['company_id' => $companyId]
                    )
                    : route('payments.index') }}"
                class="clear-btn"
            >
                Clear Filters
            </a>

        </form>



        <section class="payments-table-card">

            <div class="table-wrap">

                <table>

                    <thead>

                    <tr>

                        <th>#</th>

                        <th>Date</th>

                        <th>Invoice</th>

                        <th>Customer</th>

                        <th>Method</th>

                        <th>Reference</th>

                        <th>Amount</th>

                        <th>Invoice Balance</th>

                    </tr>

                    </thead>


                    <tbody>


                    @forelse($payments as $payment)

                        <tr>

                            <td>

                                {{
                                    $payments->firstItem()
                                    + $loop->index
                                }}

                            </td>


                            <td>

                                {{
                                    \Carbon\Carbon::parse(
                                        $payment->payment_date
                                    )->format('d M Y')
                                }}

                            </td>


                            <td>

                                <strong>
                                    {{ $payment->invoice_number }}
                                </strong>

                            </td>


                            <td>

                                {{
                                    $payment->business_name
                                    ?: '-'
                                }}

                            </td>


                            <td>

                                <span class="payment-method">

                                    {{
                                        ucwords(
                                            strtolower(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $payment->payment_method
                                                )
                                            )
                                        )
                                    }}

                                </span>

                            </td>


                            <td>

                                {{
                                    $payment->reference
                                    ?: '-'
                                }}

                            </td>


                            <td class="payment-amount">

                                {{
                                    number_format(
                                        (float) $payment->amount,
                                        2
                                    )
                                }}

                            </td>


                            <td>

                                {{
                                    number_format(
                                        (float) $payment->balance_amount,
                                        2
                                    )
                                }}

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="empty"
                            >

                                @if(!$companyId)

                                    Select a company to view payments.

                                @else

                                    No payments found.

                                @endif

                            </td>

                        </tr>

                    @endforelse


                    </tbody>

                </table>

            </div>



            <div class="table-footer">

                <span>

                    Showing

                    {{
                        $payments->firstItem()
                        ?? 0
                    }}

                    to

                    {{
                        $payments->lastItem()
                        ?? 0
                    }}

                    of

                    {{ $payments->total() }}

                    payments

                </span>


                {{ $payments->links() }}

            </div>

        </section>

    </main>

</div>


<script>
    lucide.createIcons();
</script>


</body>

</html>
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

    {{-- TOPBAR --}}
    <header class="topbar">

        <div></div>

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
                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <small>
                        User
                    </small>
                </div>

            </div>

        </div>

    </header>


    <main>

        {{-- HEADING --}}
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>

            Payments
        </div>


        <div class="page-heading">

            <div>
                <h1>Payments</h1>

                <p>
                    Monitor received, outstanding,
                    pending and overdue payments
                </p>
            </div>

        </div>


        {{-- STATS --}}
        <section class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon orange">
                    <i data-lucide="wallet-cards"></i>
                </div>

                <div class="stat-content">

                    <span class="stat-label">
                        Outstanding Payments
                    </span>

                    <strong>
                        {{ $currency }}
                        {{ number_format($stats['outstanding_amount'], 2) }}
                    </strong>

                    <small>
                        {{ $stats['outstanding_count'] }}
                        invoices outstanding
                    </small>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon green">
                    <i data-lucide="circle-dollar-sign"></i>
                </div>

                <div class="stat-content">

                    <span class="stat-label">
                        Total Payments Received
                    </span>

                    <strong>
                        {{ $currency }}
                        {{ number_format($stats['total_received'], 2) }}
                    </strong>

                    <small>
                        {{ $stats['payment_count'] }}
                        payment transactions
                    </small>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon blue">
                    <i data-lucide="circle-check-big"></i>
                </div>

                <div class="stat-content">

                    <span class="stat-label">
                        Payment Completed
                    </span>

                    <strong>
                        {{ $stats['completed_count'] }}
                    </strong>

                    <small>
                        {{ $currency }}
                        {{ number_format($stats['completed_amount'], 2) }}
                    </small>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon red">
                    <i data-lucide="triangle-alert"></i>
                </div>

                <div class="stat-content">

                    <span class="stat-label">
                        Overdue
                    </span>

                    <strong>
                        {{ $stats['overdue_count'] }}
                    </strong>

                    <small>
                        {{ $currency }}
                        {{ number_format($stats['overdue_amount'], 2) }}
                        overdue
                    </small>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon purple">
                    <i data-lucide="clock-3"></i>
                </div>

                <div class="stat-content">

                    <span class="stat-label">
                        Payment Pending
                    </span>

                    <strong>
                        {{ $stats['pending_count'] }}
                    </strong>

                    <small>
                        {{ $currency }}
                        {{ number_format($stats['pending_amount'], 2) }}
                        pending
                    </small>

                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon cyan">
                    <i data-lucide="calendar-check"></i>
                </div>

                <div class="stat-content">

                    <span class="stat-label">
                        Received This Month
                    </span>

                    <strong>
                        {{ $currency }}
                        {{ number_format($stats['month_received'], 2) }}
                    </strong>

                    <small>
                        {{ $stats['month_count'] }}
                        payments this month
                    </small>

                </div>

            </div>

        </section>


        {{-- FILTERS --}}
        <section class="filter-card">

            <form
                method="GET"
                action="{{ route('payments.index') }}"
                class="filters"
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
                        placeholder="Invoice, customer or reference..."
                    >

                </div>


                <select name="payment_method">

                    <option value="">
                        All Methods
                    </option>

                    <option
                        value="CASH"
                        {{ request('payment_method') === 'CASH' ? 'selected' : '' }}
                    >
                        Cash
                    </option>

                    <option
                        value="BANK_TRANSFER"
                        {{ request('payment_method') === 'BANK_TRANSFER' ? 'selected' : '' }}
                    >
                        Bank Transfer
                    </option>

                    <option
                        value="CARD"
                        {{ request('payment_method') === 'CARD' ? 'selected' : '' }}
                    >
                        Card
                    </option>

                    <option
                        value="CHEQUE"
                        {{ request('payment_method') === 'CHEQUE' ? 'selected' : '' }}
                    >
                        Cheque
                    </option>

                    <option
                        value="OTHER"
                        {{ request('payment_method') === 'OTHER' ? 'selected' : '' }}
                    >
                        Other
                    </option>

                </select>


                <select name="status">

                    <option value="">
                        All Invoice Status
                    </option>

                    <option
                        value="ISSUED"
                        {{ request('status') === 'ISSUED' ? 'selected' : '' }}
                    >
                        Issued
                    </option>

                    <option
                        value="PARTIALLY_PAID"
                        {{ request('status') === 'PARTIALLY_PAID' ? 'selected' : '' }}
                    >
                        Partially Paid
                    </option>

                    <option
                        value="PAID"
                        {{ request('status') === 'PAID' ? 'selected' : '' }}
                    >
                        Paid
                    </option>

                    <option
                        value="OVERDUE"
                        {{ request('status') === 'OVERDUE' ? 'selected' : '' }}
                    >
                        Overdue
                    </option>

                    <option
                        value="CANCELLED"
                        {{ request('status') === 'CANCELLED' ? 'selected' : '' }}
                    >
                        Cancelled
                    </option>

                </select>


                <input
                    type="date"
                    name="from_date"
                    value="{{ request('from_date') }}"
                >


                <input
                    type="date"
                    name="to_date"
                    value="{{ request('to_date') }}"
                >


                <button
                    type="submit"
                    class="filter-btn"
                >
                    <i data-lucide="sliders-horizontal"></i>
                    Filter
                </button>


                <a
                    href="{{ route('payments.index', [
                        'company_id' => $companyId
                    ]) }}"
                    class="reset-btn"
                >
                    Reset
                </a>

            </form>

        </section>


        {{-- PAYMENT HISTORY --}}
        <section class="table-card">

            <div class="table-heading">

                <div>

                    <h2>
                        Payment History
                    </h2>

                    <p>
                        All payments recorded through invoices
                    </p>

                </div>

            </div>


            <div class="table-wrap">

                <table>

                    <thead>

                    <tr>
                        <th>#</th>
                        <th>Payment Date</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Payment Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Invoice Total</th>
                        <th>Total Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Recorded By</th>
                        <th>Actions</th>
                    </tr>

                    </thead>


                    <tbody>

                    @forelse($payments as $payment)

                        @php

                            $displayStatus =
                                $payment->status;

                            if (
                                $payment->balance_amount > 0 &&
                                $payment->due_date &&
                                \Carbon\Carbon::parse(
                                    $payment->due_date
                                )->isPast() &&
                                !in_array(
                                    $payment->status,
                                    ['PAID', 'CANCELLED']
                                )
                            ) {
                                $displayStatus = 'OVERDUE';
                            }

                        @endphp


                        <tr>

                            <td>
                                {{ $payments->firstItem() + $loop->index }}
                            </td>


                            <td>
                                {{ \Carbon\Carbon::parse(
                                    $payment->payment_date
                                )->format('d M Y') }}
                            </td>


                            <td>
                                <strong class="invoice-number">
                                    {{ $payment->invoice_number }}
                                </strong>
                            </td>


                            <td>

                                <strong>
                                    {{ $payment->business_name }}
                                </strong>

                                @if($payment->customer_name)
                                    <small class="sub-text">
                                        {{ $payment->customer_name }}
                                    </small>
                                @endif

                            </td>


                            <td class="amount received">

                                {{ $currency }}
                                {{ number_format(
                                    $payment->amount,
                                    2
                                ) }}

                            </td>


                            <td>

                                <span class="method-badge">

                                    {{ ucwords(
                                        strtolower(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $payment->payment_method
                                            )
                                        )
                                    ) }}

                                </span>

                            </td>


                            <td>
                                {{ $payment->reference ?: '-' }}
                            </td>


                            <td class="amount">

                                {{ $currency }}
                                {{ number_format(
                                    $payment->grand_total,
                                    2
                                ) }}

                            </td>


                            <td class="amount paid">

                                {{ $currency }}
                                {{ number_format(
                                    $payment->amount_paid,
                                    2
                                ) }}

                            </td>


                            <td class="amount {{ $payment->balance_amount > 0 ? 'balance' : '' }}">

                                {{ $currency }}
                                {{ number_format(
                                    $payment->balance_amount,
                                    2
                                ) }}

                            </td>


                            <td>

                                <span
                                    class="status-badge {{ strtolower($displayStatus) }}"
                                >
                                    {{ str_replace(
                                        '_',
                                        ' ',
                                        $displayStatus
                                    ) }}
                                </span>

                            </td>


                            <td>
                                {{ $payment->recorded_by ?? '-' }}
                            </td>


                            <td>

                                <a
                                    href="{{ route(
                                        'invoices.pdf',
                                        $payment->invoice_id
                                    ) }}"
                                    class="action-btn"
                                    title="Invoice PDF"
                                >
                                    <i data-lucide="file-down"></i>
                                </a>

                            </td>

                        </tr>


                        @if($payment->notes)

                            <tr class="notes-row">

                                <td></td>

                                <td colspan="12">

                                    <strong>
                                        Payment Note:
                                    </strong>

                                    {{ $payment->notes }}

                                </td>

                            </tr>

                        @endif


                    @empty

                        <tr>

                            <td
                                colspan="13"
                                class="empty-state"
                            >

                                <i data-lucide="credit-card"></i>

                                <strong>
                                    No payments found
                                </strong>

                                <span>
                                    Payments recorded from invoices
                                    will appear here.
                                </span>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            <div class="table-footer">

                <span>
                    Showing
                    {{ $payments->firstItem() ?? 0 }}
                    to
                    {{ $payments->lastItem() ?? 0 }}
                    of
                    {{ $payments->total() }}
                    payments
                </span>

                <div>
                    {{ $payments->links() }}
                </div>

            </div>

        </section>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>
</html>
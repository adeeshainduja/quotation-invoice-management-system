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

@include('partials.sidebar')

@php
    $quotationStats = array_merge([
        'total' => 0,
        'draft' => 0,
        'sent' => 0,
        'accepted' => 0,
        'rejected' => 0,
        'expired' => 0,
        'converted' => 0,
    ], $stats ?? []);
@endphp


<div class="page">

    @include('partials.topbar')


    <main>

        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>

            Quotations

        </div>


        <div class="quotation-heading">

            <div>
                <h1>Quotations</h1>
                <p>Create and manage your quotations</p>
            </div>


            <a
                href="{{ route('quotations.create', [
                    'company_id' => $companyId
                ]) }}"
                class="new-quotation"
            >
                <i data-lucide="plus"></i>
                New Quotation
            </a>

        </div>


        {{-- QUOTATION SUMMARY --}}
        <section class="quotation-summary">

            <a
                href="{{ route('quotations.index', [
                    'company_id' => $companyId
                ]) }}"
                class="summary-card"
            >

                <div class="summary-icon blue">
                    <i data-lucide="file-text"></i>
                </div>

                <div>
                    <span>All Quotations</span>

                    <strong>
                        {{ $quotationStats['total'] }}
                    </strong>

                    <small>
                        Total quotations
                    </small>
                </div>

            </a>


            <a
                href="{{ route('quotations.index', [
                    'company_id' => $companyId,
                    'status' => 'SENT'
                ]) }}"
                class="summary-card"
            >

                <div class="summary-icon purple">
                    <i data-lucide="send"></i>
                </div>

                <div>
                    <span>Pending Response</span>

                    <strong>
                        {{ $quotationStats['sent'] }}
                    </strong>

                    <small>
                        Sent to customers
                    </small>
                </div>

            </a>


            <a
                href="{{ route('quotations.index', [
                    'company_id' => $companyId,
                    'status' => 'ACCEPTED'
                ]) }}"
                class="summary-card"
            >

                <div class="summary-icon green">
                    <i data-lucide="circle-check"></i>
                </div>

                <div>
                    <span>Accepted</span>

                    <strong>
                        {{ $quotationStats['accepted'] }}
                    </strong>

                    <small>
                        Approved quotations
                    </small>
                </div>

            </a>


            <a
                href="{{ route('quotations.index', [
                    'company_id' => $companyId,
                    'status' => 'REJECTED'
                ]) }}"
                class="summary-card"
            >

                <div class="summary-icon red">
                    <i data-lucide="circle-x"></i>
                </div>

                <div>
                    <span>Rejected</span>

                    <strong>
                        {{ $quotationStats['rejected'] }}
                    </strong>

                    <small>
                        Declined quotations
                    </small>
                </div>

            </a>


            <a
                href="{{ route('quotations.index', [
                    'company_id' => $companyId,
                    'status' => 'EXPIRED'
                ]) }}"
                class="summary-card"
            >

                <div class="summary-icon orange">
                    <i data-lucide="clock-3"></i>
                </div>

                <div>
                    <span>Expired</span>

                    <strong>
                        {{ $quotationStats['expired'] }}
                    </strong>

                    <small>
                        No longer valid
                    </small>
                </div>

            </a>


            <a
                href="{{ route('quotations.index', [
                    'company_id' => $companyId,
                    'status' => 'CONVERTED'
                ]) }}"
                class="summary-card"
            >

                <div class="summary-icon violet">
                    <i data-lucide="receipt-text"></i>
                </div>

                <div>
                    <span>Converted</span>

                    <strong>
                        {{ $quotationStats['converted'] }}
                    </strong>

                    <small>
                        Invoice created
                    </small>
                </div>

            </a>

        </section>


        {{-- FILTER --}}
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

                <option value="">
                    All Status
                </option>

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


            <button
                type="submit"
                class="filter-button"
            >
                Search
            </button>


            <a
                href="{{ route('quotations.index', [
                    'company_id' => $companyId
                ]) }}"
                class="clear-button"
            >
                Clear Filters
            </a>

        </form>


        {{-- TABLE --}}
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
                        <th>Amount ({{ $currency ?? 'LKR' }})</th>
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
                                {{ \Carbon\Carbon::parse(
                                    $quotation->quotation_date
                                )->format('Y-m-d') }}
                            </td>


                            <td>
                                {{ $quotation->business_name }}
                            </td>


                            <td>

                                {{ $quotation->expiry_date
                                    ? \Carbon\Carbon::parse(
                                        $quotation->expiry_date
                                    )->format('Y-m-d')
                                    : '-'
                                }}

                            </td>


                            <td>

                                {{ number_format(
                                    $quotation->grand_total,
                                    2
                                ) }}

                            </td>


                            <td>

                                <span
                                    class="quotation-status {{ strtolower(
                                        $quotation->status
                                    ) }}"
                                >
                                    {{ $quotation->status }}
                                </span>

                            </td>


                            <td>

                                <div class="actions">

                                    <a
                                        href="{{ route(
                                            'quotations.show',
                                            $quotation->id
                                        ) }}"
                                        title="View Quotation"
                                    >
                                        <i data-lucide="eye"></i>
                                        View
                                    </a>

                                    @if($quotation->status !== 'CONVERTED')
                                        <a
                                            href="{{ route('quotations.edit', $quotation->id) }}"
                                            title="Edit Quotation"
                                        >
                                            <i data-lucide="pencil"></i>
                                            Edit
                                        </a>
                                    @endif

                                    <a
                                        href="{{ route('quotations.pdf', $quotation->id) }}"
                                        target="_blank"
                                        title="Export Quotation PDF"
                                    >
                                        <i data-lucide="download"></i>
                                        Export PDF
                                    </a>

                                    <div class="dropdown-wrapper" style="position: relative; display: inline-block;">
                                        <button
                                            type="button"
                                            class="more-btn"
                                            onclick="toggleQuotationDropdown(event, {{ $quotation->id }})"
                                            title="More Actions"
                                        >
                                            <i data-lucide="more-horizontal"></i>
                                        </button>

                                        <div
                                            id="dropdown-{{ $quotation->id }}"
                                            class="dropdown-menu-list"
                                            style="display: none; position: absolute; right: 0; top: calc(100% + 4px); background: #ffffff; border: 1px solid #dce5ef; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.12); min-width: 170px; z-index: 1050; padding: 6px 0;"
                                        >
                                            <form method="POST" action="{{ route('quotations.clone', $quotation->id) }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item-btn" style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 14px; border: none; background: transparent; color: #263b58; font-size: 12px; font-weight: 500; cursor: pointer; text-align: left;">
                                                    <i data-lucide="copy" style="width: 14px; height: 14px;"></i>
                                                    Clone Quotation
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('quotations.send', $quotation->id) }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item-btn" style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 14px; border: none; background: transparent; color: #263b58; font-size: 12px; font-weight: 500; cursor: pointer; text-align: left;">
                                                    <i data-lucide="send" style="width: 14px; height: 14px;"></i>
                                                    Mark as Sent
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('quotations.reject', $quotation->id) }}" onsubmit="return confirm('Are you sure you want to reject this quotation?');">
                                                @csrf
                                                <button type="submit" class="dropdown-item-btn" style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 14px; border: none; background: transparent; color: #dc2626; font-size: 12px; font-weight: 500; cursor: pointer; text-align: left;">
                                                    <i data-lucide="x-circle" style="width: 14px; height: 14px; color: #dc2626;"></i>
                                                    Reject
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('quotations.convert', $quotation->id) }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item-btn" style="width: 100%; display: flex; align-items: center; gap: 8px; padding: 8px 14px; border: none; background: transparent; color: #1677ff; font-size: 12px; font-weight: 500; cursor: pointer; text-align: left;">
                                                    <i data-lucide="receipt" style="width: 14px; height: 14px; color: #1677ff;"></i>
                                                    Convert To Invoice
                                                </button>
                                            </form>
                                        </div>
                                    </div>

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

                                    No company selected. Create a company first.

                                @else

                                    No quotations found.

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

    function toggleQuotationDropdown(event, id) {
        event.stopPropagation();
        const currentDropdown = document.getElementById('dropdown-' + id);
        const isOpen = currentDropdown && currentDropdown.style.display === 'block';

        document.querySelectorAll('.dropdown-menu-list').forEach(el => {
            el.style.display = 'none';
        });

        if (currentDropdown && !isOpen) {
            currentDropdown.style.display = 'block';
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-wrapper')) {
            document.querySelectorAll('.dropdown-menu-list').forEach(el => {
                el.style.display = 'none';
            });
        }
    });
</script>

<style>
    .dropdown-item-btn:hover {
        background: #f1f5f9 !important;
    }
</style>

</body>
</html>
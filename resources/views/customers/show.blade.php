<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Details</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer-show.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

@include('partials.sidebar')


<div class="page content-wrapper">

    @include('partials.topbar')


    <main>

        {{-- BREADCRUMB --}}
        <div class="breadcrumb">

            <a href="{{ route('dashboard', [
                'company_id' => $customer->company_id
            ]) }}">
                Home
            </a>

            <span>›</span>


            <a href="{{ route('customers.index', [
                'company_id' => $customer->company_id
            ]) }}">
                Customers
            </a>

            <span>›</span>

            Customer Details

        </div>


        {{-- PAGE HEADER --}}
        <div class="details-heading">

            <div>
                <h1>Customer Details</h1>

                <p>
                    View and manage customer information
                </p>
            </div>


            <div class="heading-actions">

                <a href="{{ route('customers.index', [
                    'company_id' => $customer->company_id
                ]) }}">

                    <i data-lucide="arrow-left"></i>

                    Back to Customers

                </a>


                @if(Route::has('customers.edit'))

                    <a href="{{ route('customers.edit', $customer->id) }}">

                        <i data-lucide="pencil"></i>

                        Edit Customer

                    </a>

                @endif



            </div>

        </div>


        {{-- CUSTOMER SUMMARY --}}
        <section class="customer-summary">

            <div class="customer-profile">

                <div class="customer-avatar">
                    {{ strtoupper(substr(
                        $customer->business_name ?: $customer->customer_name,
                        0,
                        2
                    )) }}
                </div>


                <div>

                    <h2>
                        {{ $customer->business_name ?: $customer->customer_name }}
                    </h2>

                    <p>
                        {{ $customer->customer_name }}
                    </p>


                    <span class="status {{ strtolower($customer->status) }}">
                        {{ ucfirst(strtolower($customer->status)) }}
                    </span>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon">
                    <i data-lucide="file-text"></i>
                </div>

                <div>
                    <p>Total Quotations</p>

                    <strong>
                        {{ $totalQuotations }}
                    </strong>
                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon">
                    <i data-lucide="receipt-text"></i>
                </div>

                <div>
                    <p>Total Invoices</p>

                    <strong>
                        {{ $totalInvoices }}
                    </strong>
                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon">
                    <i data-lucide="credit-card"></i>
                </div>

                <div>

                    <p>
                        Outstanding Amount
                    </p>

                    <strong>
                        LKR {{ number_format($outstandingAmount, 2) }}
                    </strong>

                </div>

            </div>

        </section>


        {{-- INFORMATION --}}
        <section class="info-grid">


            {{-- CONTACT INFORMATION --}}
            <div class="info-card">

                <h3>
                    <i data-lucide="user"></i>
                    Contact Information
                </h3>


                <div class="info-row">

                    <span>
                        Contact Person
                    </span>

                    <strong>
                        {{ $customer->customer_name }}
                    </strong>

                </div>


                <div class="info-row">

                    <span>
                        Email
                    </span>

                    <strong>
                        {{ $customer->email ?: '-' }}
                    </strong>

                </div>


                <div class="info-row">

                    <span>
                        Phone
                    </span>

                    <strong>
                        {{ $customer->phone ?: '-' }}
                    </strong>

                </div>

            </div>



            {{-- BUSINESS INFORMATION --}}
            <div class="info-card">

                <h3>
                    <i data-lucide="building-2"></i>
                    Business Information
                </h3>


                <div class="info-row">

                    <span>
                        Business Name
                    </span>

                    <strong>
                        {{ $customer->business_name ?: '-' }}
                    </strong>

                </div>


                <div class="info-row">

                    <span>
                        Registration Number
                    </span>

                    <strong>
                        {{ $customer->registration_number ?: '-' }}
                    </strong>

                </div>


                <div class="info-row">

                    <span>
                        VAT Number
                    </span>

                    <strong>
                        {{ $customer->vat_number ?: '-' }}
                    </strong>

                </div>

            </div>



            {{-- ADDRESS --}}
            <div class="info-card">

                <h3>
                    <i data-lucide="map-pin"></i>
                    Address
                </h3>


                <div class="info-row">

                    <span>
                        Street Address
                    </span>

                    <strong>

                        {{ $customer->address_line_1 ?: '-' }}

                        @if($customer->address_line_2)
                            <br>
                            {{ $customer->address_line_2 }}
                        @endif

                    </strong>

                </div>


                <div class="info-row">

                    <span>
                        City
                    </span>

                    <strong>
                        {{ $customer->city ?: '-' }}
                    </strong>

                </div>


                <div class="info-row">

                    <span>
                        Country
                    </span>

                    <strong>
                        {{ $customer->country ?: '-' }}
                    </strong>

                </div>

            </div>



            {{-- ADDITIONAL INFORMATION --}}
            <div class="info-card">

                <h3>
                    <i data-lucide="notebook"></i>
                    Additional Information
                </h3>


                <label>
                    Internal Notes
                </label>


                <div class="notes">
                    {{ $customer->notes ?: 'No notes available.' }}
                </div>

            </div>

        </section>


        {{-- HISTORY --}}
        <section class="history-card">

            <div class="tabs">

                <button
                    type="button"
                    class="active"
                >
                    Quotations
                </button>


                <button type="button">
                    Invoices
                </button>


                <button type="button">
                    Payments
                </button>

            </div>


            <div class="history-title">

                <h3>
                    Recent Quotations
                </h3>


                <a href="{{ route('quotations.index', [
                    'company_id' => $customer->company_id,
                    'customer_id' => $customer->id
                ]) }}">
                    View All
                </a>

            </div>


            <div class="table-wrap">

                <table>

                    <thead>

                    <tr>
                        <th>#</th>
                        <th>Quotation Number</th>
                        <th>Date</th>
                        <th>Expiry Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                    </thead>


                    <tbody>

                    @forelse($recentQuotations as $quotation)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
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

                                {{ $quotation->expiry_date
                                    ? \Carbon\Carbon::parse(
                                        $quotation->expiry_date
                                      )->format('Y-m-d')
                                    : '-' }}

                            </td>


                            <td>
                                LKR {{ number_format($quotation->grand_total, 2) }}
                            </td>


                            <td>

                                <span class="quote-status {{ strtolower($quotation->status) }}">

                                    {{ ucfirst(strtolower($quotation->status)) }}

                                </span>

                            </td>


                            <td>

                                @if(Route::has('quotations.show'))

                                    <a
                                        href="{{ route(
                                            'quotations.show',
                                            $quotation->id
                                        ) }}"
                                        class="view-btn"
                                    >

                                        <i data-lucide="eye"></i>

                                        View

                                    </a>

                                @endif

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="empty"
                            >
                                No quotations found for this customer.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>

</html>
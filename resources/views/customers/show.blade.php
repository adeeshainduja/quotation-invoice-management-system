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

        <a href="{{ route('customers.index') }}" class="active">
            <i data-lucide="users"></i>
            Customers
        </a>

        <a href="#">
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

        <div class="company-box">
            <i data-lucide="building-2"></i>
            {{ $company->name ?? 'Company' }}
        </div>

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

            <a href="{{ route('customers.index', ['company_id' => $customer->company_id]) }}">
                Customers
            </a>

            <span>›</span>
            Customer Details
        </div>


        <div class="details-heading">

            <div>
                <h1>Customer Details</h1>
                <p>View and manage customer information</p>
            </div>

            <div class="heading-actions">

                <a href="{{ route('customers.index', ['company_id' => $customer->company_id]) }}">
                    <i data-lucide="arrow-left"></i>
                    Back to Customers
                </a>

                <a href="#">
                    <i data-lucide="pencil"></i>
                    Edit Customer
                </a>

                <button class="delete-btn">
                    <i data-lucide="trash-2"></i>
                    Delete
                </button>

            </div>

        </div>


        <section class="customer-summary">

            <div class="customer-profile">

                <div class="customer-avatar">
                    {{ strtoupper(substr($customer->business_name, 0, 2)) }}
                </div>

                <div>

                    <h2>{{ $customer->business_name }}</h2>

                    <p>{{ $customer->customer_name }}</p>

                    <span class="status {{ strtolower($customer->status) }}">
                        {{ $customer->status }}
                    </span>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon">
                    <i data-lucide="file-text"></i>
                </div>

                <div>
                    <p>Total Quotations</p>
                    <strong>{{ $totalQuotations }}</strong>
                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon">
                    <i data-lucide="receipt-text"></i>
                </div>

                <div>
                    <p>Total Invoices</p>
                    <strong>{{ $totalInvoices }}</strong>
                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon">
                    <i data-lucide="credit-card"></i>
                </div>

                <div>
                    <p>Outstanding Amount</p>

                    <strong>
                        LKR {{ number_format($outstandingAmount, 2) }}
                    </strong>
                </div>

            </div>

        </section>


        <section class="info-grid">

            <div class="info-card">

                <h3>
                    <i data-lucide="user"></i>
                    Contact Information
                </h3>

                <div class="info-row">
                    <span>Contact Person</span>
                    <strong>{{ $customer->customer_name }}</strong>
                </div>

                <div class="info-row">
                    <span>Email</span>
                    <strong>{{ $customer->email ?: '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Phone</span>
                    <strong>{{ $customer->phone ?: '-' }}</strong>
                </div>

            </div>


            <div class="info-card">

                <h3>
                    <i data-lucide="building-2"></i>
                    Business Information
                </h3>

                <div class="info-row">
                    <span>Business Name</span>
                    <strong>{{ $customer->business_name }}</strong>
                </div>

                <div class="info-row">
                    <span>Registration Number</span>
                    <strong>{{ $customer->registration_number ?: '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>VAT Number</span>
                    <strong>{{ $customer->vat_number ?: '-' }}</strong>
                </div>

            </div>


            <div class="info-card">

                <h3>
                    <i data-lucide="map-pin"></i>
                    Address
                </h3>

                <div class="info-row">
                    <span>Street Address</span>

                    <strong>
                        {{ $customer->address_line_1 }}
                        {{ $customer->address_line_2 }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>City</span>
                    <strong>{{ $customer->city }}</strong>
                </div>

                <div class="info-row">
                    <span>Country</span>
                    <strong>{{ $customer->country }}</strong>
                </div>

            </div>


            <div class="info-card">

                <h3>
                    <i data-lucide="notebook"></i>
                    Additional Information
                </h3>

                <label>Internal Notes</label>

                <div class="notes">
                    {{ $customer->notes ?: 'No notes available.' }}
                </div>

            </div>

        </section>


        <section class="history-card">

            <div class="tabs">

                <button class="active">
                    Quotations
                </button>

                <button>
                    Invoices
                </button>

                <button>
                    Payments
                </button>

            </div>


            <div class="history-title">

                <h3>Recent Quotations</h3>

                <a href="#">
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

                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $quotation->quotation_number }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('Y-m-d') }}
                            </td>

                            <td>
                                {{ $quotation->expiry_date
                                    ? \Carbon\Carbon::parse($quotation->expiry_date)->format('Y-m-d')
                                    : '-' }}
                            </td>

                            <td>
                                LKR {{ number_format($quotation->grand_total, 2) }}
                            </td>

                            <td>

                                <span class="quote-status {{ strtolower($quotation->status) }}">
                                    {{ $quotation->status }}
                                </span>

                            </td>

                            <td>

                                <a href="#" class="view-btn">
                                    <i data-lucide="eye"></i>
                                    View
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="empty">
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
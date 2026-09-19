<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        <a class="active" href="{{ route('dashboard') }}">
            <i data-lucide="house"></i>
            Dashboard
        </a>

        <a href="#">
            <i data-lucide="building-2"></i>
            Companies
        </a>

        <a href="#">
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
            <i data-lucide="credit-card"></i>
            Payments
        </a>

        <a href="#">
            <i data-lucide="notebook-tabs"></i>
            Templates
        </a>

        <a href="#">
            <i data-lucide="history"></i>
            Activity Logs
        </a>

        <div class="nav-line"></div>

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

            <form method="GET" action="{{ route('dashboard') }}">

                <select
                    name="company_id"
                    onchange="this.form.submit()"
                    class="company-select"
                >

                    @foreach($companies as $item)

                        <option
                            value="{{ $item->id }}"
                            {{ $companyId == $item->id ? 'selected' : '' }}
                        >
                            {{ $item->name }}
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
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>Administrator</small>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button class="logout" title="Logout">
                        <i data-lucide="log-out"></i>
                    </button>
                </form>

            </div>

        </div>

    </header>


    <main>

        <div class="heading">

            <div>
                <h1>Dashboard</h1>
                <p>
                    Welcome back, {{ explode(' ', auth()->user()->name)[0] }}! 🎉
                </p>
            </div>

            <div class="heading-right">

                <span>{{ now()->format('l, d F Y') }}</span>

                <button class="create-button">
                    + &nbsp; Create New
                    <i data-lucide="chevron-down"></i>
                </button>

            </div>

        </div>


        {{-- STAT CARDS --}}
        <section class="stats">

            <div class="stat-card">

                <div class="stat-icon blue">
                    <i data-lucide="briefcase-business"></i>
                </div>

                <div>
                    <strong>{{ $companyCount }}</strong>
                    <h3>Companies</h3>
                    <p>Active companies</p>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon green">
                    <i data-lucide="users"></i>
                </div>

                <div>
                    <strong>{{ $customerCount }}</strong>
                    <h3>Customers</h3>
                    <p>Total customers</p>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon purple">
                    <i data-lucide="file-text"></i>
                </div>

                <div>
                    <strong>{{ $quotationCount }}</strong>
                    <h3>Quotations</h3>
                    <p>This month</p>
                </div>

                <span class="growth">
                    ↑ {{ $quotationGrowth }}%
                </span>

            </div>


            <div class="stat-card">

                <div class="stat-icon yellow">
                    <i data-lucide="receipt-text"></i>
                </div>

                <div>
                    <strong>{{ $invoiceCount }}</strong>
                    <h3>Invoices</h3>
                    <p>This month</p>
                </div>

                <span class="growth">
                    ↑ {{ $invoiceGrowth }}%
                </span>

            </div>

        </section>


        {{-- CHARTS --}}
        <section class="charts">

            <div class="card chart-main">

                <h2>Monthly Overview</h2>

                <div class="chart-box">
                    <canvas id="monthlyChart"></canvas>
                </div>

            </div>


            <div class="card">

                <h2>Invoice Status</h2>

                <div class="invoice-status">

                    <div class="donut">
                        <canvas id="invoiceChart"></canvas>
                    </div>

                    <div class="legend">

                        <p>
                            <span class="dot paid"></span>
                            Paid
                            <strong>{{ $invoiceStatus->paid ?? 0 }}</strong>
                        </p>

                        <p>
                            <span class="dot partial"></span>
                            Partial
                            <strong>{{ $invoiceStatus->partial ?? 0 }}</strong>
                        </p>

                        <p>
                            <span class="dot unpaid"></span>
                            Unpaid
                            <strong>{{ $invoiceStatus->unpaid ?? 0 }}</strong>
                        </p>

                    </div>

                </div>

            </div>

        </section>


        {{-- RECENT TABLES --}}
        <section class="tables">

            <div class="card">

                <div class="card-title">
                    <h2>Recent Quotations</h2>
                    <a href="#">View All →</a>
                </div>

                <div class="table-wrap">

                    <table>

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Expiry Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                        @forelse($recentQuotations as $quotation)

                            <tr>

                                <td>{{ $quotation->quotation_number }}</td>

                                <td>{{ $quotation->business_name }}</td>

                                <td>
                                    {{ Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y') }}
                                </td>

                                <td>
                                    {{ $quotation->expiry_date
                                        ? Carbon\Carbon::parse($quotation->expiry_date)->format('d M Y')
                                        : '-' }}
                                </td>

                                <td>
                                    Rs. {{ number_format($quotation->grand_total, 2) }}
                                </td>

                                <td>
                                    <span class="status {{ strtolower($quotation->status) }}">
                                        {{ ucfirst(strtolower($quotation->status)) }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="empty">No quotations found</td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>



            <div class="card">

                <div class="card-title">
                    <h2>Recent Invoices</h2>
                    <a href="#">View All →</a>
                </div>

                <div class="table-wrap">

                    <table>

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                        @forelse($recentInvoices as $invoice)

                            @php
                                $statusClass = str_replace(
                                    '_',
                                    '-',
                                    strtolower($invoice->status)
                                );
                            @endphp

                            <tr>

                                <td>{{ $invoice->invoice_number }}</td>

                                <td>{{ $invoice->business_name }}</td>

                                <td>
                                    {{ Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
                                </td>

                                <td>
                                    Rs. {{ number_format($invoice->grand_total, 2) }}
                                </td>

                                <td>
                                    <span class="status {{ $statusClass }}">
                                        {{ ucwords(strtolower(str_replace('_', ' ', $invoice->status))) }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="empty">No invoices found</td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </section>


        {{-- BOTTOM CARDS --}}
        <section class="bottom-stats">

            <div class="bottom-card">

                <div class="stat-icon yellow">
                    <i data-lucide="coins"></i>
                </div>

                <div>
                    <h4>Outstanding Payments</h4>

                    <strong>
                        Rs. {{ number_format($outstandingAmount, 2) }}
                    </strong>

                    <p>Total outstanding amount</p>
                </div>

                <span class="red-pill">
                    {{ $outstandingCount }} Invoices
                </span>

            </div>


            <div class="bottom-card">

                <div class="stat-icon red">
                    <i data-lucide="clock-3"></i>
                </div>

                <div>
                    <h4>Overdue Invoices</h4>

                    <strong>{{ $overdueCount }}</strong>

                    <p>Invoices overdue</p>
                </div>

                <span class="red-pill">
                    Rs. {{ number_format($overdueAmount, 2) }}
                </span>

            </div>


            <div class="bottom-card">

                <div class="stat-icon green">
                    <i data-lucide="credit-card"></i>
                </div>

                <div>
                    <h4>Total Payments Received</h4>

                    <strong>
                        Rs. {{ number_format($payments, 2) }}
                    </strong>

                    <p>This month</p>
                </div>

            </div>

        </section>

    </main>

</div>


<script>
    lucide.createIcons();

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',

        data: {
            labels: @json($monthlyLabels),

            datasets: [
                {
                    label: 'Quotations',
                    data: @json($monthlyQuotations),
                    backgroundColor: '#2f80ed',
                    borderRadius: 3
                },
                {
                    label: 'Invoices',
                    data: @json($monthlyInvoices),
                    backgroundColor: '#34bd8a',
                    borderRadius: 3
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    position: 'top',
                    align: 'end'
                }
            },

            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },

                y: {
                    beginAtZero: true
                }
            }
        }
    });


    new Chart(document.getElementById('invoiceChart'), {
        type: 'doughnut',

        data: {
            labels: ['Paid', 'Partial', 'Unpaid'],

            datasets: [{
                data: [
                    {{ $invoiceStatus->paid ?? 0 }},
                    {{ $invoiceStatus->partial ?? 0 }},
                    {{ $invoiceStatus->unpaid ?? 0 }}
                ],

                backgroundColor: [
                    '#34bd8a',
                    '#2f80ed',
                    '#ffba21'
                ],

                borderWidth: 0
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            cutout: '66%',

            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>

</body>
</html>
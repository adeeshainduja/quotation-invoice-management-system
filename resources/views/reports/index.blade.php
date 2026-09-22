<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        .reports-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        @media (max-width: 1024px) {
            .reports-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .reports-stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-header {
            margin: 28px 0 16px;
        }

        .section-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            color: #071a42;
        }

        .section-header p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #64738e;
        }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 18px;
        }

        .report-category-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(30, 55, 90, .03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .report-category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 55, 90, .06);
        }

        .category-head {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 16px;
        }

        .category-icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            flex-shrink: 0;
        }

        .category-icon svg {
            width: 22px;
            height: 22px;
        }

        .category-title h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #071a42;
        }

        .category-title p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #73819a;
            line-height: 1.4;
        }

        .placeholder-list {
            list-style: none;
            padding: 0;
            margin: 0;
            border-top: 1px solid #f1f5f9;
            padding-top: 12px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .placeholder-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: #334155;
            padding: 8px 10px;
            border-radius: 6px;
            background: #f8fafc;
        }

        .placeholder-badge {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 8px;
            border-radius: 12px;
            background: #e2e8f0;
            color: #475569;
        }
    </style>
</head>

<body>

@include('partials.sidebar')

<div class="page">

    <header class="topbar">
        <button class="menu" type="button"><i data-lucide="menu"></i></button>

        <div class="topbar-right">

            @if($companies->isNotEmpty())
                <form method="GET" action="{{ route('reports.index') }}">
                    <select name="company_id"
                            class="company-select"
                            onchange="this.form.submit()">

                        @if($companies->count() > 1)
                            <option value="" {{ empty($companyId) ? 'selected' : '' }}>
                                All Companies
                            </option>
                        @endif

                        @foreach($companies as $item)
                            <option value="{{ $item->id }}"
                                {{ $companyId == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach

                    </select>
                </form>
            @endif

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
                    <small>{{ auth()->user()->isAdmin() ? 'Administrator' : 'User' }}</small>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="logout" type="submit">
                        <i data-lucide="log-out"></i>
                    </button>
                </form>
            </div>

        </div>
    </header>

    <main>

        <div class="heading">
            <div>
                <h1>Reports</h1>
                <p>Overview of system performance and business metrics</p>
            </div>

            <div class="heading-right">
                <span>{{ now()->format('l, d F Y') }}</span>
            </div>
        </div>

        {{-- SUMMARY STAT CARDS --}}
        <section class="reports-stats-grid">

            {{-- 1. Total Invoices --}}
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i data-lucide="receipt-text"></i>
                </div>
                <div>
                    <strong>{{ number_format($totalInvoicesCount) }}</strong>
                    <h3>Total Invoices</h3>
                    <p>Total invoices generated</p>
                </div>
            </div>

            {{-- 2. Total Invoice Amount --}}
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i data-lucide="coins"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($totalInvoiceValue, 2) }}</strong>
                    <h3>Total Invoice Amount</h3>
                    <p>Cumulative invoice value</p>
                </div>
            </div>

            {{-- 3. Payments Received --}}
            <div class="stat-card">
                <div class="stat-icon green">
                    <i data-lucide="credit-card"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($totalPaymentsReceived, 2) }}</strong>
                    <h3>Payments Received</h3>
                    <p>Total collected payments</p>
                </div>
            </div>

            {{-- 4. Outstanding Payments --}}
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i data-lucide="clock-3"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($outstandingInvoiceAmount, 2) }}</strong>
                    <h3>Outstanding Payments</h3>
                    <p>Pending invoice balances</p>
                </div>
            </div>

            {{-- 5. Total Quotations --}}
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i data-lucide="file-text"></i>
                </div>
                <div>
                    <strong>{{ number_format($totalQuotationsCount) }}</strong>
                    <h3>Total Quotations</h3>
                    <p>Total quotations generated</p>
                </div>
            </div>

            {{-- 6. Total Customers --}}
            <div class="stat-card">
                <div class="stat-icon green">
                    <i data-lucide="users"></i>
                </div>
                <div>
                    <strong>{{ number_format($totalCustomersCount) }}</strong>
                    <h3>Total Customers</h3>
                    <p>Total registered customers</p>
                </div>
            </div>

        </section>


        {{-- FUTURE-READY REPORT SECTIONS --}}
        <div class="section-header">
            <h2>Report Categories</h2>
            <p>Future-ready reporting sections and detailed analytical views</p>
        </div>

        <section class="reports-grid">

            {{-- 1. Sales Reports --}}
            <div class="report-category-card">
                <div>
                    <div class="category-head">
                        <div class="category-icon blue">
                            <i data-lucide="trending-up"></i>
                        </div>
                        <div class="category-title">
                            <h3>Sales Reports</h3>
                            <p>Periodic sales trends, recurring revenue analysis, and sales performance metrics.</p>
                        </div>
                    </div>

                    <ul class="placeholder-list">
                        <li class="placeholder-item">
                            <span>Monthly Sales Summary</span>
                            <span class="placeholder-badge">Placeholder</span>
                        </li>
                        <li class="placeholder-item">
                            <span>Revenue Growth Analysis</span>
                            <span class="placeholder-badge">Placeholder</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- 2. Invoice Reports --}}
            <div class="report-category-card">
                <div>
                    <div class="category-head">
                        <div class="category-icon yellow">
                            <i data-lucide="receipt-text"></i>
                        </div>
                        <div class="category-title">
                            <h3>Invoice Reports</h3>
                            <p>Invoice lifecycle, overdue aging reports, and status fulfillment breakdowns.</p>
                        </div>
                    </div>

                    <ul class="placeholder-list">
                        <li class="placeholder-item">
                            <a href="{{ route('reports.invoices', $companyId ? ['company_id' => $companyId] : []) }}" style="display: flex; align-items: center; justify-content: space-between; width: 100%; text-decoration: none; color: inherit;">
                                <span style="color: #2563eb; font-weight: 500;">Invoice Aging Report</span>
                                <span class="placeholder-badge" style="background: #e0e7ff; color: #1d4ed8;">View Report →</span>
                            </a>
                        </li>
                        <li class="placeholder-item">
                            <a href="{{ route('reports.invoices', $companyId ? ['company_id' => $companyId] : []) }}" style="display: flex; align-items: center; justify-content: space-between; width: 100%; text-decoration: none; color: inherit;">
                                <span style="color: #2563eb; font-weight: 500;">Paid vs Unpaid Breakdown</span>
                                <span class="placeholder-badge" style="background: #e0e7ff; color: #1d4ed8;">View Report →</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- 3. Payment Reports --}}
            <div class="report-category-card">
                <div>
                    <div class="category-head">
                        <div class="category-icon green">
                            <i data-lucide="credit-card"></i>
                        </div>
                        <div class="category-title">
                            <h3>Payment Reports</h3>
                            <p>Payment transaction histories, payment method distribution, and collection logs.</p>
                        </div>
                    </div>

                    <ul class="placeholder-list">
                        <li class="placeholder-item">
                            <a href="{{ route('reports.payments', $companyId ? ['company_id' => $companyId] : []) }}" style="display: flex; align-items: center; justify-content: space-between; width: 100%; text-decoration: none; color: inherit;">
                                <span style="color: #2563eb; font-weight: 500;">Collection History</span>
                                <span class="placeholder-badge" style="background: #e0e7ff; color: #1d4ed8;">View Report →</span>
                            </a>
                        </li>
                        <li class="placeholder-item">
                            <a href="{{ route('reports.payments', $companyId ? ['company_id' => $companyId] : []) }}" style="display: flex; align-items: center; justify-content: space-between; width: 100%; text-decoration: none; color: inherit;">
                                <span style="color: #2563eb; font-weight: 500;">Payment Methods Summary</span>
                                <span class="placeholder-badge" style="background: #e0e7ff; color: #1d4ed8;">View Report →</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- 4. Customer Reports --}}
            <div class="report-category-card">
                <div>
                    <div class="category-head">
                        <div class="category-icon purple">
                            <i data-lucide="users"></i>
                        </div>
                        <div class="category-title">
                            <h3>Customer Reports</h3>
                            <p>Top client revenue generators, customer balances, and client engagement logs.</p>
                        </div>
                    </div>

                    <ul class="placeholder-list">
                        <li class="placeholder-item">
                            <span>Top Customers by Revenue</span>
                            <span class="placeholder-badge">Placeholder</span>
                        </li>
                        <li class="placeholder-item">
                            <span>Client Outstanding Balances</span>
                            <span class="placeholder-badge">Placeholder</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- 5. Quotation Reports --}}
            <div class="report-category-card">
                <div>
                    <div class="category-head">
                        <div class="category-icon blue">
                            <i data-lucide="file-text"></i>
                        </div>
                        <div class="category-title">
                            <h3>Quotation Reports</h3>
                            <p>Quotation conversion pipeline, accepted vs rejected ratios, and expiry tracking.</p>
                        </div>
                    </div>

                    <ul class="placeholder-list">
                        <li class="placeholder-item">
                            <span>Quotation Conversion Rate</span>
                            <span class="placeholder-badge">Placeholder</span>
                        </li>
                        <li class="placeholder-item">
                            <span>Quotation Status Pipeline</span>
                            <span class="placeholder-badge">Placeholder</span>
                        </li>
                    </ul>
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

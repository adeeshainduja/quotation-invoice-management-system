<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reports</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/invoices.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        .customer-report-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .customer-report-stats-bottom {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        @media (max-width: 1200px) {
            .customer-report-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .customer-report-stats-bottom {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .customer-report-stats,
            .customer-report-stats-bottom {
                grid-template-columns: 1fr;
            }
        }

        .report-filter-card {
            background: #ffffff;
            border: 1px solid #e3e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.035);
        }

        .report-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto;
            gap: 14px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group input,
        .filter-group select {
            height: 42px;
            padding: 0 12px;
            background: #ffffff;
            border: 1px solid #d8dfeb;
            border-radius: 8px;
            color: #172033;
            font-size: 13px;
            outline: none;
            width: 100%;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
        }

        .filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            height: 42px;
        }

        .btn-search {
            height: 42px;
            padding: 0 18px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s ease;
        }

        .btn-search:hover {
            background: #1d4ed8;
        }

        .btn-reset {
            height: 42px;
            padding: 0 16px;
            border: 1px solid #d8dfeb;
            border-radius: 8px;
            background: #ffffff;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s ease;
        }

        .btn-reset:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .export-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-export {
            height: 40px;
            padding: 0 14px;
            border: 1px solid #d8dfeb;
            border-radius: 8px;
            background: #ffffff;
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: 0.15s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .btn-export:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .btn-export svg {
            width: 16px;
            height: 16px;
        }

        .btn-print {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .btn-print:hover {
            background: #e2e8f0;
        }

        .customer-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .customer-status-badge.active   { background: #e2f8ef; color: #15976b; }
        .customer-status-badge.inactive { background: #f1f5f9; color: #64748b; }

        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        @media (max-width: 900px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }

        .analytics-card {
            background: #ffffff;
            border: 1px solid #e3e8f0;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.035);
        }

        .analytics-card h2 {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 16px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .analytics-card h2 i {
            width: 16px;
            height: 16px;
            color: #2563eb;
        }

        .analytics-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .analytics-list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 8px;
            gap: 12px;
        }

        .analytics-list-item .customer-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .analytics-list-item .customer-name {
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .analytics-list-item .customer-company {
            font-size: 11px;
            color: #64748b;
        }

        .analytics-list-item .customer-value {
            font-size: 13px;
            font-weight: 700;
            color: #2563eb;
            white-space: nowrap;
        }

        .analytics-list-item .customer-balance {
            font-size: 13px;
            font-weight: 700;
            color: #dc2626;
            white-space: nowrap;
        }

        .analytics-empty {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            font-size: 13px;
        }

        @media print {
            .sidebar,
            .topbar,
            .breadcrumb,
            .report-filter-card,
            .export-group,
            .table-footer,
            .pagination {
                display: none !important;
            }

            .page {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }

            body {
                background: #ffffff !important;
            }

            .table-card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body>

@include('partials.sidebar')

<div class="page">

    {{-- TOPBAR --}}
    <header class="topbar">
        <button class="menu-btn" type="button"><i data-lucide="menu"></i></button>

        <div class="topbar-right">
            <div class="notification">
                <i data-lucide="bell"></i>
                <span></span>
            </div>

            <div class="user-box">
                <div class="avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->isAdmin() ? 'Administrator' : 'User' }}</small>
                </div>
            </div>
        </div>
    </header>

    <main>

        {{-- BREADCRUMB --}}
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span>›</span>
            <a href="{{ route('reports.index') }}">Reports</a>
            <span>›</span>
            <span>Customer Reports</span>
        </div>

        {{-- PAGE HEADER & EXPORT BUTTONS --}}
        <div class="page-head">
            <div>
                <h1>Customer Reports</h1>
                <p>Customer performance, revenue analysis and outstanding balances</p>
            </div>

            <div class="export-group">
                <button type="button" class="btn-export" onclick="alert('Export PDF feature is ready to be linked to your export engine.')">
                    <i data-lucide="file-down"></i>
                    Export PDF
                </button>

                <button type="button" class="btn-export" onclick="alert('Export Excel feature is ready to be linked to your export engine.')">
                    <i data-lucide="sheet"></i>
                    Export Excel
                </button>

                <button type="button" class="btn-export btn-print" onclick="window.print()">
                    <i data-lucide="printer"></i>
                    Print
                </button>
            </div>
        </div>

        {{-- FILTER SECTION --}}
        <section class="report-filter-card">
            <form method="GET" action="{{ route('reports.customers') }}" class="report-filter-grid">

                <div class="filter-group">
                    <label for="from">From Date</label>
                    <input type="date" id="from" name="from" value="{{ $from }}">
                </div>

                <div class="filter-group">
                    <label for="to">To Date</label>
                    <input type="date" id="to" name="to" value="{{ $to }}">
                </div>

                <div class="filter-group">
                    <label for="company_id">Company</label>
                    <select id="company_id" name="company_id">
                        <option value="">All Companies</option>
                        @foreach($companies as $item)
                            <option value="{{ $item->id }}" {{ $companyId == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="customer_id">Customer</label>
                    <select id="customer_id" name="customer_id">
                        <option value="">All Customers</option>
                        @foreach($allCustomers as $item)
                            <option value="{{ $item->id }}" {{ $customerId == $item->id ? 'selected' : '' }}>
                                {{ $item->business_name ?: $item->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-search">
                        <i data-lucide="search"></i>
                        Search
                    </button>

                    <a href="{{ route('reports.customers') }}" class="btn-reset">
                        <i data-lucide="rotate-ccw"></i>
                        Reset
                    </a>
                </div>

            </form>
        </section>

        {{-- SUMMARY CARDS --}}
        {{-- Row 1: Primary Customer Overview --}}
        <section class="customer-report-stats">

            {{-- 1. Total Customers --}}
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i data-lucide="users"></i>
                </div>
                <div>
                    <strong>{{ number_format($totalCustomers) }}</strong>
                    <h3>Total Customers</h3>
                    <p>All customers in scope</p>
                </div>
            </div>

            {{-- 2. Active Customers --}}
            <div class="stat-card">
                <div class="stat-icon green">
                    <i data-lucide="user-check"></i>
                </div>
                <div>
                    <strong>{{ number_format($activeCustomers) }}</strong>
                    <h3>Active Customers</h3>
                    <p>Customers with active status</p>
                </div>
            </div>

            {{-- 3. Customer Revenue --}}
            <div class="stat-card">
                <div class="stat-icon green">
                    <i data-lucide="circle-dollar-sign"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($totalCustomerRevenue, 2) }}</strong>
                    <h3>Customer Revenue</h3>
                    <p>Total invoice value</p>
                </div>
            </div>

            {{-- 4. Payments Received --}}
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i data-lucide="credit-card"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($totalPaymentsReceived, 2) }}</strong>
                    <h3>Payments Received</h3>
                    <p>Total collected from customers</p>
                </div>
            </div>

        </section>

        {{-- Row 2: Additional Metrics --}}
        <section class="customer-report-stats-bottom">

            {{-- 5. Outstanding Balance --}}
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i data-lucide="clock-3"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($outstandingBalance, 2) }}</strong>
                    <h3>Outstanding Balance</h3>
                    <p>Unpaid invoice balances</p>
                </div>
            </div>

            {{-- 6. Total Quotations --}}
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i data-lucide="file-text"></i>
                </div>
                <div>
                    <strong>{{ number_format($totalQuotationsGenerated) }}</strong>
                    <h3>Total Quotations</h3>
                    <p>Quotations generated for customers</p>
                </div>
            </div>

            {{-- 7. Average Customer Value --}}
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i data-lucide="trending-up"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($averageCustomerValue, 2) }}</strong>
                    <h3>Avg. Customer Value</h3>
                    <p>Average revenue per customer</p>
                </div>
            </div>

        </section>

        {{-- ANALYTICS SECTIONS --}}
        <div class="analytics-grid">

            {{-- Top Customers By Revenue --}}
            <div class="analytics-card">
                <h2>
                    <i data-lucide="trophy"></i>
                    Top Customers by Revenue
                </h2>

                @if($topCustomers->isNotEmpty())
                    <ul class="analytics-list">
                        @foreach($topCustomers as $top)
                            <li class="analytics-list-item">
                                <div class="customer-info">
                                    <span class="customer-name">
                                        {{ $top->business_name ?: $top->customer_name }}
                                    </span>
                                    <span class="customer-company">{{ $top->company?->name ?? '—' }}</span>
                                </div>
                                <span class="customer-value">
                                    {{ $currency }} {{ number_format((float) $top->total_revenue, 2) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="analytics-empty">No customer revenue data available.</div>
                @endif
            </div>

            {{-- Customers With Outstanding Balances --}}
            <div class="analytics-card">
                <h2>
                    <i data-lucide="alert-circle"></i>
                    Outstanding Customers
                </h2>

                @if($outstandingCustomers->isNotEmpty())
                    <ul class="analytics-list">
                        @foreach($outstandingCustomers as $oc)
                            <li class="analytics-list-item">
                                <div class="customer-info">
                                    <span class="customer-name">
                                        {{ $oc->business_name ?: $oc->customer_name }}
                                    </span>
                                    <span class="customer-company">{{ $oc->company?->name ?? '—' }}</span>
                                </div>
                                <span class="customer-balance">
                                    {{ $currency }} {{ number_format((float) $oc->outstanding_amount, 2) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="analytics-empty">No outstanding balances found.</div>
                @endif
            </div>

        </div>

        {{-- CUSTOMER TABLE --}}
        <section class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Total Quotations</th>
                            <th>Total Invoices</th>
                            <th>Total Purchase Value</th>
                            <th>Total Paid</th>
                            <th>Outstanding Balance</th>
                            <th>Last Invoice Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <strong>{{ $customer->business_name ?: $customer->customer_name }}</strong>
                                    @if($customer->business_name && $customer->customer_name)
                                        <br><small style="color: #64748b;">{{ $customer->customer_name }}</small>
                                    @endif
                                </td>
                                <td>{{ $customer->company?->name ?? '—' }}</td>
                                <td>
                                    <span class="customer-status-badge {{ strtolower($customer->status) }}">
                                        {{ ucfirst(strtolower($customer->status)) }}
                                    </span>
                                </td>
                                <td>{{ number_format((int) $customer->quotation_count) }}</td>
                                <td>{{ number_format((int) $customer->invoice_count) }}</td>
                                <td>
                                    <strong>{{ $currency }} {{ number_format((float) $customer->total_purchase_value, 2) }}</strong>
                                </td>
                                <td>
                                    <span style="color: #15976b; font-weight: 600;">
                                        {{ $currency }} {{ number_format((float) $customer->total_amount_paid, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if((float) $customer->total_balance > 0)
                                        <span style="color: #dc2626; font-weight: 600;">
                                            {{ $currency }} {{ number_format((float) $customer->total_balance, 2) }}
                                        </span>
                                    @else
                                        <span style="color: #15976b; font-weight: 600;">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $customer->last_invoice_date
                                        ? \Carbon\Carbon::parse($customer->last_invoice_date)->format('d/m/Y')
                                        : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <i data-lucide="users-x"></i>
                                    <p>No customers found matching the specified criteria</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
                <div class="table-footer">
                    <span>
                        Showing {{ $customers->firstItem() ?? 0 }}
                        to {{ $customers->lastItem() ?? 0 }}
                        of {{ $customers->total() }} customers
                    </span>

                    {{ $customers->links() }}
                </div>
            @endif
        </section>

    </main>

</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>

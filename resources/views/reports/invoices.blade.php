<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Reports</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/invoices.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        .invoice-report-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .invoice-report-stats-bottom {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        @media (max-width: 1200px) {
            .invoice-report-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .invoice-report-stats-bottom {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .invoice-report-stats,
            .invoice-report-stats-bottom {
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
            <span>Invoice Reports</span>
        </div>

        {{-- PAGE HEADER & EXPORT BUTTONS --}}
        <div class="page-head">
            <div>
                <h1>Invoice Reports</h1>
                <p>Detailed invoice performance and payment tracking</p>
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

        {{-- FILTER AREA --}}
        <section class="report-filter-card">
            <form method="GET" action="{{ route('reports.invoices') }}" class="report-filter-grid">

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
                        @foreach($customers as $item)
                            <option value="{{ $item->id }}" {{ $customerId == $item->id ? 'selected' : '' }}>
                                {{ $item->business_name ?: $item->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="PAID" {{ $status == 'PAID' ? 'selected' : '' }}>Paid</option>
                        <option value="PARTIALLY_PAID" {{ in_array($status, ['PARTIAL', 'PARTIALLY_PAID']) ? 'selected' : '' }}>Partially Paid</option>
                        <option value="PENDING" {{ $status == 'PENDING' ? 'selected' : '' }}>Pending</option>
                        <option value="OVERDUE" {{ $status == 'OVERDUE' ? 'selected' : '' }}>Overdue</option>
                        <option value="SENT" {{ $status == 'SENT' ? 'selected' : '' }}>Sent</option>
                        <option value="DRAFT" {{ $status == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                        <option value="CANCELLED" {{ $status == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-search">
                        <i data-lucide="search"></i>
                        Search
                    </button>

                    <a href="{{ route('reports.invoices') }}" class="btn-reset">
                        <i data-lucide="rotate-ccw"></i>
                        Reset
                    </a>
                </div>

            </form>
        </section>

        {{-- SUMMARY CARDS --}}
        {{-- Row 1: Primary Values --}}
        <section class="invoice-report-stats">

            {{-- 1. Total Invoices --}}
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i data-lucide="receipt-text"></i>
                </div>
                <div>
                    <strong>{{ number_format($totalInvoices) }}</strong>
                    <h3>Total Invoices</h3>
                    <p>Total filtered invoices</p>
                </div>
            </div>

            {{-- 2. Total Invoice Amount --}}
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i data-lucide="coins"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($totalInvoiceAmount, 2) }}</strong>
                    <h3>Total Invoice Amount</h3>
                    <p>Cumulative invoice total</p>
                </div>
            </div>

            {{-- 3. Paid Amount --}}
            <div class="stat-card">
                <div class="stat-icon green">
                    <i data-lucide="credit-card"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($paidAmount, 2) }}</strong>
                    <h3>Paid Amount</h3>
                    <p>Total payments received</p>
                </div>
            </div>

            {{-- 4. Outstanding Amount --}}
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i data-lucide="clock-3"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($outstandingAmount, 2) }}</strong>
                    <h3>Outstanding Amount</h3>
                    <p>Remaining balance due</p>
                </div>
            </div>

        </section>

        {{-- Row 2: Status Breakdown Counts --}}
        <section class="invoice-report-stats-bottom">

            {{-- 5. Paid Invoices --}}
            <div class="stat-card">
                <div class="stat-icon green">
                    <i data-lucide="badge-check"></i>
                </div>
                <div>
                    <strong>{{ number_format($paidInvoices) }}</strong>
                    <h3>Paid Invoices</h3>
                    <p>Invoices fully paid</p>
                </div>
            </div>

            {{-- 6. Pending Invoices --}}
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i data-lucide="clock"></i>
                </div>
                <div>
                    <strong>{{ number_format($pendingInvoices) }}</strong>
                    <h3>Pending Invoices</h3>
                    <p>Unpaid or partial invoices</p>
                </div>
            </div>

            {{-- 7. Overdue Invoices --}}
            <div class="stat-card">
                <div class="stat-icon red">
                    <i data-lucide="alert-circle"></i>
                </div>
                <div>
                    <strong>{{ number_format($overdueInvoices) }}</strong>
                    <h3>Overdue Invoices</h3>
                    <p>Past due date with balance</p>
                </div>
            </div>

        </section>

        {{-- INVOICE TABLE --}}
        <section class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice Number</th>
                            <th>Customer</th>
                            <th>Company</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            @php
                                $statusClass = strtolower(str_replace('_', '-', $invoice->status));
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                </td>
                                <td>
                                    {{ $invoice->customer?->business_name ?: ($invoice->customer?->customer_name ?: '-') }}
                                </td>
                                <td>
                                    {{ $invoice->company?->name ?: '-' }}
                                </td>
                                <td>
                                    {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}
                                </td>
                                <td>
                                    {{ $currency }} {{ number_format($invoice->grand_total, 2) }}
                                </td>
                                <td>
                                    {{ $currency }} {{ number_format($invoice->amount_paid, 2) }}
                                </td>
                                <td>
                                    <strong>{{ $currency }} {{ number_format($invoice->balance_amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="status {{ $statusClass }}">
                                        {{ ucwords(strtolower(str_replace('_', ' ', $invoice->status))) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <i data-lucide="file-x2"></i>
                                    <p>No invoices found matching the specified criteria</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="table-footer">
                    <span>
                        Showing {{ $invoices->firstItem() ?? 0 }}
                        to {{ $invoices->lastItem() ?? 0 }}
                        of {{ $invoices->total() }} invoices
                    </span>

                    {{ $invoices->links() }}
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reports</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/invoices.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        .payment-report-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .payment-report-stats-bottom {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        @media (max-width: 1200px) {
            .payment-report-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .payment-report-stats-bottom {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .payment-report-stats,
            .payment-report-stats-bottom {
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

        .method-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background: #f1f5f9;
            color: #334155;
        }

        .method-badge.cash {
            background: #e2f8ef;
            color: #15976b;
        }

        .method-badge.bank-transfer {
            background: #e5f0ff;
            color: #1d4ed8;
        }

        .method-badge.card {
            background: #eeeafe;
            color: #6d28d9;
        }

        .method-badge.cheque {
            background: #fff3d9;
            color: #b45309;
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

<div class="page content-wrapper">

    @include('partials.topbar')

    <main>

        {{-- BREADCRUMB --}}
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span>›</span>
            <a href="{{ route('reports.index') }}">Reports</a>
            <span>›</span>
            <span>Payment Reports</span>
        </div>

        {{-- PAGE HEADER & EXPORT BUTTONS --}}
        <div class="page-head">
            <div>
                <h1>Payment Reports</h1>
                <p>Payment collection history and transaction analysis</p>
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
            <form method="GET" action="{{ route('reports.payments') }}" class="report-filter-grid">

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
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method">
                        <option value="">All Methods</option>
                        <option value="CASH" {{ $paymentMethod === 'CASH' ? 'selected' : '' }}>Cash</option>
                        <option value="BANK_TRANSFER" {{ $paymentMethod === 'BANK_TRANSFER' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="CARD" {{ $paymentMethod === 'CARD' ? 'selected' : '' }}>Card</option>
                        <option value="CHEQUE" {{ $paymentMethod === 'CHEQUE' ? 'selected' : '' }}>Cheque</option>
                        <option value="OTHER" {{ $paymentMethod === 'OTHER' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-search">
                        <i data-lucide="search"></i>
                        Search
                    </button>

                    <a href="{{ route('reports.payments') }}" class="btn-reset">
                        <i data-lucide="rotate-ccw"></i>
                        Reset
                    </a>
                </div>

            </form>
        </section>

        {{-- SUMMARY CARDS --}}
        {{-- Row 1: Primary Collection Overview --}}
        <section class="payment-report-stats">

            {{-- 1. Total Received --}}
            <div class="stat-card">
                <div class="stat-icon green">
                    <i data-lucide="credit-card"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($totalPaymentsReceived, 2) }}</strong>
                    <h3>Total Received</h3>
                    <p>Total payments received</p>
                </div>
            </div>

            {{-- 2. Payment Count --}}
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i data-lucide="receipt"></i>
                </div>
                <div>
                    <strong>{{ number_format($numberOfPayments) }}</strong>
                    <h3>Payment Count</h3>
                    <p>Number of payments</p>
                </div>
            </div>

            {{-- 3. This Month Collection --}}
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i data-lucide="calendar-check"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($thisMonthPayments, 2) }}</strong>
                    <h3>This Month Collection</h3>
                    <p>Current month collection</p>
                </div>
            </div>

            {{-- 7. Outstanding Balance --}}
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i data-lucide="clock-3"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($outstandingInvoiceAmount, 2) }}</strong>
                    <h3>Outstanding Balance</h3>
                    <p>Remaining invoice amount</p>
                </div>
            </div>

        </section>

        {{-- Row 2: Payment Method Distributions --}}
        <section class="payment-report-stats-bottom">

            {{-- 4. Cash Collection --}}
            <div class="stat-card">
                <div class="stat-icon green">
                    <i data-lucide="banknote"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($cashPayments, 2) }}</strong>
                    <h3>Cash Collection</h3>
                    <p>Total cash payments</p>
                </div>
            </div>

            {{-- 5. Bank Collection --}}
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i data-lucide="landmark"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($bankPayments, 2) }}</strong>
                    <h3>Bank Collection</h3>
                    <p>Total bank payments</p>
                </div>
            </div>

            {{-- 6. Card Collection --}}
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i data-lucide="credit-card"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($cardPayments, 2) }}</strong>
                    <h3>Card Collection</h3>
                    <p>Total card payments</p>
                </div>
            </div>

        </section>

        {{-- PAYMENT TABLE --}}
        <section class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Payment Date</th>
                            <th>Payment Reference</th>
                            <th>Invoice Number</th>
                            <th>Customer</th>
                            <th>Company</th>
                            <th>Payment Method</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            @php
                                $methodClass = strtolower(str_replace('_', '-', $payment->payment_method));
                            @endphp
                            <tr>
                                <td>
                                    {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    {{ $payment->reference ?: '-' }}
                                </td>
                                <td>
                                    <strong>{{ $payment->invoice?->invoice_number ?: '-' }}</strong>
                                </td>
                                <td>
                                    {{ $payment->invoice?->customer?->business_name ?: ($payment->invoice?->customer?->customer_name ?: '-') }}
                                </td>
                                <td>
                                    {{ $payment->invoice?->company?->name ?: '-' }}
                                </td>
                                <td>
                                    <span class="method-badge {{ $methodClass }}">
                                        {{ ucwords(strtolower(str_replace('_', ' ', $payment->payment_method))) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $currency }} {{ number_format($payment->amount, 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i data-lucide="file-x2"></i>
                                    <p>No payments found matching the specified criteria</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>
                    Showing {{ $payments->firstItem() ?? 0 }}
                    to {{ $payments->lastItem() ?? 0 }}
                    of {{ $payments->total() }} payments
                </span>
            </div>
        </section>

    </main>

</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Reports</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/invoices.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        .quotation-report-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .quotation-report-stats-bottom {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        @media (max-width: 1200px) {
            .quotation-report-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .quotation-report-stats-bottom {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .quotation-report-stats,
            .quotation-report-stats-bottom {
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

        .quotation-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            background: #f1f5f9;
            color: #334155;
        }

        .quotation-status-badge.draft     { background: #f1f5f9; color: #64748b; }
        .quotation-status-badge.sent      { background: #e5f0ff; color: #1d4ed8; }
        .quotation-status-badge.accepted  { background: #e2f8ef; color: #15976b; }
        .quotation-status-badge.rejected  { background: #ffe4e6; color: #be123c; }
        .quotation-status-badge.expired   { background: #fff3d9; color: #b45309; }
        .quotation-status-badge.converted { background: #eeeafe; color: #6d28d9; }

        .pipeline-card {
            background: #ffffff;
            border: 1px solid #e3e8f0;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 28px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.035);
        }

        .pipeline-card h2 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 18px 0;
        }

        .pipeline-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 14px;
        }

        .pipeline-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px 12px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e3e8f0;
            gap: 6px;
        }

        .pipeline-item .pipeline-count {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .pipeline-item .pipeline-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-align: center;
        }

        .pipeline-item.draft-item   { border-color: #cbd5e1; }
        .pipeline-item.sent-item    { border-color: #93c5fd; }
        .pipeline-item.accepted-item{ border-color: #6ee7b7; }
        .pipeline-item.rejected-item{ border-color: #fca5a5; }
        .pipeline-item.expired-item { border-color: #fcd34d; }
        .pipeline-item.converted-item{ border-color: #c4b5fd; }

        .conversion-rate-bar {
            margin-top: 18px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .conversion-rate-label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
        }

        .rate-track {
            flex: 1;
            height: 10px;
            background: #e3e8f0;
            border-radius: 99px;
            overflow: hidden;
        }

        .rate-fill {
            height: 100%;
            background: linear-gradient(90deg, #2563eb, #7c3aed);
            border-radius: 99px;
            transition: width 0.5s ease;
        }

        .rate-value {
            font-size: 15px;
            font-weight: 800;
            color: #2563eb;
            white-space: nowrap;
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
            <span>Quotation Reports</span>
        </div>

        {{-- PAGE HEADER & EXPORT BUTTONS --}}
        <div class="page-head">
            <div>
                <h1>Quotation Reports</h1>
                <p>Quotation performance, conversion tracking and sales pipeline</p>
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
            <form method="GET" action="{{ route('reports.quotations') }}" class="report-filter-grid">

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
                        <option value="DRAFT"     {{ $status === 'DRAFT'     ? 'selected' : '' }}>Draft</option>
                        <option value="SENT"      {{ $status === 'SENT'      ? 'selected' : '' }}>Sent</option>
                        <option value="ACCEPTED"  {{ $status === 'ACCEPTED'  ? 'selected' : '' }}>Accepted</option>
                        <option value="REJECTED"  {{ $status === 'REJECTED'  ? 'selected' : '' }}>Rejected</option>
                        <option value="EXPIRED"   {{ $status === 'EXPIRED'   ? 'selected' : '' }}>Expired</option>
                        <option value="CONVERTED" {{ $status === 'CONVERTED' ? 'selected' : '' }}>Converted</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-search">
                        <i data-lucide="search"></i>
                        Search
                    </button>

                    <a href="{{ route('reports.quotations') }}" class="btn-reset">
                        <i data-lucide="rotate-ccw"></i>
                        Reset
                    </a>
                </div>

            </form>
        </section>

        {{-- SUMMARY CARDS --}}
        {{-- Row 1: Primary Quotation Overview --}}
        <section class="quotation-report-stats">

            {{-- 1. Total Quotations --}}
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i data-lucide="file-text"></i>
                </div>
                <div>
                    <strong>{{ number_format($totalQuotations) }}</strong>
                    <h3>Total Quotations</h3>
                    <p>All quotations in range</p>
                </div>
            </div>

            {{-- 2. Total Quotation Value --}}
            <div class="stat-card">
                <div class="stat-icon green">
                    <i data-lucide="circle-dollar-sign"></i>
                </div>
                <div>
                    <strong>{{ $currency }} {{ number_format($totalQuotationValue, 2) }}</strong>
                    <h3>Total Quotation Value</h3>
                    <p>Combined grand total</p>
                </div>
            </div>

            {{-- 3. Accepted --}}
            <div class="stat-card">
                <div class="stat-icon green">
                    <i data-lucide="check-circle-2"></i>
                </div>
                <div>
                    <strong>{{ number_format($acceptedQuotations) }}</strong>
                    <h3>Accepted Quotations</h3>
                    <p>Client-approved quotes</p>
                </div>
            </div>

            {{-- 4. Converted --}}
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i data-lucide="arrow-right-left"></i>
                </div>
                <div>
                    <strong>{{ number_format($convertedQuotations) }}</strong>
                    <h3>Converted to Invoice</h3>
                    <p>Quotes with linked invoices</p>
                </div>
            </div>

        </section>

        {{-- Row 2: Status Breakdown --}}
        <section class="quotation-report-stats-bottom">

            {{-- 5. Pending --}}
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i data-lucide="clock"></i>
                </div>
                <div>
                    <strong>{{ number_format($pendingQuotations) }}</strong>
                    <h3>Pending Quotations</h3>
                    <p>Draft &amp; sent quotes</p>
                </div>
            </div>

            {{-- 6. Rejected --}}
            <div class="stat-card">
                <div class="stat-icon red">
                    <i data-lucide="x-circle"></i>
                </div>
                <div>
                    <strong>{{ number_format($rejectedQuotations) }}</strong>
                    <h3>Rejected Quotations</h3>
                    <p>Client-declined quotes</p>
                </div>
            </div>

            {{-- 7. Expired --}}
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i data-lucide="calendar-x-2"></i>
                </div>
                <div>
                    <strong>{{ number_format($expiredQuotations) }}</strong>
                    <h3>Expired Quotations</h3>
                    <p>Past expiry date</p>
                </div>
            </div>

        </section>

        {{-- QUOTATION STATUS PIPELINE --}}
        <section class="pipeline-card">
            <h2>Quotation Status Pipeline</h2>

            <div class="pipeline-grid">
                <div class="pipeline-item draft-item">
                    <span class="pipeline-count">{{ number_format(\App\Models\Quotation::where('status', 'DRAFT')->when($companyId, fn($q) => $q->where('company_id', $companyId))->count()) }}</span>
                    <span class="pipeline-label">Draft</span>
                </div>
                <div class="pipeline-item sent-item">
                    <span class="pipeline-count">{{ number_format(\App\Models\Quotation::where('status', 'SENT')->when($companyId, fn($q) => $q->where('company_id', $companyId))->count()) }}</span>
                    <span class="pipeline-label">Sent</span>
                </div>
                <div class="pipeline-item accepted-item">
                    <span class="pipeline-count">{{ number_format($acceptedQuotations) }}</span>
                    <span class="pipeline-label">Accepted</span>
                </div>
                <div class="pipeline-item rejected-item">
                    <span class="pipeline-count">{{ number_format($rejectedQuotations) }}</span>
                    <span class="pipeline-label">Rejected</span>
                </div>
                <div class="pipeline-item expired-item">
                    <span class="pipeline-count">{{ number_format($expiredQuotations) }}</span>
                    <span class="pipeline-label">Expired</span>
                </div>
                <div class="pipeline-item converted-item">
                    <span class="pipeline-count">{{ number_format($convertedQuotations) }}</span>
                    <span class="pipeline-label">Converted</span>
                </div>
            </div>

            {{-- Conversion Rate Bar --}}
            @php
                $conversionRate = $totalQuotations > 0 ? round(($convertedQuotations / $totalQuotations) * 100, 1) : 0;
            @endphp
            <div class="conversion-rate-bar">
                <span class="conversion-rate-label">Conversion Rate</span>
                <div class="rate-track">
                    <div class="rate-fill" style="width: {{ $conversionRate }}%"></div>
                </div>
                <span class="rate-value">{{ $conversionRate }}%</span>
            </div>
        </section>

        {{-- QUOTATION TABLE --}}
        <section class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Quotation Number</th>
                            <th>Customer</th>
                            <th>Company</th>
                            <th>Quotation Date</th>
                            <th>Valid Until</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Converted Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $quotation)
                            @php
                                $statusClass = strtolower($quotation->status);
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $quotation->quotation_number }}</strong>
                                </td>
                                <td>
                                    {{ $quotation->customer?->business_name ?: ($quotation->customer?->customer_name ?: '-') }}
                                </td>
                                <td>
                                    {{ $quotation->company?->name ?: '-' }}
                                </td>
                                <td>
                                    {{ $quotation->quotation_date ? \Carbon\Carbon::parse($quotation->quotation_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    {{ $quotation->expiry_date ? \Carbon\Carbon::parse($quotation->expiry_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    <strong>{{ $currency }} {{ number_format($quotation->grand_total, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="quotation-status-badge {{ $statusClass }}">
                                        {{ ucfirst(strtolower($quotation->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($quotation->invoice)
                                        <span style="color: #2563eb; font-weight: 600;">
                                            {{ $quotation->invoice->invoice_number }}
                                        </span>
                                    @else
                                        <span style="color: #94a3b8;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <i data-lucide="file-x2"></i>
                                    <p>No quotations found matching the specified criteria</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($quotations->hasPages())
                <div class="table-footer">
                    <span>
                        Showing {{ $quotations->firstItem() ?? 0 }}
                        to {{ $quotations->lastItem() ?? 0 }}
                        of {{ $quotations->total() }} quotations
                    </span>

                    {{ $quotations->links() }}
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

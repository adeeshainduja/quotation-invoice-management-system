<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/invoices.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

@include('partials.sidebar')

<div class="page">

    @include('partials.topbar')

    <main>
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span>›</span>
            <span>Invoices</span>
        </div>

        <div class="page-head">
            <div>
                <h1>Invoices</h1>
                <p>Manage invoices and customer payments</p>
            </div>

            <a href="{{ route('invoices.create', ['company_id' => $companyId]) }}" class="new-btn">
                <i data-lucide="plus"></i>
                New Invoice
            </a>
        </div>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i data-lucide="file-text"></i></div>
                <div>
                    <h4>This Month Invoices</h4>
                    <strong>{{ $stats['month_count'] ?? 0 }}</strong>
                    <span>{{ $companyCurrency }} {{ number_format($stats['month_total'] ?? 0, 2) }}</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green"><i data-lucide="badge-check"></i></div>
                <div>
                    <h4>Payment Completed</h4>
                    <strong>{{ $stats['paid'] ?? 0 }}</strong>
                    <span>Fully paid invoices</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange"><i data-lucide="clock-3"></i></div>
                <div>
                    <h4>Payment Pending</h4>
                    <strong>{{ $stats['unpaid'] ?? 0 }}</strong>
                    <span>{{ $companyCurrency }} {{ number_format($stats['outstanding'] ?? 0, 2) }} outstanding</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple"><i data-lucide="send"></i></div>
                <div>
                    <h4>To Send This Month</h4>
                    <strong>{{ $stats['to_send'] ?? 0 }}</strong>
                    <span>Draft invoices</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red"><i data-lucide="alert-circle"></i></div>
                <div>
                    <h4>Overdue</h4>
                    <strong>{{ $stats['overdue'] ?? 0 }}</strong>
                    <span>Need attention</span>
                </div>
            </div>

            <div class="stat-card highlight">
                <div>
                    <h4>Outstanding Amount</h4>
                    <strong>{{ $companyCurrency }} {{ number_format($stats['outstanding'] ?? 0, 2) }}</strong>
                    <span>Total balance pending</span>
                </div>
            </div>
        </section>

        <section class="table-card">
            <form method="GET" action="{{ route('invoices.index') }}" class="filter-bar">
                <input type="hidden" name="company_id" value="{{ $companyId }}">

                <div class="search-box">
                    <i data-lucide="search"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search invoice or customer"
                    >
                </div>

                <select name="status">
                    <option value="">All Status</option>
                    <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="SENT" {{ request('status') == 'SENT' ? 'selected' : '' }}>Sent</option>
                    <option value="PARTIAL" {{ request('status') == 'PARTIAL' ? 'selected' : '' }}>Partial</option>
                    <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Paid</option>
                    <option value="OVERDUE" {{ request('status') == 'OVERDUE' ? 'selected' : '' }}>Overdue</option>
                </select>

                <select name="customer_id">
                    <option value="">All Customers</option>
                    @foreach($customerList as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->business_name }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="from_date" value="{{ request('from_date') }}">
                <input type="date" name="to_date" value="{{ request('to_date') }}">

                <button type="submit" class="search-btn">Search</button>

                <a href="{{ route('invoices.index', ['company_id' => $companyId]) }}" class="clear-btn">
                    Clear
                </a>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoices->firstItem() + $loop->index }}</td>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->business_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                            <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</td>
                            <td>{{ $companyCurrency }} {{ number_format($invoice->grand_total, 2) }}</td>
                            <td>{{ $companyCurrency }} {{ number_format($invoice->amount_paid, 2) }}</td>
                            <td>{{ $companyCurrency }} {{ number_format($invoice->balance_amount, 2) }}</td>
                            <td>
                                <span class="status {{ strtolower($invoice->status) }}">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    @if(Route::has('invoices.show'))
                                        <a href="{{ route('invoices.show', $invoice->id) }}">View</a>
                                    @endif

                                    @if(Route::has('invoices.pdf'))
                                        <a href="{{ route('invoices.pdf', $invoice->id) }}" target="_blank">PDF</a>
                                    @endif

                                    @if(Route::has('payments.create'))
                                        <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}">Pay</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="empty-state">
                                <i data-lucide="file-x2"></i>
                                <p>No invoices found</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>
                    Showing {{ $invoices->firstItem() ?? 0 }}
                    to {{ $invoices->lastItem() ?? 0 }}
                    of {{ $invoices->total() }} invoices
                </span>

                {{ $invoices->links() }}
            </div>
        </section>
    </main>
</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>
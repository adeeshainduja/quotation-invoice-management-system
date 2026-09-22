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
        .report-action {
            margin-top:20px;
            padding-top:16px;
            border-top:1px solid #eef2f7;
        }


        .view-report-btn {

            height:42px;

            display:flex;
            align-items:center;
            justify-content:center;

            gap:8px;

            width:100%;

            background:#2563eb;

            color:white;

            text-decoration:none;

            border-radius:8px;

            font-size:14px;

            font-weight:600;

            transition:.25s ease;
        }


        .view-report-btn:hover {

            background:#1d4ed8;

            transform:translateY(-2px);

        }


        .view-report-btn svg {

            width:17px;
            height:17px;

        }
    </style>
</head>

<body>

@include('partials.sidebar')

<div class="page content-wrapper">

    @include('partials.topbar')

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


        {{-- REPORT CATEGORIES --}}
<div class="section-header">
    <h2>Report Categories</h2>
    <p>Future-ready reporting sections and detailed analytical views</p>
</div>


<section class="reports-grid">


    {{-- Sales Reports --}}
    <!-- <div class="report-category-card">

        <div class="category-head">

            <div class="category-icon blue">
                <i data-lucide="trending-up"></i>
            </div>

            <div class="category-title">
                <h3>Sales Reports</h3>
                <p>
                    Sales trends, revenue analysis,
                    and business performance metrics.
                </p>
            </div>

        </div>


        <div class="report-action">

            <a href="{{ route('reports.quotations', $companyId ? ['company_id'=>$companyId] : []) }}"
               class="view-report-btn">

                <span>View Sales Report</span>

                <i data-lucide="arrow-right"></i>

            </a>

        </div>

    </div>
-->


    {{-- Invoice Reports --}}
    <div class="report-category-card">

        <div class="category-head">

            <div class="category-icon yellow">
                <i data-lucide="receipt-text"></i>
            </div>

            <div class="category-title">

                <h3>Invoice Reports</h3>

                <p>
                    Invoice lifecycle, overdue tracking,
                    and payment status analysis.
                </p>

            </div>

        </div>


        <div class="report-action">

            <a href="{{ route('reports.invoices', $companyId ? ['company_id'=>$companyId] : []) }}"
               class="view-report-btn">

                <span>View Invoice Report</span>

                <i data-lucide="arrow-right"></i>

            </a>

        </div>

    </div>




    {{-- Payment Reports --}}
    <div class="report-category-card">

        <div class="category-head">

            <div class="category-icon green">
                <i data-lucide="credit-card"></i>
            </div>

            <div class="category-title">

                <h3>Payment Reports</h3>

                <p>
                    Payment history, collections,
                    and transaction analysis.
                </p>

            </div>

        </div>


        <div class="report-action">

            <a href="{{ route('reports.payments', $companyId ? ['company_id'=>$companyId] : []) }}"
               class="view-report-btn">

                <span>View Payment Report</span>

                <i data-lucide="arrow-right"></i>

            </a>

        </div>

    </div>




    {{-- Customer Reports --}}
    <div class="report-category-card">

        <div class="category-head">

            <div class="category-icon purple">
                <i data-lucide="users"></i>
            </div>


            <div class="category-title">

                <h3>Customer Reports</h3>

                <p>
                    Customer revenue, balances,
                    and customer activity.
                </p>

            </div>

        </div>


        <div class="report-action">

            <a href="{{ route('reports.customers', $companyId ? ['company_id'=>$companyId] : []) }}"
               class="view-report-btn">

                <span>View Customer Report</span>

                <i data-lucide="arrow-right"></i>

            </a>

        </div>

    </div>





    {{-- Quotation Reports --}}
    <div class="report-category-card">

        <div class="category-head">

            <div class="category-icon blue">
                <i data-lucide="file-text"></i>
            </div>


            <div class="category-title">

                <h3>Quotation Reports</h3>

                <p>
                    Conversion rates, quotation status,
                    and sales pipeline.
                </p>

            </div>

        </div>


        <div class="report-action">

            <a href="{{ route('reports.quotations', $companyId ? ['company_id'=>$companyId] : []) }}"
               class="view-report-btn">

                <span>View Quotation Report</span>

                <i data-lucide="arrow-right"></i>

            </a>

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

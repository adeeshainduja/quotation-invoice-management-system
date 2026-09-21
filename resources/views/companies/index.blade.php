<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Companies</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/companies.css') }}">

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

        <a href="{{ route('companies.index') }}" class="active">
            <i data-lucide="building-2"></i>
            Companies
        </a>

        <a href="{{ route('customers.index') }}">
            <i data-lucide="users"></i>
            Customers
        </a>

        <a href="{{ route('quotations.index') }}">
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

            <select class="company-select">
                <option>All Companies</option>

                @foreach($companyList as $item)
                    <option>{{ $item->name }}</option>
                @endforeach
            </select>

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

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="logout">
                        <i data-lucide="log-out"></i>
                    </button>
                </form>

            </div>

        </div>

    </header>


    <main>

        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span>›</span>
            Companies
        </div>


        <div class="company-heading">

            <div>
                <h1>Companies</h1>
                <p>Manage your companies and business information</p>
            </div>

            <a href="{{ route('companies.create') }}" class="add-company">
                <i data-lucide="plus"></i>
                Add Company
            </a>

        </div>


        <section class="company-stats">

            <div class="company-stat">
                <div class="stat-icon blue">
                    <i data-lucide="building-2"></i>
                </div>

                <div>
                    <strong>{{ $totalCompanies }}</strong>
                    <p>Total Companies</p>
                </div>
            </div>


            <div class="company-stat">
                <div class="stat-icon green">
                    <i data-lucide="circle-check"></i>
                </div>

                <div>
                    <strong>{{ $activeCompanies }}</strong>
                    <p>Active Companies</p>
                </div>
            </div>


            <div class="company-stat">
                <div class="stat-icon red">
                    <i data-lucide="circle-pause"></i>
                </div>

                <div>
                    <strong>{{ $inactiveCompanies }}</strong>
                    <p>Inactive Companies</p>
                </div>
            </div>


            <div class="company-stat">
                <div class="stat-icon purple">
                    <i data-lucide="users"></i>
                </div>

                <div>
                    <strong>{{ $totalUsers }}</strong>
                    <p>Total Users</p>
                </div>
            </div>

        </section>


        <form method="GET"
              action="{{ route('companies.index') }}"
              class="company-filter">

            <div class="search-input">
                <i data-lucide="search"></i>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search company name, registration number, TIN..."
                >
            </div>


            <div>
                <label>Status</label>

                <select name="status">
                    <option value="">All Status</option>

                    <option value="ACTIVE"
                        {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="INACTIVE"
                        {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>


            <div>
                <label>Sort by</label>

                <select name="sort">

                    <option value="newest">
                        Newest First
                    </option>

                    <option value="oldest"
                        {{ request('sort') === 'oldest' ? 'selected' : '' }}>
                        Oldest First
                    </option>

                </select>
            </div>


            <button type="submit" class="search-button">
                <i data-lucide="search"></i>
                Search
            </button>

        </form>


        <section class="company-table-card">

            <div class="table-wrap">

                <table>

                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Company Name</th>
                        <th>Registration No.</th>
                        <th>TIN</th>
                        <th>VAT No.</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($companies as $company)

                        <tr>

                            <td>
                                {{ $companies->firstItem() + $loop->index }}
                            </td>


                            <td>

                                <div class="company-name">

                                    <div class="company-avatar">
                                        {{ strtoupper(substr($company->name, 0, 2)) }}
                                    </div>

                                    <strong>
                                        {{ $company->name }}
                                    </strong>

                                </div>

                            </td>


                            <td>
                                {{ $company->registration_number }}
                            </td>


                            <td>
                                {{ $company->tin ?: '-' }}
                            </td>


                            <td>
                                {{ $company->vat_number ?: '-' }}
                            </td>


                            <td>

                                <span class="company-status
                                    {{ strtolower($company->status) }}">

                                    {{ ucfirst(strtolower($company->status)) }}

                                </span>

                            </td>


                            <td>
                                {{ \Carbon\Carbon::parse($company->created_at)->format('d M Y') }}
                            </td>


                            <td>

                                <div class="actions">

                                    <a href="#">
                                        <i data-lucide="eye"></i>
                                        View
                                    </a>

                                    <a href="#">
                                        <i data-lucide="pencil"></i>
                                        Edit
                                    </a>

                                    <button type="button">
                                        <i data-lucide="more-vertical"></i>
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="empty">
                                No companies found.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            <div class="table-footer">

                <span>
                    Showing
                    {{ $companies->firstItem() ?? 0 }}
                    to
                    {{ $companies->lastItem() ?? 0 }}
                    of
                    {{ $companies->total() }}
                    companies
                </span>

                {{ $companies->links() }}

            </div>

        </section>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>
</html>

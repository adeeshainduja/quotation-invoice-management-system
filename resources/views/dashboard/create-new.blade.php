<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create New</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/create-new.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

@include('partials.sidebar')

<div class="page">

    <main>

        <div class="heading">
            <div>
                <h1>Create New</h1>
                <p>Select what you want to create</p>
            </div>
        </div>

        <div class="create-grid">

            <a href="{{ route('customers.create') }}" class="create-card">
                <i data-lucide="user-plus"></i>
                <strong>Customer</strong>
                <span>Add a new customer</span>
            </a>

            <a href="{{ route('quotations.create') }}" class="create-card">
                <i data-lucide="file-text"></i>
                <strong>Quotation</strong>
                <span>Create a quotation</span>
            </a>

            <a href="{{ route('invoices.create') }}" class="create-card">
                <i data-lucide="receipt-text"></i>
                <strong>Invoice</strong>
                <span>Create an invoice</span>
            </a>

            <a href="{{ route('templates.create') }}" class="create-card">
                <i data-lucide="notebook-tabs"></i>
                <strong>Template</strong>
                <span>Create a template</span>
            </a>

            <a href="{{ route('companies.create') }}" class="create-card">
                <i data-lucide="building-2"></i>
                <strong>Company</strong>
                <span>Add a company</span>
            </a>

        </div>

    </main>

</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>
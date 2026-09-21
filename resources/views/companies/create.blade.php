<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Company</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer-create.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<aside class="sidebar"><div class="logo"><i data-lucide="file-text"></i><div><strong>Quotation & Invoice</strong><small>Management System</small></div></div><nav>
    <a href="{{ route('dashboard') }}"><i data-lucide="house"></i>Dashboard</a>
    <a class="active" href="{{ route('companies.index') }}"><i data-lucide="building-2"></i>Companies</a>
    <a href="{{ route('customers.index') }}"><i data-lucide="users"></i>Customers</a>
    <a href="{{ route('quotations.index') }}"><i data-lucide="file-text"></i>Quotations</a>
</nav></aside>
<div class="page"><main>
    <div class="breadcrumb"><a href="{{ route('dashboard') }}">Home</a><span>›</span><a href="{{ route('companies.index') }}">Companies</a><span>›</span>Add Company</div>
    <div class="create-heading"><div><h1>Add New Company</h1><p>Enter the business information used for quotations and invoices.</p></div><a href="{{ route('companies.index') }}" class="back-btn"><i data-lucide="arrow-left"></i>Back to Companies</a></div>
    @if ($errors->any())<div class="form-errors">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('companies.store') }}" enctype="multipart/form-data">@csrf
        <div class="form-grid">
            <section class="form-card"><h2><i data-lucide="building-2"></i>Company Information</h2>
                <label>Company Name <span>*</span></label><input name="name" value="{{ old('name') }}" required>
                <label>Registration Number <span>*</span></label><input name="registration_number" value="{{ old('registration_number') }}" required>
                <label>TIN</label><input name="tin" value="{{ old('tin') }}">
                <label>Website</label><input type="url" name="website" value="{{ old('website') }}">
            </section>
            <section class="form-card"><h2><i data-lucide="phone"></i>Contact Information</h2>
                <label>Email <span>*</span></label><input type="email" name="email" value="{{ old('email') }}" required>
                <label>Phone <span>*</span></label><input name="phone" value="{{ old('phone') }}" required>
                <label>Logo</label><input type="file" name="logo_path" accept="image/*">
                <label>Signature</label><input type="file" name="signature_path" accept="image/*">
                <label>Stamp</label><input type="file" name="stamp_path" accept="image/*">
            </section>
            <section class="form-card"><h2><i data-lucide="map-pin"></i>Address</h2>
                <label>Address Line 1 <span>*</span></label><input name="address_line_1" value="{{ old('address_line_1') }}" required>
                <label>Address Line 2</label><input name="address_line_2" value="{{ old('address_line_2') }}">
                <label>City <span>*</span></label><input name="city" value="{{ old('city') }}" required>
                <label>Country <span>*</span></label><input name="country" value="{{ old('country', 'Sri Lanka') }}" required>
            </section>
            <section class="form-card"><h2><i data-lucide="receipt-text"></i>Tax & Numbering</h2>
                <label><input type="checkbox" name="vat_registered" value="1" {{ old('vat_registered') ? 'checked' : '' }}> VAT registered</label>
                <label>VAT Number</label><input name="vat_number" value="{{ old('vat_number') }}">
                <label>VAT Percentage</label><input type="number" step="0.01" min="0" max="100" name="vat_percentage" value="{{ old('vat_percentage') }}">
                <label>Quotation Prefix <span>*</span></label><input name="quotation_prefix" value="{{ old('quotation_prefix', 'QUO') }}" required>
                <label>Next Quotation Number</label><input type="number" min="1" name="quotation_next_number" value="{{ old('quotation_next_number', 1) }}">
                <label>Invoice Prefix <span>*</span></label><input name="invoice_prefix" value="{{ old('invoice_prefix', 'INV') }}" required>
                <label>Next Invoice Number</label><input type="number" min="1" name="invoice_next_number" value="{{ old('invoice_next_number', 1) }}">
                <label>Currency <span>*</span></label><input name="currency" value="{{ old('currency', 'LKR') }}" required>
                <label>Status</label><select name="status"><option value="ACTIVE" {{ old('status', 'ACTIVE') === 'ACTIVE' ? 'selected' : '' }}>Active</option><option value="INACTIVE" {{ old('status') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option></select>
            </section>
        </div>
        <div class="form-actions"><a href="{{ route('companies.index') }}" class="cancel-btn">Cancel</a><button class="save-btn" type="submit"><i data-lucide="save"></i>Save Company</button></div>
    </form>
</main></div><script>lucide.createIcons();</script></body></html>

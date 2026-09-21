<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>New Quotation</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quotation-create.css') }}">

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

        <a href="{{ route('dashboard', ['company_id' => $companyId]) }}">
            <i data-lucide="house"></i>
            Dashboard
        </a>

        <a href="{{ route('companies.index') }}">
            <i data-lucide="building-2"></i>
            Companies
        </a>

        <a href="{{ route('customers.index', ['company_id' => $companyId]) }}">
            <i data-lucide="users"></i>
            Customers
        </a>

        <a href="{{ route('quotations.index') }}" class="active">
            <i data-lucide="file-text"></i>
            Quotations
        </a>

        <a href="#">
            <i data-lucide="receipt-text"></i>
            Invoices
        </a>

        <a href="#">
            <i data-lucide="wallet"></i>
            Payments
        </a>

        <a href="#">
            <i data-lucide="files"></i>
            Templates
        </a>

        <a href="#">
            <i data-lucide="bar-chart-3"></i>
            Reports
        </a>

        <a href="#">
            <i data-lucide="settings"></i>
            Settings
        </a>

    </nav>

</aside>


<div class="page">

    <header class="topbar">

        <form method="GET" action="{{ route('quotations.create') }}">

            <select
                name="company_id"
                class="company-select"
                onchange="this.form.submit()"
            >

                <option value="">Select Company</option>

                @foreach($companyList as $item)

                    <option
                        value="{{ $item->id }}"
                        {{ $companyId == $item->id ? 'selected' : '' }}
                    >
                        {{ $item->name }}
                    </option>

                @endforeach

            </select>

        </form>


        <div class="topbar-right">

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

            </div>

        </div>

    </header>


    <main>

        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span>›</span>

            <a href="{{ route('quotations.index', ['company_id' => $companyId]) }}">
                Quotations
            </a>

            <span>›</span>
            New Quotation
        </div>


        <div class="create-heading">

            <div>
                <h1>New Quotation</h1>
                <p>Create a new quotation for your customer</p>
            </div>

            <a
                href="{{ route('quotations.index', ['company_id' => $companyId]) }}"
                class="back-btn"
            >
                <i data-lucide="arrow-left"></i>
                Back to Quotations
            </a>

        </div>


        @if ($errors->any())
            <div class="form-errors">
                {{ $errors->first() }}
            </div>
        @endif

        @if($companyList->isEmpty())
            <div class="form-errors">No active company exists. <a href="{{ route('companies.create') }}">Add Company</a></div>
        @elseif(! $company)
            <div class="form-errors">Select an active company before creating a quotation.</div>
        @endif


        <form
            action="{{ route('quotations.store') }}"
            method="POST"
            id="quotationForm"
        >

            @csrf

            <input
                type="hidden"
                name="company_id"
                value="{{ $companyId }}"
            >


            <div class="top-grid">

                {{-- CUSTOMER --}}
                <section class="form-card">

                    <h2>
                        <i data-lucide="user"></i>
                        Customer Information
                    </h2>

                    <label>Select Customer *</label>

                    <div class="customer-select-row">

                        <select
                            name="customer_id"
                            id="customerSelect"
                            required
                        >

                            <option value="">
                                Search and select a customer...
                            </option>

                            @foreach($customers as $customer)

                                <option
                                    value="{{ $customer->id }}"
                                    data-business="{{ $customer->business_name }}"
                                    data-name="{{ $customer->customer_name }}"
                                    data-address="{{ $customer->address_line_1 }}"
                                    data-city="{{ $customer->city }}"
                                    data-phone="{{ $customer->phone }}"
                                    data-email="{{ $customer->email }}"
                                    {{ old('customer_id') == $customer->id ? 'selected' : '' }}
                                >
                                    {{ $customer->business_name }}
                                </option>

                            @endforeach

                        </select>


                        <a
                            href="{{ route('customers.create', ['company_id' => $companyId]) }}"
                            class="add-customer-btn"
                        >
                            <i data-lucide="plus"></i>
                            Add New
                        </a>

                    </div>


                    <div class="customer-preview" id="customerPreview">

                        <strong id="previewBusiness">
                            Select a customer
                        </strong>

                        <span id="previewName"></span>
                        <span id="previewAddress"></span>
                        <span id="previewContact"></span>

                    </div>

                </section>


                {{-- DETAILS --}}
                <section class="form-card">

                    <h2>
                        <i data-lucide="file-text"></i>
                        Quotation Details
                    </h2>

                    <div class="two-columns">

                        <div>
                            <label>Quotation Number *</label>

                            <input
                                type="text"
                                value="{{ $quotationNumber }}"
                                readonly
                            >
                        </div>


                        <div>
                            <label>Template *</label>

                            <select name="template_id" required>

                                <option value="">Select Template</option>

                                @foreach($templates as $template)

                                    <option
                                        value="{{ $template->id }}"
                                        {{ old('template_id') == $template->id ? 'selected' : '' }}
                                    >
                                        {{ $template->template_name }}
                                    </option>

                                @endforeach

                            </select>
                        </div>


                        <div>
                            <label>Quotation Date *</label>

                            <input
                                type="date"
                                name="quotation_date"
                                value="{{ old('quotation_date', now()->format('Y-m-d')) }}"
                                required
                            >
                        </div>


                        <div>
                            <label>Currency *</label>

                            <input
                                type="text"
                                value="{{ $company->currency ?? 'LKR' }}"
                                readonly
                            >
                        </div>


                        <div>
                            <label>Valid Until *</label>

                            <input
                                type="date"
                                name="expiry_date"
                                value="{{ old('expiry_date', now()->addDays(30)->format('Y-m-d')) }}"
                                required
                            >
                        </div>


                        <div>
                            <label>Reference No.</label>

                            <input
                                type="text"
                                name="reference"
                                value="{{ old('reference') }}"
                                placeholder="Enter reference number (optional)"
                            >
                        </div>

                    </div>

                </section>

            </div>



            {{-- ITEMS --}}
            <section class="items-card">

                <div class="items-header">

                    <h2>
                        <i data-lucide="file-text"></i>
                        Quotation Items
                    </h2>

                    <button
                        type="button"
                        class="add-item-btn"
                        onclick="addItem()"
                    >
                        <i data-lucide="plus"></i>
                        Add Item
                    </button>

                </div>


                <div class="table-wrap">

                    <table id="itemsTable">

                        <thead>

                        <tr>
                            <th>#</th>
                            <th>Item / Description *</th>
                            <th>Quantity *</th>
                            <th>Unit Price *</th>
                            <th>Discount (%)</th>
                            <th>Tax (%)</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>

                        </thead>


                        <tbody id="itemsBody">

                        <tr class="item-row">

                            <td class="row-number">1</td>

                            <td>

                                <input
                                    type="text"
                                    name="items[0][item_name]"
                                    placeholder="Item name"
                                    required
                                >

                                <input
                                    type="text"
                                    name="items[0][description]"
                                    placeholder="Description"
                                    class="description"
                                >

                            </td>


                            <td>
                                <input
                                    type="number"
                                    name="items[0][quantity]"
                                    value="1"
                                    min="0.01"
                                    step="0.01"
                                    class="calc quantity"
                                    required
                                >
                            </td>


                            <td>
                                <input
                                    type="number"
                                    name="items[0][unit_price]"
                                    value="0"
                                    min="0"
                                    step="0.01"
                                    class="calc unit-price"
                                    required
                                >
                            </td>


                            <td>
                                <input
                                    type="number"
                                    name="items[0][discount]"
                                    value="0"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    class="calc discount"
                                >
                            </td>


                            <td>
                                <input
                                    type="number"
                                    name="items[0][tax]"
                                    value="{{ $company && $company->vat_registered ? ($company->vat_percentage ?? 0) : 0 }}"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    class="calc tax"
                                >
                            </td>


                            <td class="line-total">
                                0.00
                            </td>


                            <td>

                                <button
                                    type="button"
                                    class="delete-item"
                                    onclick="removeItem(this)"
                                >
                                    <i data-lucide="trash-2"></i>
                                </button>

                            </td>

                        </tr>

                        </tbody>

                    </table>

                </div>

            </section>



            <div class="bottom-grid">

                <section class="form-card">

                    <h2>
                        <i data-lucide="notebook"></i>
                        Additional Information
                    </h2>


                    <div class="two-columns">

                        <div>
                            <label>Terms & Conditions</label>

                            <textarea
                                name="terms_conditions"
                                placeholder="Enter terms and conditions"
                            >{{ old('terms_conditions') }}</textarea>
                        </div>


                        <div>
                            <label>Notes</label>

                            <textarea
                                name="notes"
                                placeholder="Enter any additional notes..."
                            >{{ old('notes') }}</textarea>
                        </div>

                    </div>

                </section>



                <section class="summary-card">

                    <h2>
                        <i data-lucide="calculator"></i>
                        Summary
                    </h2>


                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>
                            {{ $company->currency ?? 'LKR' }}
                            <span id="subtotal">0.00</span>
                        </strong>
                    </div>


                    <div class="summary-row">
                        <span>Discount</span>
                        <strong>
                            {{ $company->currency ?? 'LKR' }}
                            <span id="discountTotal">0.00</span>
                        </strong>
                    </div>


                    <div class="summary-row">
                        <span>Tax</span>
                        <strong>
                            {{ $company->currency ?? 'LKR' }}
                            <span id="taxTotal">0.00</span>
                        </strong>
                    </div>


                    <div class="summary-total">

                        <span>Total Amount</span>

                        <strong>
                            {{ $company->currency ?? 'LKR' }}
                            <span id="grandTotal">0.00</span>
                        </strong>

                    </div>

                </section>

            </div>



            <div class="form-actions">

                <a
                    href="{{ route('quotations.index', ['company_id' => $companyId]) }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>


                <button type="submit" class="save-btn" @disabled(! $company)>

                    <i data-lucide="save"></i>
                    Save Quotation

                </button>

            </div>

        </form>

    </main>

</div>


<script>

let itemIndex = 1;

const defaultTax =
    {{ $company && $company->vat_registered ? ($company->vat_percentage ?? 0) : 0 }};


function addItem()
{
    const body = document.getElementById('itemsBody');

    const row = document.createElement('tr');

    row.className = 'item-row';

    row.innerHTML = `
        <td class="row-number">${itemIndex + 1}</td>

        <td>
            <input
                type="text"
                name="items[${itemIndex}][item_name]"
                placeholder="Item name"
                required
            >

            <input
                type="text"
                name="items[${itemIndex}][description]"
                placeholder="Description"
                class="description"
            >
        </td>

        <td>
            <input
                type="number"
                name="items[${itemIndex}][quantity]"
                value="1"
                min="0.01"
                step="0.01"
                class="calc quantity"
                required
            >
        </td>

        <td>
            <input
                type="number"
                name="items[${itemIndex}][unit_price]"
                value="0"
                min="0"
                step="0.01"
                class="calc unit-price"
                required
            >
        </td>

        <td>
            <input
                type="number"
                name="items[${itemIndex}][discount]"
                value="0"
                min="0"
                max="100"
                step="0.01"
                class="calc discount"
            >
        </td>

        <td>
            <input
                type="number"
                name="items[${itemIndex}][tax]"
                value="${defaultTax}"
                min="0"
                max="100"
                step="0.01"
                class="calc tax"
            >
        </td>

        <td class="line-total">0.00</td>

        <td>
            <button
                type="button"
                class="delete-item"
                onclick="removeItem(this)"
            >
                <i data-lucide="trash-2"></i>
            </button>
        </td>
    `;

    body.appendChild(row);

    itemIndex++;

    lucide.createIcons();
}


function removeItem(button)
{
    const rows =
        document.querySelectorAll('.item-row');

    if (rows.length === 1) {
        return;
    }

    button.closest('tr').remove();

    updateNumbers();
    calculateTotals();
}


function updateNumbers()
{
    document
        .querySelectorAll('.item-row')
        .forEach((row, index) => {

            row.querySelector('.row-number').textContent =
                index + 1;
        });
}


function calculateTotals()
{
    let subtotal = 0;
    let discountTotal = 0;
    let taxTotal = 0;
    let grandTotal = 0;

    document
        .querySelectorAll('.item-row')
        .forEach(row => {

            const quantity =
                parseFloat(
                    row.querySelector('.quantity').value
                ) || 0;

            const price =
                parseFloat(
                    row.querySelector('.unit-price').value
                ) || 0;

            const discount =
                parseFloat(
                    row.querySelector('.discount').value
                ) || 0;

            const tax =
                parseFloat(
                    row.querySelector('.tax').value
                ) || 0;

            const lineSubtotal =
                quantity * price;

            const discountAmount =
                lineSubtotal * discount / 100;

            const taxable =
                lineSubtotal - discountAmount;

            const taxAmount =
                taxable * tax / 100;

            const lineTotal =
                taxable + taxAmount;

            subtotal += lineSubtotal;
            discountTotal += discountAmount;
            taxTotal += taxAmount;
            grandTotal += lineTotal;

            row.querySelector('.line-total').textContent =
                lineTotal.toLocaleString(
                    undefined,
                    {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );
        });


    document.getElementById('subtotal').textContent =
        subtotal.toLocaleString(undefined, {
            minimumFractionDigits: 2
        });

    document.getElementById('discountTotal').textContent =
        discountTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2
        });

    document.getElementById('taxTotal').textContent =
        taxTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2
        });

    document.getElementById('grandTotal').textContent =
        grandTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2
        });
}


document.addEventListener('input', function(event) {

    if (event.target.classList.contains('calc')) {
        calculateTotals();
    }
});


const customerSelect =
    document.getElementById('customerSelect');


function updateCustomer()
{
    const selected =
        customerSelect.options[
            customerSelect.selectedIndex
        ];

    if (!selected.value) {
        return;
    }

    document.getElementById('previewBusiness').textContent =
        selected.dataset.business || '';

    document.getElementById('previewName').textContent =
        selected.dataset.name || '';

    document.getElementById('previewAddress').textContent =
        `${selected.dataset.address || ''}, ${selected.dataset.city || ''}`;

    document.getElementById('previewContact').textContent =
        `${selected.dataset.phone || ''} | ${selected.dataset.email || ''}`;
}


customerSelect.addEventListener(
    'change',
    updateCustomer
);


updateCustomer();
calculateTotals();
lucide.createIcons();

</script>

</body>
</html>

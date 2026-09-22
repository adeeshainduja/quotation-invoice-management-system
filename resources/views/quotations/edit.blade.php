<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Quotation - {{ $quotation->quotation_number }}</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quotation-create.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

{{-- SHARED SIDEBAR --}}
@include('partials.sidebar')

<div class="page">

    @include('partials.topbar')

    <main>

        {{-- BREADCRUMB --}}
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span>›</span>
            <a href="{{ route('quotations.index', ['company_id' => $quotation->company_id]) }}">Quotations</a>
            <span>›</span>
            <a href="{{ route('quotations.show', $quotation->id) }}">{{ $quotation->quotation_number }}</a>
            <span>›</span>
            Edit
        </div>

        {{-- PAGE HEADER --}}
        <div class="create-heading">
            <div>
                <h1>Edit Quotation</h1>
                <p>Update quotation details for your customer</p>
            </div>

            <a href="{{ route('quotations.show', $quotation->id) }}" class="back-btn">
                <i data-lucide="arrow-left"></i>
                Back to Details
            </a>
        </div>

        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())
            <div class="form-errors">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('error'))
            <div class="form-errors">
                {{ session('error') }}
            </div>
        @endif

        <form
            action="{{ route('quotations.update', $quotation->id) }}"
            method="POST"
            id="quotationForm"
        >
            @csrf
            @method('PUT')

            <input type="hidden" name="company_id" value="{{ $quotation->company_id }}">

            {{-- TOP SECTION --}}
            <div class="top-grid">

                {{-- CUSTOMER INFORMATION --}}
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
                            <option value="">Search and select a customer...</option>

                            @foreach($customers as $cust)
                                <option
                                    value="{{ $cust->id }}"
                                    data-business="{{ $cust->business_name }}"
                                    data-name="{{ $cust->customer_name }}"
                                    data-address="{{ $cust->address_line_1 }}"
                                    data-city="{{ $cust->city }}"
                                    data-phone="{{ $cust->phone }}"
                                    data-email="{{ $cust->email }}"
                                    {{ old('customer_id', $quotation->customer_id) == $cust->id ? 'selected' : '' }}
                                >
                                    {{ $cust->business_name }}
                                </option>
                            @endforeach
                        </select>

                        <a
                            href="{{ route('customers.create', ['company_id' => $quotation->company_id]) }}"
                            class="add-customer-btn"
                        >
                            <i data-lucide="plus"></i>
                            Add New
                        </a>
                    </div>

                    <div class="customer-preview" id="customerPreview">
                        <strong id="previewBusiness">Select a customer</strong>
                        <span id="previewName"></span>
                        <span id="previewAddress"></span>
                        <span id="previewContact"></span>
                    </div>
                </section>

                {{-- QUOTATION DETAILS --}}
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
                                value="{{ $quotation->quotation_number }}"
                                readonly
                            >
                        </div>

                        <div>
                            <label>VAT Status</label>
                            @php
                                $isVatEnabled = (bool) ($company && ($company->vat_enabled ?? $company->vat_registered));
                                $vatRate = $isVatEnabled ? ($company->tax_percentage ?? $company->vat_percentage ?? 0) : 0;
                            @endphp
                            @if($isVatEnabled)
                                <div style="display: flex; align-items: center; height: 38px; padding: 0 12px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 13px; font-weight: 600; gap: 6px;">
                                    <i data-lucide="check-circle" style="width: 16px; height: 16px; color: #059669;"></i>
                                    VAT Enabled ({{ $vatRate }}%)
                                </div>
                            @else
                                <div style="display: flex; align-items: center; height: 38px; padding: 0 12px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; color: #6b7280; font-size: 13px; font-weight: 500; gap: 6px;">
                                    <i data-lucide="x-circle" style="width: 16px; height: 16px; color: #9ca3af;"></i>
                                    VAT Disabled
                                </div>
                            @endif
                        </div>

                        <div>
                            <label>Template *</label>
                            @if($isVatEnabled)
                                @php
                                    $defaultTemplateId = old('template_id', $quotation->template_id)
                                        ?: optional($templates->firstWhere('template_name', 'Tax Quotation Template'))->id
                                        ?: optional($templates->firstWhere('is_default', 1))->id
                                        ?: optional($templates->first())->id;
                                @endphp
                                <input type="hidden" name="template_id" value="{{ $defaultTemplateId }}">
                                <select disabled class="form-control" style="background-color: #f3f4f6; cursor: not-allowed; color: #374151;">
                                    <option selected>Tax Quotation Template</option>
                                </select>
                                <p style="margin-top: 5px; font-size: 12px; color: #059669; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                                    <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                                    VAT registered company - Tax template automatically applied
                                </p>
                            @elseif(!auth()->user()->isAdmin())
                                @php
                                    $userAssignedTemplate = auth()->user()->getAssignedQuotationTemplate($quotation->company_id);
                                    $lockedTemplateId = old('template_id', $quotation->template_id)
                                        ?: optional($userAssignedTemplate)->id
                                        ?: optional($templates->firstWhere('is_default', 1))->id
                                        ?: optional($templates->first())->id;
                                    $lockedTemplateName = optional($templates->firstWhere('id', $lockedTemplateId))->template_name
                                        ?: optional($userAssignedTemplate)->template_name
                                        ?: 'Modern Quotation Template';
                                @endphp
                                <input type="hidden" name="template_id" value="{{ $lockedTemplateId }}">
                                <select disabled class="form-control" style="background-color: #f3f4f6; cursor: not-allowed; color: #374151;">
                                    <option selected>{{ $lockedTemplateName }} (Assigned)</option>
                                </select>
                                <p style="margin-top: 5px; font-size: 12px; color: #2563eb; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                                    <i data-lucide="lock" style="width: 14px; height: 14px;"></i>
                                    Template assigned by administrator
                                </p>
                            @else
                                <select
                                    name="template_id"
                                    required
                                >
                                    <option value="">Select Template</option>
                                    @foreach($templates as $tmpl)
                                        <option
                                            value="{{ $tmpl->id }}"
                                            {{ old('template_id', $quotation->template_id) == $tmpl->id ? 'selected' : '' }}
                                        >
                                            {{ $tmpl->template_name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div>
                            <label>Quotation Date *</label>
                            <input
                                type="date"
                                name="quotation_date"
                                value="{{ old('quotation_date', \Carbon\Carbon::parse($quotation->quotation_date)->format('Y-m-d')) }}"
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
                            <label>Valid Until</label>
                            <input
                                type="date"
                                name="expiry_date"
                                value="{{ old('expiry_date', $quotation->expiry_date ? \Carbon\Carbon::parse($quotation->expiry_date)->format('Y-m-d') : '') }}"
                            >
                        </div>

                        <div>
                            <label>Reference No.</label>
                            <input
                                type="text"
                                name="reference"
                                value="{{ old('reference', $quotation->reference) }}"
                                placeholder="Enter reference number (optional)"
                            >
                        </div>
                    </div>
                </section>
            </div>

            {{-- QUOTATION ITEMS --}}
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

                @php
                    $initialItems = old('items');
                    if (!$initialItems) {
                        $initialItems = $items->map(function ($it) {
                            return [
                                'item_name' => $it->item_name,
                                'description' => $it->description ?? '',
                                'quantity' => (float) $it->quantity,
                                'unit_price' => (float) $it->unit_price,
                                'discount' => (float) ($it->discount_value ?? 0),
                                'tax' => (float) ($it->tax_percentage ?? 0),
                            ];
                        })->toArray();
                    }
                    if (empty($initialItems)) {
                        $initialItems = [[
                            'item_name' => '',
                            'description' => '',
                            'quantity' => 1,
                            'unit_price' => 0,
                            'discount' => 0,
                            'tax' => $vatRate,
                        ]];
                    }
                @endphp

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
                        @foreach($initialItems as $idx => $it)
                            <tr class="item-row">
                                <td class="row-number">{{ $loop->iteration }}</td>

                                <td>
                                    <input
                                        type="text"
                                        name="items[{{ $idx }}][item_name]"
                                        value="{{ $it['item_name'] ?? '' }}"
                                        placeholder="Item name"
                                        required
                                    >
                                    <input
                                        type="text"
                                        name="items[{{ $idx }}][description]"
                                        value="{{ $it['description'] ?? '' }}"
                                        placeholder="Description"
                                        class="description"
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $idx }}][quantity]"
                                        value="{{ $it['quantity'] ?? 1 }}"
                                        min="0.01"
                                        step="0.01"
                                        class="calc quantity"
                                        required
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $idx }}][unit_price]"
                                        value="{{ $it['unit_price'] ?? 0 }}"
                                        min="0"
                                        step="0.01"
                                        class="calc unit-price"
                                        required
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $idx }}][discount]"
                                        value="{{ $it['discount'] ?? 0 }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        class="calc discount"
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $idx }}][tax]"
                                        value="{{ $it['tax'] ?? ($isVatEnabled ? $vatRate : 0) }}"
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
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- BOTTOM SECTION --}}
            <div class="bottom-grid">

                {{-- ADDITIONAL INFORMATION --}}
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
                            >{{ old('terms_conditions', $quotation->terms_conditions) }}</textarea>
                        </div>

                        <div>
                            <label>Notes</label>
                            <textarea
                                name="notes"
                                placeholder="Enter any additional notes..."
                            >{{ old('notes', $quotation->notes) }}</textarea>
                        </div>
                    </div>
                </section>

                {{-- SUMMARY --}}
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

                    @if($isVatEnabled)
                    <div class="summary-row">
                        <span>VAT Amount ({{ $vatRate }}%)</span>
                        <strong>
                            {{ $company->currency ?? 'LKR' }}
                            <span id="taxTotal">0.00</span>
                        </strong>
                    </div>
                    @else
                    <span id="taxTotal" style="display: none;">0.00</span>
                    @endif

                    <div class="summary-total">
                        <span>Total Amount</span>
                        <strong>
                            {{ $company->currency ?? 'LKR' }}
                            <span id="grandTotal">0.00</span>
                        </strong>
                    </div>
                </section>
            </div>

            {{-- FORM ACTIONS --}}
            <div class="form-actions">
                <a
                    href="{{ route('quotations.show', $quotation->id) }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    formaction="{{ route('quotations.preview') }}"
                    formmethod="POST"
                    formtarget="_blank"
                    class="save-btn"
                >
                    <i data-lucide="eye"></i>
                    Preview
                </button>

                <button
                    type="submit"
                    class="save-btn"
                >
                    <i data-lucide="save"></i>
                    Update Quotation
                </button>
            </div>
        </form>
    </main>
</div>

<script>
    let itemIndex = {{ count($initialItems) }};

    const defaultTax = @json(
        $company && ($company->vat_enabled ?? $company->vat_registered)
            ? (float) ($company->tax_percentage ?? $company->vat_percentage ?? 0)
            : 0
    );

    function addItem()
    {
        const body = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        row.className = 'item-row';

        row.innerHTML = `
            <td class="row-number">${body.children.length + 1}</td>
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
        calculateTotals();
    }

    function removeItem(button)
    {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length === 1) {
            return;
        }

        button.closest('tr').remove();
        updateNumbers();
        calculateTotals();
    }

    function updateNumbers()
    {
        document.querySelectorAll('.item-row').forEach((row, index) => {
            row.querySelector('.row-number').textContent = index + 1;
        });
    }

    function calculateTotals()
    {
        let subtotal = 0;
        let discountTotal = 0;
        let taxTotal = 0;
        let grandTotal = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
            const price = parseFloat(row.querySelector('.unit-price').value) || 0;
            const discount = parseFloat(row.querySelector('.discount').value) || 0;
            const tax = parseFloat(row.querySelector('.tax').value) || 0;

            const lineSubtotal = quantity * price;
            const discountAmount = lineSubtotal * discount / 100;
            const taxableAmount = lineSubtotal - discountAmount;
            const taxAmount = taxableAmount * tax / 100;
            const lineTotal = taxableAmount + taxAmount;

            subtotal += lineSubtotal;
            discountTotal += discountAmount;
            taxTotal += taxAmount;
            grandTotal += lineTotal;

            row.querySelector('.line-total').textContent = lineTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        });

        document.getElementById('subtotal').textContent = subtotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        document.getElementById('discountTotal').textContent = discountTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        const taxEl = document.getElementById('taxTotal');
        if (taxEl) {
            taxEl.textContent = taxTotal.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        document.getElementById('grandTotal').textContent = grandTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    document.addEventListener('input', function (event) {
        if (event.target.classList.contains('calc')) {
            calculateTotals();
        }
    });

    const customerSelect = document.getElementById('customerSelect');

    function updateCustomer()
    {
        if (!customerSelect) {
            return;
        }

        const selected = customerSelect.options[customerSelect.selectedIndex];

        if (!selected || !selected.value) {
            document.getElementById('previewBusiness').textContent = 'Select a customer';
            document.getElementById('previewName').textContent = '';
            document.getElementById('previewAddress').textContent = '';
            document.getElementById('previewContact').textContent = '';
            return;
        }

        document.getElementById('previewBusiness').textContent = selected.dataset.business || '';
        document.getElementById('previewName').textContent = selected.dataset.name || '';

        const addressParts = [selected.dataset.address, selected.dataset.city].filter(Boolean);
        document.getElementById('previewAddress').textContent = addressParts.join(', ');

        const contactParts = [selected.dataset.phone, selected.dataset.email].filter(Boolean);
        document.getElementById('previewContact').textContent = contactParts.join(' | ');
    }

    if (customerSelect) {
        customerSelect.addEventListener('change', updateCustomer);
    }

    updateCustomer();
    calculateTotals();
    lucide.createIcons();
</script>

</body>
</html>

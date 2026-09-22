<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Invoice - {{ $invoice->invoice_number }}</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/invoice-create.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

@include('partials.sidebar')

@php
    $currency = $company->currency ?? 'LKR';
    $isVatEnabled = (bool) ($company && ($company->vat_enabled ?? $company->vat_registered));

    $defaultTax = $isVatEnabled
        ? ($company->tax_percentage ?? $company->vat_percentage ?? 0)
        : 0;

    $oldItems = old('items');
    if (!$oldItems) {
        $oldItems = $items->map(function ($it) {
            return [
                'item_name' => $it->item_name,
                'description' => $it->description ?? '',
                'quantity' => (float) $it->quantity,
                'unit' => $it->unit ?? '',
                'unit_price' => (float) $it->unit_price,
                'discount_type' => $it->discount_type ?? 'NONE',
                'discount_value' => (float) ($it->discount_value ?? 0),
                'tax_percentage' => (float) ($it->tax_percentage ?? 0),
            ];
        })->toArray();
    }
    if (empty($oldItems)) {
        $oldItems = [[
            'item_name' => '',
            'description' => '',
            'quantity' => 1,
            'unit' => '',
            'unit_price' => 0,
            'discount_type' => 'NONE',
            'discount_value' => 0,
            'tax_percentage' => $defaultTax,
        ]];
    }
@endphp

<div class="page content-wrapper">

    @include('partials.topbar')

    <main class="invoice-container">

        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span>›</span>
            <a href="{{ route('invoices.index', ['company_id' => $invoice->company_id]) }}">Invoices</a>
            <span>›</span>
            <a href="{{ route('invoices.show', $invoice->id) }}">{{ $invoice->invoice_number }}</a>
            <span>›</span>
            Edit
        </div>

        <div class="page-heading">
            <div>
                <h1>Edit Invoice</h1>
                <p>Update invoice details and items</p>
            </div>

            <a
                href="{{ route('invoices.show', $invoice->id) }}"
                class="back-btn"
            >
                <i data-lucide="arrow-left"></i>
                Back to Details
            </a>
        </div>

        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-error">
                {{ session('error') }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('invoices.update', $invoice->id) }}"
            id="invoiceForm"
        >
            @csrf
            <input type="hidden" name="_method" value="PUT" id="invoiceFormMethod">
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
            <input type="hidden" name="invoice_number" value="{{ $invoice->invoice_number }}">
            <input type="hidden" name="company_id" value="{{ $invoice->company_id }}">

            <div class="top-grid invoice-header-grid">

                {{-- CUSTOMER --}}
                <section class="card">
                    <div class="card-title">
                        <i data-lucide="user"></i>
                        <div>
                            <h2>Customer Information</h2>
                            <p>Select customer</p>
                        </div>
                    </div>

                    <label>Customer *</label>
                    <select
                        name="customer_id"
                        id="customerSelect"
                        required
                    >
                        <option value="">Select Customer</option>
                        @foreach($customers as $cust)
                            <option
                                value="{{ $cust->id }}"
                                data-business="{{ $cust->business_name }}"
                                data-name="{{ $cust->customer_name }}"
                                data-phone="{{ $cust->phone }}"
                                data-email="{{ $cust->email }}"
                                data-address="{{ $cust->address_line_1 }}"
                                data-city="{{ $cust->city }}"
                                {{ old('customer_id', $invoice->customer_id) == $cust->id ? 'selected' : '' }}
                            >
                                {{ $cust->business_name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="customer-preview" id="customerPreview">
                        <strong id="previewBusiness">Select a customer</strong>
                        <span id="previewName"></span>
                        <span id="previewContact"></span>
                        <span id="previewAddress"></span>
                    </div>
                </section>

                {{-- INVOICE DETAILS --}}
                <section class="card">
                    <div class="card-title">
                        <i data-lucide="file-text"></i>
                        <div>
                            <h2>Invoice Details</h2>
                            <p>Dates and invoice settings</p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div>
                            <label>Invoice Number</label>
                            <input
                                type="text"
                                value="{{ $invoice->invoice_number }}"
                                readonly
                            >
                        </div>

                        <div>
                            <label>VAT Status</label>
                            @if($isVatEnabled)
                                <div style="display: flex; align-items: center; height: 38px; padding: 0 12px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 13px; font-weight: 600; gap: 6px;">
                                    <i data-lucide="check-circle" style="width: 16px; height: 16px; color: #059669;"></i>
                                    VAT Enabled ({{ $defaultTax }}%)
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
                                    $defaultTemplateId = old('template_id', $invoice->template_id)
                                        ?: optional($templates->firstWhere('template_name', 'Tax Invoice Template'))->id
                                        ?: optional($templates->firstWhere('is_default', 1))->id
                                        ?: optional($templates->first())->id;
                                @endphp
                                <input type="hidden" name="template_id" value="{{ $defaultTemplateId }}">
                                <select disabled class="form-control" style="background-color: #f3f4f6; cursor: not-allowed; color: #374151;">
                                    <option selected>Tax Invoice Template</option>
                                </select>
                                <p style="margin-top: 5px; font-size: 12px; color: #059669; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                                    <i data-lucide="info" style="width: 14px; height: 14px;"></i>
                                    VAT registered company - Tax template automatically applied
                                </p>
                            @elseif(!auth()->user()->isAdmin())
                                @php
                                    $userAssignedTemplate = auth()->user()->getAssignedInvoiceTemplate($invoice->company_id);
                                    $lockedTemplateId = old('template_id', $invoice->template_id)
                                        ?: optional($userAssignedTemplate)->id
                                        ?: optional($templates->firstWhere('is_default', 1))->id
                                        ?: optional($templates->first())->id;
                                    $lockedTemplateName = optional($templates->firstWhere('id', $lockedTemplateId))->template_name
                                        ?: optional($userAssignedTemplate)->template_name
                                        ?: 'Modern Invoice Template';
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
                                <select name="template_id" required>
                                    <option value="">Select Template</option>
                                    @foreach($templates as $tmpl)
                                        <option
                                            value="{{ $tmpl->id }}"
                                            {{ old('template_id', $invoice->template_id) == $tmpl->id ? 'selected' : '' }}
                                        >
                                            {{ $tmpl->template_name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div>
                            <label>Invoice Date *</label>
                            <input
                                type="date"
                                name="invoice_date"
                                value="{{ old('invoice_date', \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d')) }}"
                                required
                            >
                        </div>

                        <div>
                            <label>Due Date</label>
                            <input
                                type="date"
                                name="due_date"
                                value="{{ old('due_date', $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '') }}"
                            >
                        </div>

                        <div>
                            <label>Subject</label>
                            <input
                                type="text"
                                name="subject"
                                value="{{ old('subject', $invoice->subject) }}"
                                placeholder="Invoice subject (optional)"
                            >
                        </div>

                        <div>
                            <label>Reference</label>
                            <input
                                type="text"
                                name="reference"
                                value="{{ old('reference', $invoice->reference) }}"
                                placeholder="PO or project reference"
                            >
                        </div>
                    </div>
                </section>
            </div>

            {{-- INVOICE ITEMS --}}
            <section class="card items-section">
                <div class="items-head">
                    <div class="card-title">
                        <i data-lucide="layers"></i>
                        <div>
                            <h2>Invoice Items</h2>
                            <p>Items and pricing breakdown</p>
                        </div>
                    </div>

                    <button type="button" class="add-btn" onclick="addItem()">
                        <i data-lucide="plus"></i>
                        Add Item
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="invoice-items-table" id="itemsTable">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name / Description *</th>
                            <th>Quantity *</th>
                            <th>Unit</th>
                            <th>Unit Price ({{ $currency }}) *</th>
                            <th>Discount</th>
                            <th>VAT (%)</th>
                            <th>Total ({{ $currency }})</th>
                            <th>Action</th>
                        </tr>
                        </thead>

                        <tbody id="itemsBody">
                        @foreach($oldItems as $index => $item)
                            <tr class="item-row">
                                <td class="row-number">{{ $loop->iteration }}</td>

                                <td>
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][item_name]"
                                        value="{{ $item['item_name'] }}"
                                        placeholder="Item name"
                                        class="item-name"
                                        required
                                    >
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][description]"
                                        value="{{ $item['description'] }}"
                                        placeholder="Description (optional)"
                                        class="description"
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][quantity]"
                                        value="{{ $item['quantity'] }}"
                                        min="0.01"
                                        step="0.01"
                                        class="calc quantity"
                                        required
                                    >
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][unit]"
                                        value="{{ $item['unit'] }}"
                                        placeholder="hrs/pcs"
                                        class="unit unit-input"
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][unit_price]"
                                        value="{{ $item['unit_price'] }}"
                                        min="0"
                                        step="0.01"
                                        class="calc price unit-price"
                                        required
                                    >
                                </td>

                                <td>
                                    <div class="discount discount-box">
                                        <select
                                            name="items[{{ $index }}][discount_type]"
                                            class="calc discount-type"
                                        >
                                            <option value="NONE" {{ ($item['discount_type'] ?? 'NONE') === 'NONE' ? 'selected' : '' }}>None</option>
                                            <option value="PERCENTAGE" {{ ($item['discount_type'] ?? '') === 'PERCENTAGE' ? 'selected' : '' }}>%</option>
                                            <option value="FIXED" {{ ($item['discount_type'] ?? '') === 'FIXED' ? 'selected' : '' }}>Fixed</option>
                                        </select>

                                        <input
                                            type="number"
                                            name="items[{{ $index }}][discount_value]"
                                            value="{{ $item['discount_value'] ?? 0 }}"
                                            min="0"
                                            step="0.01"
                                            class="calc discount-value"
                                        >
                                    </div>
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][tax_percentage]"
                                        value="{{ $item['tax_percentage'] ?? ($isVatEnabled ? $defaultTax : 0) }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        class="calc vat tax"
                                    >
                                </td>

                                <td class="line-total total">0.00</td>

                                <td>
                                    <button
                                        type="button"
                                        class="remove-btn"
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

            <div class="bottom-grid summary-grid">
                {{-- NOTES & TERMS --}}
                <section class="card">
                    <div class="card-title">
                        <i data-lucide="align-left"></i>
                        <div>
                            <h2>Notes & Terms</h2>
                            <p>Customer notes and payment conditions</p>
                        </div>
                    </div>

                    <label>Notes</label>
                    <textarea
                        name="notes"
                        rows="3"
                        placeholder="Add special notes for the customer..."
                    >{{ old('notes', $invoice->notes) }}</textarea>

                    <label style="margin-top: 10px;">Terms & Conditions</label>
                    <textarea
                        name="terms_conditions"
                        rows="3"
                        placeholder="Payment terms, late fees, bank details..."
                    >{{ old('terms_conditions', $invoice->terms_conditions) }}</textarea>
                </section>

                {{-- TOTALS SUMMARY --}}
                <section class="card summary-card">
                    <div class="card-title">
                        <i data-lucide="calculator"></i>
                        <div>
                            <h2>Summary</h2>
                            <p>Invoice total calculation</p>
                        </div>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>{{ $currency }} <span id="subtotal">0.00</span></strong>
                    </div>

                    <div class="summary-row">
                        <span>Discount</span>
                        <strong>{{ $currency }} <span id="discountTotal">0.00</span></strong>
                    </div>

                    @if($isVatEnabled)
                    <div class="summary-row">
                        <span>VAT Total</span>
                        <strong>{{ $currency }} <span id="taxTotal">0.00</span></strong>
                    </div>
                    @else
                    <span id="taxTotal" style="display: none;">0.00</span>
                    @endif

                    <div class="summary-row">
                        <span>Additional Charges</span>
                        <input
                            type="number"
                            name="additional_charges"
                            id="additionalCharges"
                            value="{{ old('additional_charges', (float) $invoice->additional_charges) }}"
                            min="0"
                            step="0.01"
                            class="calc summary-input"
                        >
                    </div>

                    <div class="summary-row paid-row">
                        <span>Amount Paid</span>
                        <strong>{{ $currency }} <span id="amountPaid">{{ number_format((float) $invoice->amount_paid, 2) }}</span></strong>
                    </div>

                    <div class="grand-total">
                        <span>Grand Total</span>
                        <strong>{{ $currency }} <span id="grandTotal">{{ number_format((float) $invoice->grand_total, 2) }}</span></strong>
                    </div>

                    <div class="balance-total">
                        <span>Balance Due</span>
                        <strong>{{ $currency }} <span id="balanceAmount">{{ number_format((float) $invoice->balance_amount, 2) }}</span></strong>
                    </div>
                </section>
            </div>

            <div class="form-actions">
                <a
                    href="{{ route('invoices.show', $invoice->id) }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    formaction="{{ route('invoices.preview') }}"
                    formmethod="POST"
                    formtarget="_blank"
                    class="preview-btn"
                    id="previewBtn"
                    onclick="var m=document.getElementById('invoiceFormMethod'); if(m){ m.disabled=true; setTimeout(function(){ m.disabled=false; }, 1000); }"
                >
                    <i data-lucide="eye"></i>
                    Preview
                </button>

                <button
                    type="submit"
                    name="_method"
                    value="PUT"
                    class="save-btn"
                    id="saveBtn"
                    onclick="var m=document.getElementById('invoiceFormMethod'); if(m){ m.disabled=false; }"
                >
                    <i data-lucide="save"></i>
                    Update Invoice
                </button>
            </div>
        </form>

    </main>

</div>

<script>
let itemIndex = {{ count($oldItems) }};
const defaultTax = @json((float) $defaultTax);
const currentPaid = @json((float) ($invoice->amount_paid ?? 0));

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
                class="item-name"
                required
            >
            <input
                type="text"
                name="items[${itemIndex}][description]"
                placeholder="Description (optional)"
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
                type="text"
                name="items[${itemIndex}][unit]"
                placeholder="hrs/pcs"
                class="unit unit-input"
            >
        </td>
        <td>
            <input
                type="number"
                name="items[${itemIndex}][unit_price]"
                value="0"
                min="0"
                step="0.01"
                class="calc price unit-price"
                required
            >
        </td>
        <td>
            <div class="discount discount-box">
                <select
                    name="items[${itemIndex}][discount_type]"
                    class="calc discount-type"
                >
                    <option value="NONE">None</option>
                    <option value="PERCENTAGE">%</option>
                    <option value="FIXED">Fixed</option>
                </select>
                <input
                    type="number"
                    name="items[${itemIndex}][discount_value]"
                    value="0"
                    min="0"
                    step="0.01"
                    class="calc discount-value"
                >
            </div>
        </td>
        <td>
            <input
                type="number"
                name="items[${itemIndex}][tax_percentage]"
                value="${defaultTax}"
                min="0"
                max="100"
                step="0.01"
                class="calc vat tax"
            >
        </td>
        <td class="line-total total">0.00</td>
        <td>
            <button
                type="button"
                class="remove-btn"
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

    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.quantity').value) || 0;
        const price = parseFloat(row.querySelector('.unit-price').value) || 0;
        const discountType = row.querySelector('.discount-type').value;
        const discountVal = parseFloat(row.querySelector('.discount-value').value) || 0;
        const taxRate = parseFloat(row.querySelector('.tax').value) || 0;

        const lineSubtotal = qty * price;
        let lineDiscount = 0;

        if (discountType === 'PERCENTAGE') {
            lineDiscount = (lineSubtotal * discountVal) / 100;
        } else if (discountType === 'FIXED') {
            lineDiscount = discountVal;
        }

        lineDiscount = Math.min(lineSubtotal, lineDiscount);
        const taxable = lineSubtotal - lineDiscount;
        const lineTax = (taxable * taxRate) / 100;
        const lineTotal = taxable + lineTax;

        row.querySelector('.line-total').textContent = lineTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        subtotal += lineSubtotal;
        discountTotal += lineDiscount;
        taxTotal += lineTax;
    });

    const addCharges = parseFloat(document.getElementById('additionalCharges').value) || 0;
    const grandTotal = Math.max(0, subtotal - discountTotal + taxTotal + addCharges);
    const balance = Math.max(0, grandTotal - currentPaid);

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

    document.getElementById('balanceAmount').textContent = balance.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('calc')) {
        calculateTotals();
    }
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('calc')) {
        calculateTotals();
    }
});

const customerSelect = document.getElementById('customerSelect');

function updateCustomer()
{
    if (!customerSelect) return;
    const option = customerSelect.options[customerSelect.selectedIndex];
    if (!option || !option.value) {
        document.getElementById('previewBusiness').textContent = 'Select a customer';
        document.getElementById('previewName').textContent = '';
        document.getElementById('previewContact').textContent = '';
        document.getElementById('previewAddress').textContent = '';
        return;
    }

    document.getElementById('previewBusiness').textContent = option.dataset.business || '';
    document.getElementById('previewName').textContent = option.dataset.name || '';
    document.getElementById('previewContact').textContent = [option.dataset.phone, option.dataset.email].filter(Boolean).join(' | ');
    document.getElementById('previewAddress').textContent = [option.dataset.address, option.dataset.city].filter(Boolean).join(', ');
}

if (customerSelect) {
    customerSelect.addEventListener('change', updateCustomer);
}

updateCustomer();
calculateTotals();
lucide.createIcons();

const invoiceForm = document.getElementById('invoiceForm');
const invoiceMethod = document.getElementById('invoiceFormMethod');
if (invoiceForm && invoiceMethod) {
    invoiceForm.addEventListener('submit', function(e) {
        if (e.submitter && (e.submitter.id === 'previewBtn' || (e.submitter.getAttribute('formaction') && e.submitter.getAttribute('formaction').includes('preview')))) {
            invoiceMethod.disabled = true;
            setTimeout(function() {
                invoiceMethod.disabled = false;
            }, 1000);
        } else {
            invoiceMethod.disabled = false;
        }
    });
}
</script>

</body>
</html>

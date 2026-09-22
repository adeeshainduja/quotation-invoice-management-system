@php
    $isVatEnabled = isset($invoice->vat_enabled)
        ? (bool) $invoice->vat_enabled
        : ((float) ($invoice->vat_amount ?? $invoice->tax_amount ?? 0) > 0 || (bool) ($company->vat_enabled ?? $company->vat_registered ?? false));
@endphp
@if($isVatEnabled)
    @include('pdf.invoices.tax-invoice')
@else
    @include('pdf.invoices.normal')
@endif
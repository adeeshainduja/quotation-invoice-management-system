@php
    $isVatEnabled = isset($quotation->vat_enabled)
        ? (bool) $quotation->vat_enabled
        : ((float) ($quotation->vat_amount ?? $quotation->tax_amount ?? 0) > 0 || (bool) ($company->vat_enabled ?? $company->vat_registered ?? false));
@endphp
@if($isVatEnabled)
    @include('pdf.quotations.tax-quotation')
@else
    @include('pdf.quotations.normal')
@endif

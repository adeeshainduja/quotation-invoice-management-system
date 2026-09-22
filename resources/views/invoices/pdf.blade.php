@if((bool) ($company->vat_enabled ?? $company->vat_registered ?? false))
    @include('pdf.invoices.tax-invoice')
@else
    @include('pdf.invoices.normal')
@endif
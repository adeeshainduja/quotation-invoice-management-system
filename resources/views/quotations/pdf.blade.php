@if((bool) ($company->vat_enabled ?? $company->vat_registered ?? false))
    @include('pdf.quotations.tax-quotation')
@else
    @include('pdf.quotations.normal')
@endif

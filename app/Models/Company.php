<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registration_number',
        'address_line_1',
        'address_line_2',
        'city',
        'country',
        'phone',
        'email',
        'website',
        'logo_path',
        'signature_path',
        'stamp_path',
        'tin_number',
        'vat_enabled',
        'vat_registered',
        'vat_number',
        'vat_percentage',
        'tax_registration_number',
        'tax_percentage',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
        'swift_code',
        'quotation_prefix',
        'quotation_next_number',
        'invoice_prefix',
        'invoice_next_number',
        'currency',
        'status',
    ];

    protected $casts = [
        'vat_enabled' => 'boolean',
        'vat_registered' => 'boolean',
        'vat_percentage' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'quotation_next_number' => 'integer',
        'invoice_next_number' => 'integer',
    ];

    public function isVatEnabled(): bool
    {
        return (bool) ($this->vat_enabled ?? $this->vat_registered ?? false);
    }

    public function getVatEnabledAttribute($value): bool
    {
        return (bool) ($value ?? $this->attributes['vat_registered'] ?? false);
    }

    public function setVatEnabledAttribute($value): void
    {
        $bool = (bool) $value;
        $this->attributes['vat_enabled'] = $bool;
        $this->attributes['vat_registered'] = $bool;
    }

    public function setVatRegisteredAttribute($value): void
    {
        $bool = (bool) $value;
        $this->attributes['vat_registered'] = $bool;
        $this->attributes['vat_enabled'] = $bool;
    }

    public function getTaxPercentageAttribute($value): ?float
    {
        if ($value !== null) {
            return (float) $value;
        }

        return isset($this->attributes['vat_percentage']) && $this->attributes['vat_percentage'] !== null
            ? (float) $this->attributes['vat_percentage']
            : null;
    }

    public function setTaxPercentageAttribute($value): void
    {
        $this->attributes['tax_percentage'] = $value;
        if (! isset($this->attributes['vat_percentage']) || $this->attributes['vat_percentage'] === null) {
            $this->attributes['vat_percentage'] = $value;
        }
    }

    public function setVatPercentageAttribute($value): void
    {
        $this->attributes['vat_percentage'] = $value;
        if (! isset($this->attributes['tax_percentage']) || $this->attributes['tax_percentage'] === null) {
            $this->attributes['tax_percentage'] = $value;
        }
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(CompanyTemplate::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

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
        'vat_registered',
        'vat_number',
        'vat_percentage',
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
        'vat_registered' => 'boolean',
        'vat_percentage' => 'decimal:2',
        'quotation_next_number' => 'integer',
        'invoice_next_number' => 'integer',
    ];

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
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'document_type',
        'template_name',
        'header_text',
        'footer_text',
        'terms_conditions',
        'show_logo',
        'show_bank_details',
        'show_vat',
        'show_signature',
        'template_config',
        'is_default',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_bank_details' => 'boolean',
        'show_vat' => 'boolean',
        'show_signature' => 'boolean',
        'template_config' => 'array',
        'is_default' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
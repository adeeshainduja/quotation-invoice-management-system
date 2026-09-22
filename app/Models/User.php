<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function templates()
    {
        return $this->belongsToMany(CompanyTemplate::class, 'company_template_user', 'user_id', 'company_template_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions()
            ->where('key', $permission)
            ->exists();
    }

    public function accessibleCompanies()
    {
        if ($this->isAdmin()) {
            return Company::where('status', 'ACTIVE');
        }

        return $this->companies()->where('companies.status', 'ACTIVE');
    }

    public function hasCompanyAccess(int|string|null $companyId): bool
    {
        if (! $companyId) {
            return false;
        }

        if ($this->isAdmin()) {
            return Company::where('id', $companyId)->exists();
        }

        return $this->companies()
            ->where('companies.id', $companyId)
            ->exists();
    }

    public function hasTemplateAccess(int|string|null $templateId): bool
    {
        if (! $templateId) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return $this->templates()
            ->where('company_templates.id', $templateId)
            ->exists();
    }

    public function getAssignedInvoiceTemplate(?int $companyId = null): ?CompanyTemplate
    {
        $query = $this->templates()->where('document_type', 'INVOICE');

        if ($companyId) {
            $companyTemplate = (clone $query)->where('company_id', $companyId)->first();
            if ($companyTemplate) {
                return $companyTemplate;
            }
        }

        return $query->first();
    }

    public function getAssignedQuotationTemplate(?int $companyId = null): ?CompanyTemplate
    {
        $query = $this->templates()->where('document_type', 'QUOTATION');

        if ($companyId) {
            $companyTemplate = (clone $query)->where('company_id', $companyId)->first();
            if ($companyTemplate) {
                return $companyTemplate;
            }
        }

        return $query->first();
    }
}

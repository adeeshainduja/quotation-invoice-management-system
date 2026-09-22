<?php

namespace App\Providers;

use App\Models\Company;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define(
            'viewActivityLogs',
            function ($user) {
                return $user->role === 'ADMIN'
                    && $user->status === 'ACTIVE';
            }
        );

        View::composer('partials.topbar', function ($view) {
            $user = auth()->user();
            if (! $user) {
                return;
            }

            if ($user->isAdmin()) {
                $companies = Company::where('status', 'ACTIVE')->orderBy('name')->get();
            } else {
                $companies = $user->accessibleCompanies()->where('companies.status', 'ACTIVE')->orderBy('name')->get();
            }

            $selectedCompanyId = request()->integer('company_id');
            if (! $selectedCompanyId || ! $companies->contains('id', $selectedCompanyId)) {
                $selectedCompanyId = optional($companies->first())->id;
            }

            $currentCompany = $companies->firstWhere('id', $selectedCompanyId);

            $view->with([
                'topbarCompanies' => $companies,
                'currentCompanyId' => $selectedCompanyId,
                'currentCompanyName' => $currentCompany?->name ?? 'No Company Assigned',
                'currentCompany' => $currentCompany,
            ]);
        });
    }
}

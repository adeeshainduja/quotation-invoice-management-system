@php
    $authUser = auth()->user();
    $activeCompanyId = $currentCompanyId ?? ($companyId ?? null);
    $activeCompanyName = $currentCompanyName ?? (isset($selectedCompany) ? $selectedCompany->name : (isset($company) && is_object($company) ? $company->name : null));
    if (!$activeCompanyName && isset($topbarCompanies) && $activeCompanyId) {
        $activeCompanyName = optional($topbarCompanies->firstWhere('id', $activeCompanyId))->name;
    }
@endphp

<header class="topbar top-header">
    <div class="topbar-left header-left">
        <button class="menu menu-btn" type="button"><i data-lucide="menu"></i></button>
    </div>

    <div class="topbar-right header-right">
        @if($authUser)
            @if($authUser->isAdmin())
                <form method="GET" action="{{ url()->current() }}" id="topbarCompanyForm" style="margin: 0;">
                    @foreach(request()->query() as $key => $val)
                        @if($key !== 'company_id' && !is_array($val))
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <select name="company_id" class="company-select" onchange="this.form.submit()">
                        @if(isset($topbarCompanies))
                            @foreach($topbarCompanies as $comp)
                                <option value="{{ $comp->id }}" {{ (isset($activeCompanyId) && $activeCompanyId == $comp->id) ? 'selected' : '' }}>
                                    {{ $comp->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </form>
            @else
                <div class="company-readonly" style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 14px; font-size: 14px; color: #1e293b;">
                    <span style="color: #64748b; font-weight: 500;">Company:</span>
                    <strong style="color: #0f172a;">{{ $activeCompanyName ?: ($authUser->accessibleCompanies()->first()?->name ?? 'No Company Assigned') }}</strong>
                </div>
            @endif

            <div class="notification" style="cursor: pointer;">
                <i data-lucide="bell"></i>
                <span></span>
            </div>

            <div class="user user-box">
                <div class="avatar">
                    {{ strtoupper(substr($authUser->name, 0, 2)) }}
                </div>

                <div>
                    <strong>{{ $authUser->name }}</strong>
                    <small>{{ $authUser->isAdmin() ? 'Administrator' : 'User' }}</small>
                </div>

                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button class="logout" type="submit" title="Logout" style="display: flex; align-items: center; justify-content: center; cursor: pointer; border: none; background: none;">
                        <i data-lucide="log-out"></i>
                    </button>
                </form>
            </div>
        @endif
    </div>
</header>

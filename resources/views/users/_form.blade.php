<section class="form-card">

    <div class="card-heading">

        <div>
            <h2>User Information</h2>

            <p>
                Account information used to log into the system
            </p>
        </div>

    </div>


    <div class="form-grid">

        <div class="form-field">

            <label>Name *</label>

            <input
                type="text"
                name="name"
                value="{{ old(
                    'name',
                    $user->name ?? ''
                ) }}"
                required
            >

        </div>


        <div class="form-field">

            <label>Email *</label>

            <input
                type="email"
                name="email"
                value="{{ old(
                    'email',
                    $user->email ?? ''
                ) }}"
                required
            >

        </div>


        <div class="form-field">

            <label>
                {{ isset($user)
                    ? 'New Password'
                    : 'Temporary Password *'
                }}
            </label>

            <input
                type="password"
                name="password"
                {{ isset($user) ? '' : 'required' }}
            >

            @if(isset($user))

                <small>
                    Leave blank to keep the current password.
                </small>

            @endif

        </div>


        <div class="form-field">

            <label>
                Confirm Password
                {{ isset($user) ? '' : '*' }}
            </label>

            <input
                type="password"
                name="password_confirmation"
                {{ isset($user) ? '' : 'required' }}
            >

        </div>


        <div class="form-field">

            <label>Status *</label>

            <select
                name="status"
                required
            >

                <option
                    value="ACTIVE"
                    {{ old(
                        'status',
                        $user->status ?? 'ACTIVE'
                    ) === 'ACTIVE'
                        ? 'selected'
                        : ''
                    }}
                >
                    Active
                </option>


                <option
                    value="INACTIVE"
                    {{ old(
                        'status',
                        $user->status ?? 'ACTIVE'
                    ) === 'INACTIVE'
                        ? 'selected'
                        : ''
                    }}
                >
                    Inactive
                </option>

            </select>

        </div>


        <div class="form-field">

            <label>Role</label>

            <div class="role-display">

                <i data-lucide="user"></i>

                USER

                <small>
                    Admin controls this user's permissions
                </small>

            </div>

        </div>

    </div>

</section>


<section class="form-card">

    <div class="card-heading">
        <div>
            <h2>Company Access</h2>
            <p>Select which companies this user is allowed to access</p>
        </div>
    </div>

    <div class="permission-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));">
        @php
            $userCompanyIds = old('companies', $selectedCompanies ?? []);
        @endphp
        @forelse($companies as $comp)
            <label class="permission-option" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; background: #fafafa; display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input
                    type="checkbox"
                    name="companies[]"
                    value="{{ $comp->id }}"
                    class="company-checkbox"
                    {{ in_array($comp->id, $userCompanyIds) ? 'checked' : '' }}
                >
                <div style="display: flex; flex-direction: column;">
                    <strong style="color: #1f2937; font-size: 14px;">{{ $comp->name }}</strong>
                    <small style="color: #6b7280;">{{ $comp->currency ?? 'LKR' }} &bull; {{ $comp->city ?? '' }}</small>
                </div>
            </label>
        @empty
            <p style="color: #6b7280; font-size: 14px;">No active companies available to assign.</p>
        @endforelse
    </div>

</section>


<section class="form-card">

    <div class="card-heading">
        <div>
            <h2>Template Access</h2>
            <p>Select which document templates this user is allowed to use</p>
        </div>
    </div>

    @php
        $userTemplateIds = old('templates', $selectedTemplates ?? []);
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        <div class="permission-group">
            <div class="module-heading">
                <strong>Invoice Templates</strong>
            </div>
            @forelse($invoiceTemplates as $tpl)
                <label class="permission-option" style="padding: 8px 12px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="templates[]"
                        value="{{ $tpl->id }}"
                        {{ in_array($tpl->id, $userTemplateIds) ? 'checked' : '' }}
                    >
                    <span>{{ $tpl->template_name }}</span>
                </label>
            @empty
                <p style="color: #6b7280; font-size: 13px; padding: 10px;">No invoice templates found.</p>
            @endforelse
        </div>

        <div class="permission-group">
            <div class="module-heading">
                <strong>Quotation Templates</strong>
            </div>
            @forelse($quotationTemplates as $tpl)
                <label class="permission-option" style="padding: 8px 12px; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="templates[]"
                        value="{{ $tpl->id }}"
                        {{ in_array($tpl->id, $userTemplateIds) ? 'checked' : '' }}
                    >
                    <span>{{ $tpl->template_name }}</span>
                </label>
            @empty
                <p style="color: #6b7280; font-size: 13px; padding: 10px;">No quotation templates found.</p>
            @endforelse
        </div>
    </div>

</section>


<section class="form-card">

    <div class="permissions-heading">

        <div>

            <h2>Permissions</h2>

            <p>
                Select exactly which functions this user can use
            </p>

        </div>


        <label class="select-all">

            <input
                type="checkbox"
                id="selectAll"
            >

            Select All

        </label>

    </div>


    <div class="permission-grid">

        @foreach($permissions as $module => $modulePermissions)

            <div class="permission-group">

                <div class="module-heading">

                    <strong>
                        {{ $module }}
                    </strong>


                    <label>

                        <input
                            type="checkbox"
                            class="module-select"
                        >

                        All

                    </label>

                </div>


                @foreach($modulePermissions as $permission)

                    @php

                        $checkedPermissions =
                            old(
                                'permissions',
                                $selectedPermissions ?? []
                            );

                    @endphp


                    <label class="permission-option">

                        <input
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission->id }}"
                            class="permission-checkbox"

                            {{ in_array(
                                $permission->id,
                                $checkedPermissions
                            ) ? 'checked' : '' }}
                        >

                        <span>
                            {{ $permission->name }}
                        </span>

                    </label>

                @endforeach

            </div>

        @endforeach

    </div>

</section>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const selectAll =
        document.getElementById('selectAll');


    const permissions =
        document.querySelectorAll(
            '.permission-checkbox'
        );


    selectAll.addEventListener(
        'change',
        function () {

            permissions.forEach(function (checkbox) {
                checkbox.checked =
                    selectAll.checked;
            });


            document
                .querySelectorAll('.module-select')
                .forEach(function (checkbox) {

                    checkbox.checked =
                        selectAll.checked;

                });
        }
    );


    document
        .querySelectorAll('.module-select')
        .forEach(function (moduleCheckbox) {

            moduleCheckbox.addEventListener(
                'change',
                function () {

                    const group =
                        moduleCheckbox.closest(
                            '.permission-group'
                        );


                    group
                        .querySelectorAll(
                            '.permission-checkbox'
                        )
                        .forEach(function (checkbox) {

                            checkbox.checked =
                                moduleCheckbox.checked;

                        });
                }
            );

        });

});

</script>
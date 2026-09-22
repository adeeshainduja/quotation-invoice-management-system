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

    @php
        $userCompanyIds = old('companies', $selectedCompanies ?? []);
    @endphp

    <div class="scroll-selection-box" style="max-height: 250px; overflow-y: auto;">
        @forelse($companies as $comp)
            <label class="scroll-selection-item">
                <input
                    type="checkbox"
                    name="companies[]"
                    value="{{ $comp->id }}"
                    class="company-checkbox"
                    {{ in_array($comp->id, $userCompanyIds) ? 'checked' : '' }}
                >
                <span class="selection-name">{{ $comp->name }}</span>
                @if(!empty($comp->currency) || !empty($comp->city))
                    <span class="selection-meta">({{ $comp->currency ?? 'LKR' }} &bull; {{ $comp->city ?? '' }})</span>
                @endif
            </label>
        @empty
            <p class="scroll-selection-empty">No active companies available to assign.</p>
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
            <div class="scroll-selection-box group-inner" style="max-height: 250px; overflow-y: auto;">
                @forelse($invoiceTemplates as $tpl)
                    <label class="scroll-selection-item">
                        <input
                            type="checkbox"
                            name="templates[]"
                            value="{{ $tpl->id }}"
                            class="template-checkbox"
                            {{ in_array($tpl->id, $userTemplateIds) ? 'checked' : '' }}
                        >
                        <span class="selection-name">{{ $tpl->template_name }}</span>
                    </label>
                @empty
                    <p class="scroll-selection-empty">No invoice templates found.</p>
                @endforelse
            </div>
        </div>

        <div class="permission-group">
            <div class="module-heading">
                <strong>Quotation Templates</strong>
            </div>
            <div class="scroll-selection-box group-inner" style="max-height: 250px; overflow-y: auto;">
                @forelse($quotationTemplates as $tpl)
                    <label class="scroll-selection-item">
                        <input
                            type="checkbox"
                            name="templates[]"
                            value="{{ $tpl->id }}"
                            class="template-checkbox"
                            {{ in_array($tpl->id, $userTemplateIds) ? 'checked' : '' }}
                        >
                        <span class="selection-name">{{ $tpl->template_name }}</span>
                    </label>
                @empty
                    <p class="scroll-selection-empty">No quotation templates found.</p>
                @endforelse
            </div>
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
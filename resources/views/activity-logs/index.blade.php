<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Activity Logs</title>


    <link
        rel="stylesheet"
        href="{{ asset('css/dashboard.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/activity-logs.css') }}"
    >


    <script src="https://unpkg.com/lucide@latest"></script>

</head>


<body>


@include('partials.sidebar')


<div class="page">


    {{-- TOP BAR --}}
    <header class="topbar">


        <button
            type="button"
            class="menu"
        >
            <i data-lucide="menu"></i>
        </button>


        <div class="topbar-right">


            <div class="restricted-badge">

                <i data-lucide="shield-check"></i>

                Restricted Access

            </div>


            <div class="notification">

                <i data-lucide="bell"></i>

                <span></span>

            </div>


            <div class="user">


                <div class="avatar">

                    {{
                        strtoupper(
                            substr(
                                auth()->user()->name,
                                0,
                                2
                            )
                        )
                    }}

                </div>


                <div>

                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <small>
                        Administrator
                    </small>

                </div>

            </div>

        </div>

    </header>



    <main>


        {{-- BREADCRUMB --}}
        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>

            Activity Logs

        </div>



        {{-- PAGE HEADER --}}
        <div class="activity-heading">


            <div>

                <div class="heading-title">

                    <h1>
                        Activity Logs
                    </h1>


                    <span class="admin-badge">

                        <i data-lucide="lock"></i>

                        Admin Only

                    </span>

                </div>


                <p>
                    Review system actions and changes made by users
                </p>

            </div>

        </div>



        {{-- SECURITY MESSAGE --}}
        <div class="security-notice">


            <div class="security-icon">

                <i data-lucide="shield-alert"></i>

            </div>


            <div>

                <strong>
                    Restricted Information
                </strong>

                <p>
                    Activity logs may contain customer,
                    financial and operational information.
                    Only authorized administrators can access this page.
                </p>

            </div>

        </div>



        {{-- FILTERS --}}
        <form
            method="GET"
            action="{{ route('activity-logs.index') }}"
            class="activity-filter"
        >


            {{-- COMPANY --}}
            <div class="filter-field">

                <label>
                    Company
                </label>


                <select name="company_id">

                    <option value="">
                        All Companies
                    </option>


                    @foreach($companyList as $company)

                        <option
                            value="{{ $company->id }}"
                            {{
                                request('company_id')
                                == $company->id
                                    ? 'selected'
                                    : ''
                            }}
                        >

                            {{ $company->name }}

                        </option>

                    @endforeach

                </select>

            </div>



            {{-- ENTITY --}}
            <div class="filter-field">

                <label>
                    Entity Type
                </label>


                <select name="entity_type">

                    <option value="">
                        All Entities
                    </option>


                    @foreach([
                        'COMPANY' => 'Company',
                        'CUSTOMER' => 'Customer',
                        'QUOTATION' => 'Quotation',
                        'INVOICE' => 'Invoice',
                        'PAYMENT' => 'Payment',
                        'TEMPLATE' => 'Template'
                    ] as $value => $label)

                        <option
                            value="{{ $value }}"
                            {{
                                request('entity_type') === $value
                                    ? 'selected'
                                    : ''
                            }}
                        >

                            {{ $label }}

                        </option>

                    @endforeach

                </select>

            </div>



            {{-- ACTION --}}
            <div class="filter-field">

                <label>
                    Action
                </label>


                <input
                    type="text"
                    name="action"
                    value="{{ request('action') }}"
                    placeholder="Create, update, delete..."
                >

            </div>



            {{-- FILTER BUTTON --}}
            <div class="filter-buttons">

                <button
                    type="submit"
                    class="filter-btn"
                >

                    <i data-lucide="filter"></i>

                    Filter

                </button>


                <a
                    href="{{ route('activity-logs.index') }}"
                    class="clear-btn"
                >

                    Clear

                </a>

            </div>


        </form>



        {{-- TABLE --}}
        <section class="activity-table-card">


            <div class="table-wrap">


                <table>


                    <thead>

                    <tr>

                        <th>#</th>

                        <th>
                            Date & Time
                        </th>

                        <th>
                            User
                        </th>

                        <th>
                            Company
                        </th>

                        <th>
                            Entity
                        </th>

                        <th>
                            Record
                        </th>

                        <th>
                            Action
                        </th>

                        <th>
                            Details
                        </th>

                    </tr>

                    </thead>



                    <tbody>


                    @forelse($logs as $log)


                        <tr>


                            {{-- NUMBER --}}
                            <td>

                                {{
                                    $logs->firstItem()
                                    + $loop->index
                                }}

                            </td>



                            {{-- DATE --}}
                            <td>

                                <div class="date-info">

                                    <strong>

                                        {{
                                            \Carbon\Carbon::parse(
                                                $log->created_at
                                            )->format(
                                                'd M Y'
                                            )
                                        }}

                                    </strong>


                                    <span>

                                        {{
                                            \Carbon\Carbon::parse(
                                                $log->created_at
                                            )->format(
                                                'h:i A'
                                            )
                                        }}

                                    </span>

                                </div>

                            </td>



                            {{-- USER --}}
                            <td>

                                <div class="activity-user">


                                    <div class="small-avatar">

                                        {{
                                            strtoupper(
                                                substr(
                                                    $log->user_name,
                                                    0,
                                                    2
                                                )
                                            )
                                        }}

                                    </div>


                                    <div>

                                        <strong>
                                            {{ $log->user_name }}
                                        </strong>

                                        <span>
                                            {{ $log->user_email }}
                                        </span>

                                    </div>

                                </div>

                            </td>



                            {{-- COMPANY --}}
                            <td>

                                {{ $log->company_name }}

                            </td>



                            {{-- ENTITY --}}
                            <td>

                                <span class="entity-badge">

                                    {{
                                        ucwords(
                                            strtolower(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $log->entity_type
                                                )
                                            )
                                        )
                                    }}

                                </span>

                            </td>



                            {{-- ENTITY ID --}}
                            <td>

                                <span class="record-id">

                                    #{{ $log->entity_id }}

                                </span>

                            </td>



                            {{-- ACTION --}}
                            <td>

                                @php
                                    $actionClass =
                                        strtolower(
                                            str_replace(
                                                '_',
                                                '-',
                                                $log->action
                                            )
                                        );
                                @endphp


                                <span
                                    class="action-badge {{ $actionClass }}"
                                >

                                    {{
                                        ucwords(
                                            strtolower(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $log->action
                                                )
                                            )
                                        )
                                    }}

                                </span>

                            </td>



                            {{-- DETAILS --}}
                            <td>

                                <button
                                    type="button"
                                    class="view-details-btn"

                                    data-log-id="{{ $log->id }}"

                                    data-user="{{ $log->user_name }}"

                                    data-company="{{ $log->company_name }}"

                                    data-entity="{{ $log->entity_type }}"

                                    data-entity-id="{{ $log->entity_id }}"

                                    data-action="{{ $log->action }}"

                                    data-date="{{
                                        \Carbon\Carbon::parse(
                                            $log->created_at
                                        )->format(
                                            'd M Y h:i:s A'
                                        )
                                    }}"

                                    data-old="{{ base64_encode(
                                        $log->old_data ?? ''
                                    ) }}"

                                    data-new="{{ base64_encode(
                                        $log->new_data ?? ''
                                    ) }}"

                                    onclick="openLogModal(this)"
                                >

                                    <i data-lucide="eye"></i>

                                    View

                                </button>

                            </td>


                        </tr>



                    @empty


                        <tr>


                            <td
                                colspan="8"
                                class="empty-state"
                            >


                                <div class="empty-icon">

                                    <i data-lucide="history"></i>

                                </div>


                                <strong>
                                    No activity logs found
                                </strong>


                                <span>
                                    System activity will appear here
                                    when actions are recorded.
                                </span>


                            </td>

                        </tr>


                    @endforelse


                    </tbody>

                </table>

            </div>



            {{-- PAGINATION --}}
            <div class="table-footer">


                <span>

                    Showing

                    {{ $logs->firstItem() ?? 0 }}

                    to

                    {{ $logs->lastItem() ?? 0 }}

                    of

                    {{ $logs->total() }}

                    activities

                </span>


                {{ $logs->links() }}

            </div>


        </section>


    </main>

</div>



{{-- DETAILS MODAL --}}
<div
    class="log-modal-overlay"
    id="logModal"
>


    <div class="log-modal">


        {{-- MODAL HEADER --}}
        <div class="modal-header">


            <div>

                <div class="modal-title">

                    <i data-lucide="shield-check"></i>

                    <div>

                        <h2>
                            Activity Details
                        </h2>

                        <span id="modalLogId"></span>

                    </div>

                </div>

            </div>


            <button
                type="button"
                class="modal-close"
                onclick="closeLogModal()"
            >

                <i data-lucide="x"></i>

            </button>


        </div>



        {{-- META --}}
        <div class="modal-meta">


            <div>

                <span>
                    User
                </span>

                <strong id="modalUser">
                    -
                </strong>

            </div>


            <div>

                <span>
                    Company
                </span>

                <strong id="modalCompany">
                    -
                </strong>

            </div>


            <div>

                <span>
                    Entity
                </span>

                <strong id="modalEntity">
                    -
                </strong>

            </div>


            <div>

                <span>
                    Record
                </span>

                <strong id="modalEntityId">
                    -
                </strong>

            </div>


            <div>

                <span>
                    Action
                </span>

                <strong id="modalAction">
                    -
                </strong>

            </div>


            <div>

                <span>
                    Date & Time
                </span>

                <strong id="modalDate">
                    -
                </strong>

            </div>


        </div>



        {{-- CHANGE DATA --}}
        <div class="change-grid">


            {{-- OLD --}}
            <section class="change-card old-card">


                <div class="change-header">

                    <i data-lucide="history"></i>

                    <div>

                        <h3>
                            Previous Data
                        </h3>

                        <span>
                            Values before the action
                        </span>

                    </div>

                </div>


                <div
                    class="json-content"
                    id="modalOldData"
                >
                    No previous data
                </div>


            </section>



            {{-- NEW --}}
            <section class="change-card new-card">


                <div class="change-header">

                    <i data-lucide="file-check"></i>

                    <div>

                        <h3>
                            New Data
                        </h3>

                        <span>
                            Values after the action
                        </span>

                    </div>

                </div>


                <div
                    class="json-content"
                    id="modalNewData"
                >
                    No new data
                </div>


            </section>


        </div>



        {{-- FOOTER --}}
        <div class="modal-footer">

            <div class="modal-security">

                <i data-lucide="lock"></i>

                Sensitive system information

            </div>


            <button
                type="button"
                class="close-btn"
                onclick="closeLogModal()"
            >
                Close
            </button>

        </div>


    </div>

</div>



<script>

    /*
    |--------------------------------------------------------------------------
    | Safe JSON formatter
    |--------------------------------------------------------------------------
    */

    function formatJson(base64Value)
    {
        if (!base64Value) {
            return null;
        }


        try {

            const decoded =
                decodeURIComponent(
                    escape(
                        atob(base64Value)
                    )
                );


            if (!decoded) {
                return null;
            }


            const data =
                JSON.parse(decoded);


            return data;

        }
        catch (error) {

            return null;

        }
    }



    /*
    |--------------------------------------------------------------------------
    | Render JSON
    |--------------------------------------------------------------------------
    */

    function renderData(containerId, data)
    {
        const container =
            document.getElementById(
                containerId
            );


        container.innerHTML = '';


        if (
            !data ||
            typeof data !== 'object'
        ) {

            container.innerHTML = `
                <div class="no-change-data">
                    No data recorded
                </div>
            `;

            return;
        }


        Object.entries(data)
            .forEach(
                ([key, value]) => {

                    /*
                     * Extra UI protection.
                     * Do not display common secret fields.
                     */
                    const sensitiveKeys = [

                        'password',

                        'password_confirmation',

                        'remember_token',

                        'token',

                        'access_token',

                        'refresh_token',

                        'api_key',

                        'api_secret',

                        'secret'

                    ];


                    let displayValue = value;


                    if (
                        sensitiveKeys.includes(
                            key.toLowerCase()
                        )
                    ) {

                        displayValue =
                            '[REDACTED]';

                    }


                    if (
                        typeof displayValue ===
                        'object'
                        &&
                        displayValue !== null
                    ) {

                        displayValue =
                            JSON.stringify(
                                displayValue,
                                null,
                                2
                            );

                    }


                    if (
                        displayValue === null
                    ) {

                        displayValue = 'NULL';

                    }


                    const row =
                        document.createElement(
                            'div'
                        );


                    row.className =
                        'json-row';


                    const keyElement =
                        document.createElement(
                            'span'
                        );


                    keyElement.className =
                        'json-key';


                    keyElement.textContent =
                        key
                            .replaceAll(
                                '_',
                                ' '
                            )
                            .replace(
                                /\b\w/g,
                                letter =>
                                    letter.toUpperCase()
                            );


                    const valueElement =
                        document.createElement(
                            'pre'
                        );


                    valueElement.className =
                        'json-value';


                    valueElement.textContent =
                        String(
                            displayValue
                        );


                    row.appendChild(
                        keyElement
                    );


                    row.appendChild(
                        valueElement
                    );


                    container.appendChild(
                        row
                    );

                }
            );
    }



    /*
    |--------------------------------------------------------------------------
    | Open Modal
    |--------------------------------------------------------------------------
    */

    function openLogModal(button)
    {
        document.getElementById(
            'modalLogId'
        ).textContent =
            'Log #' +
            button.dataset.logId;


        document.getElementById(
            'modalUser'
        ).textContent =
            button.dataset.user || '-';


        document.getElementById(
            'modalCompany'
        ).textContent =
            button.dataset.company || '-';


        document.getElementById(
            'modalEntity'
        ).textContent =
            button.dataset.entity || '-';


        document.getElementById(
            'modalEntityId'
        ).textContent =
            '#' +
            (
                button.dataset.entityId
                || '-'
            );


        document.getElementById(
            'modalAction'
        ).textContent =
            button.dataset.action || '-';


        document.getElementById(
            'modalDate'
        ).textContent =
            button.dataset.date || '-';


        const oldData =
            formatJson(
                button.dataset.old
            );


        const newData =
            formatJson(
                button.dataset.new
            );


        renderData(
            'modalOldData',
            oldData
        );


        renderData(
            'modalNewData',
            newData
        );


        document
            .getElementById(
                'logModal'
            )
            .classList
            .add('open');


        document.body.classList.add(
            'modal-open'
        );


        lucide.createIcons();
    }



    /*
    |--------------------------------------------------------------------------
    | Close Modal
    |--------------------------------------------------------------------------
    */

    function closeLogModal()
    {
        document
            .getElementById(
                'logModal'
            )
            .classList
            .remove('open');


        document.body.classList.remove(
            'modal-open'
        );
    }



    /*
     * Click outside modal
     */
    document
        .getElementById('logModal')
        .addEventListener(
            'click',
            function(event) {

                if (
                    event.target === this
                ) {

                    closeLogModal();

                }

            }
        );


    /*
     * ESC closes modal
     */
    document.addEventListener(
        'keydown',
        function(event) {

            if (
                event.key === 'Escape'
            ) {

                closeLogModal();

            }

        }
    );


    lucide.createIcons();

</script>


</body>

</html>
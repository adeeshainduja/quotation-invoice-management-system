<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create User</title>

    <link
        rel="stylesheet"
        href="{{ asset('css/dashboard.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/users.css') }}"
    >

    <script src="https://unpkg.com/lucide@latest"></script>

</head>

<body>

@include('partials.sidebar')


<div class="page content-wrapper">
    @include('partials.topbar')

    <main>

        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>

            <a href="{{ route('users.index') }}">
                Users
            </a>

            <span>›</span>

            Create

        </div>


        <div class="page-heading">

            <div>

                <h1>Create User</h1>

                <p>
                    Add a new system user and assign permissions
                </p>

            </div>

        </div>


        @if($errors->any())

            <div class="error-message">

                <strong>
                    Please correct the following:
                </strong>

                <ul>

                    @foreach($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form
            method="POST"
            action="{{ route('users.store') }}"
        >

            @csrf


            @include('users._form')


            <div class="form-actions">

                <a
                    href="{{ route('users.index') }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="primary-btn"
                >

                    <i data-lucide="user-plus"></i>

                    Create User

                </button>

            </div>

        </form>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>
</html>
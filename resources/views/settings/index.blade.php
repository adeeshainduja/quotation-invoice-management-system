<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Settings</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

@include('partials.sidebar')

<div class="page">

    <main>

        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">Home</a>
            <span>›</span>
            Settings
        </div>

        <div class="settings-heading">
            <h1>Settings</h1>
            <p>Manage your account settings</p>
        </div>

        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="settings-grid">

            <section class="settings-card">

                <h2>
                    <i data-lucide="user"></i>
                    Profile
                </h2>

                <form method="POST" action="{{ route('settings.profile') }}">
                    @csrf

                    <label>Name</label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', auth()->user()->name) }}"
                        required
                    >

                    <label>Email</label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', auth()->user()->email) }}"
                        required
                    >

                    <button type="submit">
                        Save Profile
                    </button>
                </form>

            </section>


            <section class="settings-card">

                <h2>
                    <i data-lucide="lock"></i>
                    Change Password
                </h2>

                <form method="POST" action="{{ route('settings.password') }}">
                    @csrf

                    <label>Current Password</label>

                    <input
                        type="password"
                        name="current_password"
                        required
                    >

                    <label>New Password</label>

                    <input
                        type="password"
                        name="password"
                        required
                    >

                    <label>Confirm Password</label>

                    <input
                        type="password"
                        name="password_confirmation"
                        required
                    >

                    <button type="submit">
                        Change Password
                    </button>
                </form>

            </section>

        </div>

    </main>

</div>

<script>
    lucide.createIcons();
</script>

</body>
</html>
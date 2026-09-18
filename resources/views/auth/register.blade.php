<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account</title>

    <link rel="stylesheet" href="{{ asset('css/quotation-login.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Caveat:wght@500;600&display=swap"
          rel="stylesheet">
</head>

<body>

<div class="login-page">

    <section class="left-panel">

        <div class="left-content">

            <div class="brand">

                <div class="brand-icon">
                    <svg viewBox="0 0 64 64" fill="none">

                        <path
                            d="M16 5H39L52 18V55C52 58.3 49.3 61 46 61H16C12.7 61 10 58.3 10 55V11C10 7.7 12.7 5 16 5Z"
                            stroke="currentColor"
                            stroke-width="4"
                        />

                        <path
                            d="M39 5V18H52"
                            stroke="currentColor"
                            stroke-width="4"
                        />

                        <path
                            d="M20 29H32M20 39H42M20 49H38"
                            stroke="currentColor"
                            stroke-width="4"
                            stroke-linecap="round"
                        />

                    </svg>
                </div>

                <div>
                    <h1>Quotation & Invoice</h1>
                    <p>Management System</p>
                </div>

            </div>

            <div class="brand-line"></div>

            <h2 class="tagline">
                Create. Manage. Send. Get Paid.
            </h2>

            <div class="features">

                <div class="feature">
                    <div>
                        <h3>Quotations</h3>
                        <p>Create professional quotations</p>
                    </div>
                </div>

                <div class="feature">
                    <div>
                        <h3>Invoices</h3>
                        <p>Manage and track invoices</p>
                    </div>
                </div>

                <div class="feature">
                    <div>
                        <h3>Customers</h3>
                        <p>Keep your customer records</p>
                    </div>
                </div>

                <div class="feature">
                    <div>
                        <h3>Payments</h3>
                        <p>Track payments with ease</p>
                    </div>
                </div>

            </div>

        </div>

    </section>


    <section class="right-panel">

        <div class="login-card">

            <div class="login-header">
                <h2>Create Account</h2>
                <p>Register your account</p>
            </div>


            @if ($errors->any())
                <div class="alert-error">
                    {{ $errors->first() }}
                </div>
            @endif


            <form action="{{ route('register.submit') }}" method="POST">

                @csrf


                <div class="form-group">

                    <label>Full Name</label>

                    <div class="input-wrapper">
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Enter your full name"
                            required
                        >
                    </div>

                </div>


                <div class="form-group">

                    <label>Email Address</label>

                    <div class="input-wrapper">
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter your email address"
                            required
                        >
                    </div>

                </div>


                <div class="form-group">

                    <label>Password</label>

                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="password"
                            placeholder="Enter password"
                            required
                        >
                    </div>

                </div>


                <div class="form-group">

                    <label>Confirm Password</label>

                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="password_confirmation"
                            placeholder="Confirm password"
                            required
                        >
                    </div>

                </div>


                <button type="submit" class="login-button">
                    Create Account →
                </button>


                <div class="signup-section">

                    Already have an account?

                    <a href="{{ route('login') }}">
                        Sign In
                    </a>

                </div>

            </form>

        </div>

    </section>

</div>

</body>
</html>
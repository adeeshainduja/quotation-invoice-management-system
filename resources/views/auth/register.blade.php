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

    {{-- LEFT SIDE --}}
    <section class="left-panel">

        <div class="left-content">

            <div class="brand">

                <div class="brand-icon">
                    <svg viewBox="0 0 64 64" fill="none">
                        <path d="M16 5H39L52 18V55C52 58.3 49.3 61 46 61H16C12.7 61 10 58.3 10 55V11C10 7.7 12.7 5 16 5Z"
                              stroke="currentColor"
                              stroke-width="4"/>
                        <path d="M39 5V18H52"
                              stroke="currentColor"
                              stroke-width="4"/>
                        <path d="M20 29H32M20 39H42M20 49H38"
                              stroke="currentColor"
                              stroke-width="4"
                              stroke-linecap="round"/>
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
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 2h8l4 4v16H6z"/>
                            <path d="M14 2v5h5"/>
                            <path d="M9 12h6M9 16h6"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Quotations</h3>
                        <p>Create professional quotations</p>
                    </div>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 2h8l4 4v16H6z"/>
                            <path d="M14 2v5h5"/>
                            <path d="M9 12h6M9 16h6"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Invoices</h3>
                        <p>Manage and track invoices</p>
                    </div>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="9" cy="8" r="3"/>
                            <circle cx="16" cy="9" r="2.5"/>
                            <path d="M3 20c0-4 2.5-6 6-6s6 2 6 6"/>
                            <path d="M14 15c4 0 7 1.5 7 5"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Customers</h3>
                        <p>Keep your customer records</p>
                    </div>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 20h16"/>
                            <path d="M6 20v-6h4v6"/>
                            <path d="M12 20V9h4v11"/>
                            <path d="M18 20V4h3v16"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Payments</h3>
                        <p>Track payments with ease</p>
                    </div>
                </div>

            </div>

            <div class="support-text">
                <span>Your Business</span>
                <span>Our Support</span>
                <div></div>
            </div>

        </div>

    </section>



    {{-- RIGHT SIDE --}}
    <section class="right-panel">

        <div class="login-card">

            <div class="login-header">
                <h2>Create Account</h2>
                <p>Get started with your new account</p>
            </div>

            @if ($errors->any())
                <div class="alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Full Name</label>

                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 20c0-4 3-6 8-6s8 2 8 6"/>
                            </svg>
                        </span>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Enter your full name"
                            required
                            autofocus
                        >
                    </div>
                </div>


                <div class="form-group">
                    <label for="email">Email Address</label>

                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="M4 7l8 6 8-6"/>
                            </svg>
                        </span>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter your email address"
                            required
                        >
                    </div>
                </div>


                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="10" width="14" height="11" rx="2"/>
                                <path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>
                        </span>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Enter password"
                            required
                        >

                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <svg viewBox="0 0 24 24">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>


                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>

                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24">
                                <rect x="5" y="10" width="14" height="11" rx="2"/>
                                <path d="M8 10V7a4 4 0 018 0v3"/>
                            </svg>
                        </span>

                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="Confirm password"
                            required
                        >

                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                            <svg viewBox="0 0 24 24">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>


                <button type="submit" class="login-button">
                    <span>Create Account</span>
                    <svg viewBox="0 0 24 24">
                        <path d="M5 12h14"/>
                        <path d="M14 7l5 5-5 5"/>
                    </svg>
                </button>


                <div class="signup-section">
                    <span>Already have an account?</span>
                    <a href="{{ route('login') }}">Sign In</a>
                </div>

            </form>


            <div class="login-footer">
                <div class="footer-line"></div>
                <p>Quotation & Invoice Management System</p>
                <p>© {{ date('Y') }}. All rights reserved.</p>
            </div>

        </div>

    </section>

</div>

<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
    }
</script>

</body>
</html>
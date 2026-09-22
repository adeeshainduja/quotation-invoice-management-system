<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quotation & Invoice System')</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @yield('styles')

    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

@include('partials.sidebar')

<div class="page content-wrapper">
    @include('partials.topbar')

    <main class="container">
        @yield('content')
    </main>
</div>

@yield('scripts')
<script>
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
</body>
</html>

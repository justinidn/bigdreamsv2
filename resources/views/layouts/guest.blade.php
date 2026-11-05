<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Log in') }} · {{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-body-tertiary d-flex align-items-center min-vh-100 py-5">
    <!-- Menggunakan container dengan max-width yang lebih aman agar tidak terlalu lebar -->
    <main class="container">
        <div class="row justify-content-center">
            <!-- Menambahkan mx-auto dan membatasi max-width khusus untuk card/form login -->
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4 mx-auto" style="max-width: 420px;">
                <div class="text-center mb-4">
                    <a href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                        <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 fw-bold px-3 py-2">
                            {{ mb_substr(config('app.name', 'Laravel'), 0, 1) }}
                        </span>
                        <span class="fs-4 fw-bold text-body">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                </div>

                {{ $slot }}


            </div>
        </div>
    </main>
</body>

</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100 lg:h-screen lg:overflow-hidden flex flex-col">
    <!-- Navbar tetap di atas -->
    <header class="w-full shrink-0">
        @include('layouts.navigation')
    </header>

    <!-- Kontainer utama: Di HP bisa scroll, di PC (lg) terkunci pas -->
    <div class="w-full lg:flex-1 lg:min-h-0 py-3 px-4 sm:px-6 lg:px-8 flex flex-col">
        <div class="max-w-7xl mx-auto w-full lg:flex-1 lg:min-h-0 flex flex-col">

            @isset($header)
            <div class="bg-white shadow-sm border border-gray-200 w-full mb-3 rounded-lg shrink-0">
                <div class="py-2 px-4 text-center sm:text-left font-semibold text-gray-800">
                    {{ $header }}
                </div>
            </div>
            @endisset

            <!-- Main slot -->
            <main class="w-full lg:flex-1 lg:min-h-0 flex flex-col">
                {{ $slot }}
            </main>

        </div>
    </div>

    <x-toast-container />
</body>

</html>
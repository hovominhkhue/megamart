<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Empêche le flash des éléments Alpine avant init --}}
    <style>[x-cloak]{display:none !important}</style>
</head>
<body class="font-sans antialiased min-h-screen bg-gray-100 dark:bg-gray-900">

    <!-- Navbar -->
    <header class="bg-white dark:bg-gray-800 shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <a href="{{ route('products.index') }}" class="font-semibold text-lg">
                Boutique
            </a>

            <nav class="flex items-center gap-4">
                @auth
                    <a href="{{ route('orders.index') }}" class="text-sm">Mes commandes</a>
                    <a href="{{ route('dashboard') }}" class="text-sm">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm">Login</a>
                    <a href="{{ route('register') }}" class="text-sm">Register</a>
                @endauth

                <!-- Panier -->
                <button
                    type="button"
                    x-data
                    @click="$dispatch('open-cart')"
                    class="relative px-3 py-1 rounded border"
                    aria-label="Ouvrir le panier"
                >
                    Panier
                    <span id="cart-badge"
                          class="absolute -top-2 -right-2 text-xs px-1.5 py-0.5 rounded-full bg-black text-white">
                        {{ session('cart_count', 0) }}
                    </span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Toast (succès/erreur AJAX) -->
    <div id="toast"
         role="status" aria-live="polite"
         class="fixed top-4 right-4 hidden px-3 py-2 rounded bg-black text-white text-sm z-50"></div>

    <!-- Drawer panier (optionnel) -->
    @includeIf('partials.cart-drawer')

    <!-- Page Heading (Breeze) -->
    @isset($header)
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <!-- Page Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

</body>
</html>
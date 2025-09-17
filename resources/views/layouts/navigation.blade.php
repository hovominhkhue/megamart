<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        {{ __('Accueil') }}
                    </x-nav-link>

                    <!-- Panier (desktop) avec badge -->
                    <x-nav-link :href="route('cart.show')" :active="request()->routeIs('cart.show')">
                        <span class="inline-flex items-center">
                            <!-- Icône panier (optionnelle) -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 me-2" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M7 4h10l1 5H6l1-5Zm-1 7h12l-1.2 6H7.2L6 11Zm2 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                            </svg>
                            <span>{{ __('Panier') }}</span>
                            @if(($cartCount ?? 0) > 0)
                                <span class="ms-2 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-xs font-semibold rounded-full bg-blue-600 text-white dark:bg-blue-500">
                                    {{ ($cartCount ?? 0) > 99 ? '99+' : ($cartCount ?? 0) }}
                                </span>
                            @endif
                        </span>
                    </x-nav-link>

                    <!-- Mes commandes (desktop) -->
                    @auth
                        <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                            {{ __('Mes commandes') }}
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <!-- Right side -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <!-- Settings Dropdown -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition">
                                <div>{{ auth()->user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <!-- Mes commandes (dropdown) -->
                            <x-dropdown-link :href="route('orders.index')">
                                {{ __('Mes commandes') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-4">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:underline">
                            {{ __('Se connecter') }}
                        </a>
                        <a href="{{ route('register') }}" class="text-sm font-medium text-blue-600 hover:underline">
                            {{ __('Créer un compte') }}
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('Accueil') }}
            </x-responsive-nav-link>

            <!-- Panier (mobile) avec badge -->
            <x-responsive-nav-link :href="route('cart.show')" :active="request()->routeIs('cart.show')">
                <span class="inline-flex items-center justify-between w-full">
                    <span class="inline-flex items-center">
                        <!-- Icône panier (optionnelle) -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 me-2" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M7 4h10l1 5H6l1-5Zm-1 7h12l-1.2 6H7.2L6 11Zm2 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/>
                        </svg>
                        <span>{{ __('Panier') }}</span>
                    </span>
                    @if(($cartCount ?? 0) > 0)
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-xs font-semibold rounded-full bg-blue-600 text-white dark:bg-blue-500">
                            {{ ($cartCount ?? 0) > 99 ? '99+' : ($cartCount ?? 0) }}
                        </span>
                    @endif
                </span>
            </x-responsive-nav-link>

            <!-- Mes commandes (mobile) -->
            @auth
                <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                    {{ __('Mes commandes') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Settings -->
        @auth
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ auth()->user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-4 border-t border-gray-200 dark:border-gray-600">
                <x-responsive-nav-link :href="route('login')">
                    {{ __('Se connecter') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')">
                    {{ __('Créer un compte') }}
                </x-responsive-nav-link>
            </div>
        @endauth
    </div>
</nav>
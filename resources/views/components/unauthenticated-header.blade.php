<header x-data="{ open: false }" class="bg-white w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo -->
            <div class="shrink-0 flex items-center text-black/50">
                <a href="{{ \App\Helpers\RouteHelper::localizedRoute('localized.welcome') }}">
                    <x-application-logo class="w-full h-auto max-w-[40px] sm:max-w-[60px] fill-current" />
                </a>
            </div>

            <!-- Desktop navigation -->
            @if (Route::has('login'))
                <div class="hidden sm:flex items-center space-x-4">
                    <a href="{{ \App\Helpers\RouteHelper::localizedRoute('localized.welcome') }}" class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]">
                        {{ __('Welcome') }}
                    </a>

                    <a href="{{ localizedRoute('localized.create-piggy-bank.step-1') }}" class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]">
                        {{ __('Create New Piggy Bank') }}
                    </a>

                    <div class="custom-language-dropdown">
                        <x-language-dropdown />
                    </div>

                    @auth
                        <a href="{{ localizedRoute('localized.dashboard') }}" class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ localizedRoute('localized.login') }}" class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]">
                            {{ __('Log in') }}
                        </a>

                        @if (Route::has('localized.register'))
                            <a href="{{ localizedRoute('localized.register') }}" class="rounded-md px-3 py-2 text-black/50 ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden focus-visible:ring-[#FF2D20]">
                                {{ __('Register') }}
                            </a>
                        @endif
                    @endauth
                </div>
            @endif

            <!-- Mobile Menu Button -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 focus:text-gray-600 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5M3.75 18.75h16.5"/>
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="sm:hidden w-full bg-white border-t border-gray-200">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('localized.welcome', ['locale' => app()->getLocale()])">
                {{ __('Welcome') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="localizedRoute('localized.create-piggy-bank.step-1')" :active="request()->routeIs('create-piggy-bank.*')">
                {{ __('Create New Piggy Bank') }}
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="localizedRoute('localized.dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="localizedRoute('localized.login')" :active="request()->routeIs('localized.login')">
                    {{ __('Log in') }}
                </x-responsive-nav-link>
                @if (Route::has('localized.register'))
                    <x-responsive-nav-link :href="localizedRoute('localized.register')" :active="request()->routeIs('localized.register')">
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                @endif
            @endauth

            <!-- Language Switch -->
            <div class="custom-language-dropdown">
                <x-language-dropdown />
            </div>
        </div>
    </div>
</header>

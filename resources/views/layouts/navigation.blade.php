
<nav x-data="{ open: false }"
     class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @auth
                        <a href="{{ localizedRoute('localized.piggy-banks.index') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-900"/>
                        </a>
                    @else
                        <a href="{{ localizedRoute('localized.welcome') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-900"/>
                        </a>
                    @endauth

                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    @auth
                        <x-nav-link href="{{ localizedRoute('localized.dashboard') }}"
                                    :active="request()->routeIs('localized.dashboard.*')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                        <x-nav-link href="{{ localizedRoute('localized.piggy-banks.index') }}"
                                    :active="request()->routeIs('localized.piggy-banks.index.*')">
                        {{ __('My Piggy Banks') }}
                    </x-nav-link>
                    @endauth

                        <x-nav-link href="{{ route('localized.create-piggy-bank.step-1', ['locale' => app()->getLocale()]) }}"
                                    :active="request()->routeIs('localized.create-piggy-bank.*')">
                        {{ __('Create New Piggy Bank') }}
                    </x-nav-link>
                        <x-nav-link href="{{ localizedRoute('localized.welcome') }}"
                                    :active="request()->routeIs('localized.welcome')">
                        {{ __('Welcome') }}
                    </x-nav-link>
                </div>


            </div>

            @auth
            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right"
                            width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-600 bg-white hover:text-gray-900 focus:outline-hidden transition ease-in-out duration-150 cursor-pointer">
                            {{ Auth::user()?->name ?? '' }}

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4"
                                     xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">

                        <x-dropdown-link href="{{ localizedRoute('localized.profile.edit') }}">
                        {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Language Switch -->
                        <x-language-dropdown/>

                        <!-- Currency switch -->
                        <x-currency-dropdown/>

                        <!-- Get Help Link -->
                        <x-dropdown-link href="#" id="getHelpBtn">
                            {{ __('Get Help') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ localizedRoute('localized.logout') }}">
                            @csrf

                            <x-dropdown-link href="{{ localizedRoute('localized.logout') }}"
                                             onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            @endauth

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 focus:text-gray-600 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6"
                         stroke="currentColor"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="1.5">
                        <path :class="{'hidden': open, 'inline-flex': ! open }"
                              class="inline-flex"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M3.75 5.25h16.5M3.75 12h16.5M3.75 18.75h16.5"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }"
                              class="hidden"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}"
         class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">

            @auth
                <x-responsive-nav-link href="{{ localizedRoute('localized.dashboard') }}"
                                       :active="request()->routeIs('localized.dashboard.*')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ localizedRoute('localized.piggy-banks.index') }}"
                                       :active="request()->routeIs('localized.piggy-banks.index.*')">
                {{ __('My Piggy Banks') }}
            </x-responsive-nav-link>
            @endauth

                <x-responsive-nav-link href="{{ route('localized.create-piggy-bank.step-1', ['locale' => app()->getLocale()]) }}"
                                       :active="request()->routeIs('localized.create-piggy-bank.*')">
                    {{ __('Create New Piggy Bank') }}
                </x-responsive-nav-link>

            <x-responsive-nav-link href="{{ localizedRoute('localized.welcome') }}"
                                   :active="request()->routeIs('localized.welcome')">
                {{ __('Welcome') }}
            </x-responsive-nav-link>
        </div>


        @auth
        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-900">{{ Auth::user()->name ?? '' }}</div>
                <div class="font-medium text-sm text-gray-600">{{ Auth::user()->email ?? '' }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ localizedRoute('localized.profile.edit') }}">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Language Switch -->
                <x-dropdown align="left"
                            width="48">
                    <x-slot name="trigger">
                        <div class="px-4 py-2 text-gray-600 cursor-pointer flex justify-between items-center">
                            <span class="flex items-center gap-2">
                                {{ __('Language') }}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
                                </svg>
                            </span>
                            <svg class="h-4 w-4 transition-transform"
                                 :class="{ 'rotate-180': open }"
                                 xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 20 20"
                                 fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M5.23 7.23a.75.75 0 011.06 0L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 010-1.06z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </x-slot>

                    <x-slot name="content">
                        @foreach (config('app.available_languages') as $language => $locale)
                            <x-responsive-nav-link :href="route('global.language.switch', ['locale' => $locale])"
                                                   :class="App::getLocale() == $locale ? 'font-bold text-gray-900' : ''">
                                {{ __($language) }}
                            </x-responsive-nav-link>
                        @endforeach
                    </x-slot>
                </x-dropdown>


                <!-- Currency Switch -->
                <x-dropdown align="left"
                            width="48">
                    <x-slot name="trigger">
                        <div class="px-4 py-2 text-gray-600 cursor-pointer flex justify-between items-center">
                            <span class="flex items-center gap-2">
                                {{ __('Currency') }}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.3" stroke="currentColor" class="size-5 ml-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                </svg>
                            </span>
                            <svg class="h-4 w-4 transition-transform"
                                 :class="{ 'rotate-180': open }"
                                 xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 20 20"
                                 fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M5.23 7.23a.75.75 0 011.06 0L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 010-1.06z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </x-slot>

                    <x-slot name="content">
                        @foreach (config('app.currencies') as $currency => $currencyData)
                            <x-responsive-nav-link :href="route('global.currency.switch', ['currency' => $currency])"
                                                   :class="(auth()->check() ? auth()->user()->currency : session('currency', config('app.default_currency'))) == $currency ? 'font-bold text-gray-900' : ''">
                                {{ __($currencyData['name']) }}
                            </x-responsive-nav-link>
                        @endforeach
                    </x-slot>
                </x-dropdown>


                <!-- Get Help Link (Mobile) -->
                <x-responsive-nav-link href="#" id="getHelpBtnMobile">
                    {{ __('Get Help') }}
                </x-responsive-nav-link>


                <!-- Authentication -->
                <form method="POST"
                      action="{{ localizedRoute('localized.logout') }}">
                    @csrf
                    <x-responsive-nav-link href="{{ localizedRoute('localized.logout') }}"
                                           onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endauth


    </div>

    @vite(['resources/js/help-popup.js'])
</nav>

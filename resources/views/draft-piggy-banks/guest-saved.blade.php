@php use App\Helpers\MoneyFormatHelper; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Draft Saved') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-8 px-6 text-center">

                    {{-- Success Icon --}}
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                        <svg class="h-8 w-8 text-green-600"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>

                    {{-- Success Message --}}
                    <h1 class="text-2xl font-semibold text-gray-900 mb-2">
                        {{ __('Your draft has been saved!') }}
                    </h1>

                    <p class="text-gray-600 mb-6">
                        {{ __('Register or login to view and manage your saved drafts.') }}
                    </p>

                    {{-- Draft Summary --}}
                    <div class="bg-gray-50 rounded-lg p-4 mb-8 inline-block">
                        <p class="text-sm text-gray-500">{{ __('Draft') }}</p>
                        <p class="font-semibold text-gray-900">{{ $draftInfo['name'] }}</p>
                        <p class="text-gray-700">
                            {{ MoneyFormatHelper::format($draftInfo['price'], $draftInfo['currency']) }}
                        </p>
                    </div>

                    {{-- CTA Buttons --}}
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ localizedRoute('localized.register') }}">
                            <x-primary-button type="button"
                                              class="w-full sm:w-auto justify-center">
                                {{ __('Register') }}
                            </x-primary-button>
                        </a>

                        <a href="{{ localizedRoute('localized.login') }}">
                            <x-secondary-button type="button"
                                                class="w-full sm:w-auto justify-center">
                                {{ __('Login') }}
                            </x-secondary-button>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

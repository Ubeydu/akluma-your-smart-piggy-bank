@php
    use App\Helpers\MoneyFormatHelper;
    use Carbon\Carbon;
@endphp
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

                    {{-- Draft Summary Card --}}
                    <div class="bg-gray-50 rounded-lg p-5 mb-8 text-left max-w-md mx-auto">

                        {{-- Header: Image + Name + Strategy --}}
                        <div class="flex items-start mb-4">
                            <div class="mr-4 w-16 h-16 shrink-0">
                                <img src="{{ str_starts_with($draftInfo['preview_image'], 'http') ? $draftInfo['preview_image'] : asset($draftInfo['preview_image']) }}"
                                     alt="{{ $draftInfo['name'] }}"
                                     class="w-full h-full object-cover rounded-lg shadow-xs">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-bold text-gray-900 mb-1 truncate">{{ $draftInfo['name'] }}</h3>
                                @if(isset($draftInfo['strategy']))
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ __('draft.strategy.' . $draftInfo['strategy']) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Info Grid --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs text-gray-500 block">{{ __('price') }}</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    {{ MoneyFormatHelper::format($draftInfo['price'], $draftInfo['currency']) }}
                                </span>
                            </div>

                            @if(isset($draftInfo['frequency']))
                                <div>
                                    <span class="text-xs text-gray-500 block">{{ __('Saving Frequency') }}</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ ucfirst(__(strtolower($draftInfo['frequency']))) }}
                                    </span>
                                </div>
                            @endif

                            <div>
                                <span class="text-xs text-gray-500 block">{{ __('Target Amount') }}</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    @php
                                        $targetAmount = $draftInfo['price'];
                                        if (isset($draftInfo['starting_amount']) && $draftInfo['starting_amount'] > 0) {
                                            $targetAmount = $draftInfo['price'] - $draftInfo['starting_amount'];
                                        }
                                        echo MoneyFormatHelper::format($targetAmount, $draftInfo['currency']);
                                    @endphp
                                </span>
                            </div>

                            @if(isset($draftInfo['target_date']) && $draftInfo['target_date'])
                                <div>
                                    <span class="text-xs text-gray-500 block">{{ __('Target Date') }}</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        @php
                                            $targetDate = $draftInfo['target_date'];
                                            if ($targetDate instanceof Carbon) {
                                                echo $targetDate->translatedFormat('d F Y');
                                            } else {
                                                echo Carbon::parse($targetDate)->translatedFormat('d F Y');
                                            }
                                        @endphp
                                    </span>
                                </div>
                            @endif

                            @if(isset($draftInfo['starting_amount']) && $draftInfo['starting_amount'] > 0)
                                <div>
                                    <span class="text-xs text-gray-500 block">{{ __('starting_amount') }}</span>
                                    <span class="text-sm font-semibold text-gray-900">
                                        {{ MoneyFormatHelper::format($draftInfo['starting_amount'], $draftInfo['currency']) }}
                                    </span>
                                </div>
                            @endif
                        </div>
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

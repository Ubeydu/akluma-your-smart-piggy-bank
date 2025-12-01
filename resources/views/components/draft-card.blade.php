@props(['draft'])

@php
    use Carbon\Carbon;
@endphp

<a href="{{ localizedRoute('localized.draft-piggy-banks.show', ['draft' => $draft->id]) }}"
   class="block text-current hover:no-underline">
    <div class="p-5 border rounded-lg shadow-md bg-white hover:bg-gray-50 transition-all duration-300">

        {{-- Header Section --}}
        <div class="flex items-start mb-4">
            {{-- Preview Image --}}
            <div class="mr-4 w-16 h-16 shrink-0">
                <img src="{{ str_starts_with($draft->preview_image, 'http') ? $draft->preview_image : asset($draft->preview_image) }}"
                     alt="{{ $draft->name }}"
                     class="w-full h-full object-cover rounded-lg shadow-xs">
            </div>

            {{-- Title and Strategy Badge --}}
            <div class="flex-1">
                <h3 class="text-lg font-bold text-gray-900 mb-1 truncate">{{ $draft->name }}</h3>
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    {{ __('draft.strategy.' . $draft->strategy) }}
                </span>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-xs text-gray-500 block">{{ __('price') }}</span>
                <span class="text-sm font-semibold text-gray-900">
                    {{ \App\Helpers\MoneyFormatHelper::format($draft->price, $draft->currency) }}
                </span>
            </div>

            <div>
                <span class="text-xs text-gray-500 block">{{ __('Saving Frequency') }}</span>
                <span class="text-sm font-semibold text-gray-900">
                    {{ ucfirst(__(strtolower($draft->frequency))) }}
                </span>
            </div>

            <div>
                <span class="text-xs text-gray-500 block">{{ __('Target Amount') }}</span>
                <span class="text-sm font-semibold text-gray-900">
                    @php
                        $startingAmount = $draft->step1_data['starting_amount'] ?? null;
                        $targetAmount = $draft->price;

                        // If starting amount exists, subtract it from price
                        if ($startingAmount && is_array($startingAmount) && isset($startingAmount['amount']) && $startingAmount['amount'] > 0) {
                            $targetAmount = $draft->price - $startingAmount['amount'];
                        }

                        echo \App\Helpers\MoneyFormatHelper::format($targetAmount, $draft->currency);
                    @endphp
                </span>
            </div>

            <div>
                <span class="text-xs text-gray-500 block">{{ __('Target Date') }}</span>
                <span class="text-sm font-semibold text-gray-900">
                    @php
                        if ($draft->strategy === 'pick-date') {
                            $targetDate = $draft->step3_data['date'] ?? null;
                            if ($targetDate) {
                                if ($targetDate instanceof Carbon) {
                                    echo $targetDate->translatedFormat('d F Y');
                                } else {
                                    echo Carbon::parse($targetDate)->translatedFormat('d F Y');
                                }
                            } else {
                                echo '-';
                            }
                        } else {
                            // enter-saving-amount strategy
                            $targetDateString = $draft->step3_data['target_dates'][$draft->frequency]['target_date'] ?? null;
                            if ($targetDateString) {
                                echo Carbon::parse($targetDateString)->translatedFormat('d F Y');
                            } else {
                                echo '-';
                            }
                        }
                    @endphp
                </span>
            </div>

            @if(isset($draft->step1_data['starting_amount']) && $draft->step1_data['starting_amount'])
                <div>
                    <span class="text-xs text-gray-500 block">{{ __('starting_amount') }}</span>
                    <span class="text-sm font-semibold text-gray-900">
                        @php
                            $startingAmount = $draft->step1_data['starting_amount'];
                            if (is_array($startingAmount) && isset($startingAmount['amount'])) {
                                echo \App\Helpers\MoneyFormatHelper::format(
                                    $startingAmount['amount'],
                                    $draft->currency
                                );
                            }
                        @endphp
                    </span>
                </div>
            @endif
        </div>
    </div>
</a>

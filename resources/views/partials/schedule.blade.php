<div id="schedule-container" class="mt-8 rounded-lg p-4 border border-gray-200" data-piggy-bank-status="{{ $piggyBank->status }}">

    @if($piggyBank->status === 'paused')
        <div class="mb-4 p-4 bg-yellow-50 border-l-4 border-yellow-400">
            <div class="flex">
                <div class="shrink-0">
                    <!-- Heroicon name: solid/exclamation -->
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-base text-yellow-700">
                        {{ __('paused_message') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @php
        // Calculate schedule statistics for active (non-archived) scheduled savings only
        $totalScheduledCount = $piggyBank->scheduledSavings()
            ->where('archived', false)
            ->count();

        $savedCount = $piggyBank->scheduledSavings()
            ->where('archived', false)
            ->where('status', 'saved')
            ->count();

        $pendingCount = $piggyBank->scheduledSavings()
            ->where('archived', false)
            ->where('status', 'pending')
            ->count();
    @endphp

    <div class="mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="text-lg font-medium text-gray-900">{{ __('Saving Schedule') }}</h2>
            <div class="flex gap-2 text-xs">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-800">
                    <span class="font-semibold">{{ __('Total') }}:</span>
                    <span class="ml-1">{{ $totalScheduledCount }}</span>
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-green-100 text-green-800">
                    <span class="font-semibold">{{ __('Saved') }}:</span>
                    <span class="ml-1">{{ $savedCount }}</span>
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-orange-100 text-orange-800">
                    <span class="font-semibold">{{ __('Pending') }}:</span>
                    <span class="ml-1">{{ $pendingCount }}</span>
                </span>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 {{ in_array($piggyBank->status, ['paused', 'cancelled', 'done']) ? 'opacity-50' : '' }}">
            <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="pl-4 pr-1 py-3 text-left text-xs font-medium text-gray-500 tracking-wider break-words max-w-[40px]">
                    {{ __('in_piggy_bank') }}
                </th>
                <th scope="col" class="px-1 py-3 text-left text-xs font-medium text-gray-500 tracking-wider break-words max-w-[40px]">
                    {{ __('Saving #') }}
                </th>
                <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    {{ __('Date') }}
                </th>
                <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    {{ __('Amount') }}
                </th>
                <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    {{ __('Save Amount') }}
                </th>
                <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    {{ __('Status') }}
                </th>
                <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                    <span class="inline-flex items-center gap-1">
                        {{ __('Last Modified') }}
                        <span x-data="{ showTooltip: false }" class="relative cursor-help">
                            <svg @mouseenter="showTooltip = true"
                                @mouseleave="showTooltip = false"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                class="w-4 h-4 text-gray-400 hover:text-gray-600 transition-colors duration-200">
                                <path stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <div x-show="showTooltip"
                                x-cloak
                                class="absolute z-10 w-64 px-4 py-2 mt-2 text-sm bg-gray-900 text-white rounded-lg shadow-lg -translate-x-1/2 left-1/2"
                                role="tooltip">
                                {{ __('Updated by user actions (marking as saved/pending) or system changes (schedule recalculations)') }}
                            </div>
                        </span>
                    </span>
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach(($scheduledSavings ?? $piggyBank->scheduledSavings()->paginate(50)->setPath(localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id]))) as $saving)
                <tr class="{{ $saving->status === 'saved' ? 'bg-green-50' : 'bg-orange-50' }}">
                    <td class="pl-4 pr-1 py-4 whitespace-normal text-sm text-gray-900">
                        <input type="checkbox"
                               class="scheduled-saving-checkbox rounded-sm border-gray-300 text-blue-600 shadow-xs focus:border-blue-300 focus:ring-3 focus:ring-blue-200 focus:ring-opacity-50 {{ in_array($piggyBank->status, ['paused', 'cancelled', 'done']) ? 'cursor-not-allowed' : 'cursor-pointer' }}"
                               {{ $saving->status === 'saved' ? 'checked' : '' }}
                               {{ in_array($piggyBank->status, ['paused', 'cancelled', 'done']) ? 'disabled' : '' }}
                               data-saving-id="{{ $saving->id }}"
                               data-piggy-bank-id="{{ $piggyBank->id }}"
                               data-amount="{{ $saving->saved_amount ?? $saving->amount }}">
                    </td>
                    <td class="px-1 py-4 whitespace-normal text-sm font-medium text-gray-900">
                        {{ $saving->saving_number }}
                    </td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $saving->saving_date->translatedFormat('d F Y') }}
                    </td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ \App\Helpers\MoneyFormatHelper::format($saving->amount, $piggyBank->currency) }}
                    </td>
                    @php
                        $currencyHasDecimals = \App\Helpers\CurrencyHelper::hasDecimalPlaces($piggyBank->currency);
                    @endphp
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($saving->status === 'pending' && !in_array($piggyBank->status, ['paused', 'cancelled', 'done']))
                            <input type="number"
                                   class="save-amount-input w-20 px-2 py-1 border border-gray-300 rounded-sm text-sm focus:ring-blue-500 focus:border-blue-500"
                                   data-saving-id="{{ $saving->id }}"
                                   data-currency-has-decimals="{{ $currencyHasDecimals ? '1' : '0' }}"
                                   value="{{ $currencyHasDecimals ? $saving->amount : (int) $saving->amount }}"
                                   min="{{ $currencyHasDecimals ? '0.01' : '1' }}"
                                   step="{{ $currencyHasDecimals ? '0.01' : '1' }}"
                                   >
                        @elseif($saving->status === 'saved')
                            {{ \App\Helpers\MoneyFormatHelper::format($saving->saved_amount ?? $saving->amount, $piggyBank->currency) }}
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ __(strtolower($saving->status)) }}
                    </td>
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $saving->last_modified_at?->timezone(auth()->user()->timezone ?? 'UTC')->locale(app()->getLocale())->isoFormat('L LT') ?? '-' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>


    <div class="mt-4">
        {{ ($scheduledSavings ?? $piggyBank->scheduledSavings()->paginate(50)->setPath(localizedRoute('localized.piggy-banks.show', ['piggy_id' => $piggyBank->id])))->links() }}
    </div>

</div>

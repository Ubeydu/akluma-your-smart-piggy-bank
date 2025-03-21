<div class="mt-8" data-piggy-bank-status="{{ $piggyBank->status }}">

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

    <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Saving Schedule') }}</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 {{ in_array($piggyBank->status, ['paused', 'cancelled', 'done']) ? 'opacity-50' : '' }}">
            <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words max-w-[40px]">
                    {{ __('in_piggy_bank') }}
                </th>
                <th scope="col" class="px-1 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider break-words max-w-[40px]">
                    {{ __('Saving #') }}
                </th>
                <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ __('Date') }}
                </th>
                <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ __('Amount') }}
                </th>
                <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ __('Status') }}
                </th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($piggyBank->scheduledSavings()->paginate(50) as $saving)
                <tr>
                    <td class="px-1 py-4 whitespace-normal text-sm text-gray-900">
                        <input type="checkbox"
                               class="rounded-sm border-gray-300 text-blue-600 shadow-xs focus:border-blue-300 focus:ring-3 focus:ring-blue-200 focus:ring-opacity-50 {{ in_array($piggyBank->status, ['paused', 'cancelled', 'done']) ? 'cursor-not-allowed' : 'cursor-pointer' }}"
                               {{ $saving->status === 'saved' ? 'checked' : '' }}
                               data-saving-id="{{ $saving->id }}"
                               data-piggy-bank-id="{{ $piggyBank->id }}"
                               data-amount="{{ $saving->amount }}">
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
                    <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ __(strtolower($saving->status)) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>


    <div class="mt-4">
        {{ $piggyBank->scheduledSavings()->paginate(50)->links() }}
    </div>

</div>

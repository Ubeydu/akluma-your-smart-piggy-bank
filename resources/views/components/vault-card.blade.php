@props(['vault'])

<a href="{{ localizedRoute('localized.vaults.show', ['vault_id' => $vault->id]) }}" class="block text-current hover:no-underline w-full">
    <div class="p-5 border rounded-lg shadow-md bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-300 cursor-pointer hover:shadow-lg hover:scale-105 w-full h-full flex flex-col min-h-[250px] max-h-[350px]">
        <div class="flex justify-between items-start mb-2">
            <h3 class="font-semibold text-lg truncate">{{ $vault->name }}</h3>
            <span class="text-xs text-gray-400 dark:text-gray-300">#{{ $vault->id }}</span>
        </div>

        <div class="mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Saved') }}</p>

            <div class="text-xl font-bold text-green-600">
                @if(empty($vault->total_saved))
                    0
                @elseif(count($vault->total_saved) === 1)
                    @php
                        $currency = array_keys($vault->total_saved)[0];
                        $amount = array_values($vault->total_saved)[0];
                    @endphp
                    {{ number_format($amount, 2) }} {{ $currency }}
                @else
                    @foreach($vault->total_saved as $currency => $amount)
                        <div>{{ number_format($amount, 2) }} {{ $currency }}</div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Connected Piggy Banks') }}</p>
            <p class="font-medium">{{ $vault->piggyBanks->count() }}</p>
        </div>

        @if($vault->details)
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 overflow-hidden line-clamp-3">{{ Str::limit($vault->details, 100) }}</p>
        @endif

        <div class="flex space-x-2 mt-auto">
            <span class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                {{ __('View') }}
            </span>
            <a href="{{ localizedRoute('localized.vaults.edit', ['vault_id' => $vault->id]) }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm"
               onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ localizedRoute('localized.vaults.edit', ['vault_id' => $vault->id]) }}';">
                {{ __('Edit') }}
            </a>
        </div>
    </div>
</a>

<div class="p-4 border rounded-lg shadow bg-rose-50 hover:bg-rose-100 transition-colors duration-300">
    <h3 class="text-lg font-bold">{{ $piggyBank->name }}</h3>
    <p class="text-sm text-gray-600 font-medium">{{ __('price') }}: {{ $piggyBank->price }}</p>
    <p class="text-sm text-gray-600 font-medium">{{ __('starting_amount') }}: {{ $piggyBank->starting_amount }}</p>
    <p class="text-sm text-gray-600 font-medium">{{ __('remaining_amount') }}: {{ $piggyBank->price - $piggyBank->starting_amount }}</p>
    <p class="text-sm text-gray-600 font-medium">{{ __('purchase_date') }}: {{ $piggyBank->purchase_date }}</p>
</div>

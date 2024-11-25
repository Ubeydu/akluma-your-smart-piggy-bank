<div class="p-4 border rounded-lg shadow">
    <h3 class="text-lg font-bold">{{ $piggyBank->name }}</h3>
    <p class="text-sm text-gray-600">{{ __('price') }}: {{ $piggyBank->price }}</p>
    <p class="text-sm text-gray-600">{{ __('starting_amount') }}: {{ $piggyBank->starting_amount }}</p>
    <p class="text-sm text-gray-600">{{ __('remaining_amount') }}: {{ $piggyBank->price - $piggyBank->starting_amount }}</p>
    <p class="text-sm text-gray-600">{{ __('purchase_date') }}: {{ $piggyBank->purchase_date }}</p>
</div>

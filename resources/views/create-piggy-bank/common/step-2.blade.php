<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Create New Piggy Bank') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="py-4 px-6">
                    <h1 class="text-lg font-semibold mb-4">{{ __('Step 2 of 3') }}</h1>
                    <p class="text-gray-600 mb-6">{{ __('Choose your strategy') }}</p>

                    <div class="grid gap-6 mb-6">
                        <!-- Pick Date Strategy -->
                        <form action="{{ route('create-piggy-bank.choose-strategy') }}" method="POST">
                            @csrf
                            <input type="hidden" name="strategy" value="pick-date">
                            <button type="submit" class="p-6 text-left border rounded-lg hover:border-indigo-500 focus:outline-none focus:border-indigo-500 transition-colors duration-200">
                                <h3 class="text-xl font-semibold mb-3">{{ __('Pick Date') }}</h3>
                                <p class="text-gray-600">{{ __('pick_date_strategy_definition') }}</p>
                            </button>
                        </form>

                        <!-- Enter Saving Amount Strategy -->
                        <form action="{{ route('create-piggy-bank.choose-strategy') }}" method="POST">
                            @csrf
                            <input type="hidden" name="strategy" value="enter-saving-amount">
                            <button type="submit" class="p-6 text-left border rounded-lg hover:border-indigo-500 focus:outline-none focus:border-indigo-500 transition-colors duration-200">
                                <h3 class="text-xl font-semibold mb-3">{{ __('Enter Saving Amount') }}</h3>
                                <p class="text-gray-600">{{ __('enter_saving_amount_strategy_definition') }}</p>
                            </button>
                        </form>
                    </div>


                    <!-- Action Buttons -->
                    <div class="flex justify-between mt-6">
                        <x-danger-button type="button" onclick="if(confirm('{{ __('Are you sure you want to cancel?') }}')) { window.location='{{ route('dashboard') }}'; }">
                            {{ __('Cancel') }}
                        </x-danger-button>
                        <x-secondary-button type="button" onclick="window.location='{{ route('create-piggy-bank.step-1') }}'">
                            {{ __('Previous') }}
                        </x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

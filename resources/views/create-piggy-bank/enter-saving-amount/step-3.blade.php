<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Create New Piggy Bank') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-4 px-6">
                    <h1 class="text-lg font-semibold mb-4">{{ __('Step 3 of 3') }}</h1>


                <p>Enter saving amount Step 3</p>


                <!-- Action Buttons -->
                <div class="flex justify-between mt-6">
                    <x-danger-button type="button" onclick="if(confirm('{{ __('Are you sure you want to cancel?') }}')) { window.location='{{ route('dashboard') }}'; }">
                        {{ __('Cancel') }}
                    </x-danger-button>
                    <x-secondary-button type="button" onclick="window.location='{{ route('create-piggy-bank.step-2.get') }}'">
                        {{ __('Previous') }}
                    </x-secondary-button>
                    {{--                        <x-primary-button id="nextButton" type="submit">--}}
                    {{--                            {{ __('Next') }}--}}
                    {{--                        </x-primary-button>--}}
                </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('My Piggy Banks') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="py-4 px-4">
                    <h1 class="text-lg font-semibold">{{ __('My Piggy Banks') }}</h1>

                    <div class="mt-4">
                        @if($piggyBanks->isEmpty())
                            <x-empty-state
                                title="No Piggy Banks Yet"
                                message="Start saving for your goals today by creating your first piggy bank!"
                                buttonText="Create Your Piggy Bank"
                                buttonLink="{{ route('piggy-banks.index') }}"
                            />
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($piggyBanks as $piggyBank)
                                    <x-piggy-bank-card :piggyBank="$piggyBank" />
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    {{-- Temporary debug output to see the structure --}}
    @dump(session('debug_summary'))


    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Create New Piggy Bank') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="py-4 px-6">
                    <h1 class="text-lg font-semibold mb-4">{{ __('Summary') }}</h1>


                    <div class="space-y-6">
                        @php
                            $debugData = session('debug_summary');
                        @endphp

                        @if($debugData)
                            {{-- Step 1 Data --}}
                            @if(isset($debugData['pick_date_step1']))
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h2 class="font-semibold text-lg mb-3">Step 1 Data</h2>
                                    <div class="space-y-2">
                                        @foreach($debugData['pick_date_step1'] as $key => $value)
                                            <div class="flex">
                                                <span class="font-medium w-32">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Step 3 Data --}}
                            @if(isset($debugData['pick_date_step3']))
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h2 class="font-semibold text-lg mb-3">Step 3 Data</h2>
                                    <div class="space-y-2">
                                        <div class="flex">
                                            <span class="font-medium w-32">Date:</span>
                                            <span>{{ $debugData['pick_date_step3']['date'] }}</span>
                                        </div>

                                        @php
                                            // Get the selected frequency from the data
                                            $selectedFrequency = $debugData['pick_date_step3']['selected_frequency'];
                                            // Get the calculations data for the selected frequency
                                            $selectedCalculation = $debugData['pick_date_step3']['calculations'][$selectedFrequency];
                                        @endphp

                                        {{-- Display only the selected frequency's calculations --}}
                                        <div class="mt-4">
                                            <span class="font-medium">Your Saving Plan:</span>
                                            <div class="ml-4 mt-2">
                                                <div class="border-b pb-4">
                                                    <h3 class="font-medium text-lg mb-2">{{ ucfirst($selectedFrequency) }}</h3>
                                                    <div class="ml-4 space-y-1">
                                                        <p>Amount: {{ $selectedCalculation['amount']['formatted_amount'] }} {{ $selectedCalculation['amount']['currency'] }}</p>
                                                        <p>Frequency: {{ $selectedCalculation['frequency'] }}</p>
                                                        <p>Target Amount: {{ $selectedCalculation['target_amount']['formatted_amount'] }} {{ $selectedCalculation['target_amount']['currency'] }}</p>
                                                        <p>Extra Savings: {{ $selectedCalculation['extra_savings']['formatted_amount'] }} {{ $selectedCalculation['extra_savings']['currency'] }}</p>
                                                        <p>Total Savings: {{ $selectedCalculation['total_savings']['formatted_amount'] }} {{ $selectedCalculation['total_savings']['currency'] }}</p>

                                                        @if(isset($paymentSchedule))
                                                            <div class="mt-6">
                                                                <h4 class="font-medium text-lg mb-3">{{ __('Payment Schedule') }}</h4>
                                                                <div class="overflow-x-auto">
                                                                    <table class="min-w-full divide-y divide-gray-200">
                                                                        <thead class="bg-gray-50">
                                                                        <tr>
                                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                                {{ __('Payment #') }}
                                                                            </th>
                                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                                {{ __('Date') }}
                                                                            </th>
                                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                                {{ __('Amount') }}
                                                                            </th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                                        @foreach($paymentSchedule as $payment)
                                                                            <tr>
                                                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                                    {{ $payment['payment_number'] }}
                                                                                </td>
                                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                                    {{ $payment['formatted_date'] }}
                                                                                </td>
                                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                                    {{ $payment['amount'] }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        @endif



                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-gray-500 italic">
                                No debug data available in session.
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between mt-6">
                        <x-danger-button type="button" onclick="if(confirm('{{ __('Are you sure you want to cancel?') }}')) { window.location='{{ route('dashboard') }}'; }">
                            {{ __('Cancel') }}
                        </x-danger-button>
                        <x-secondary-button type="button" onclick="window.location='{{ route('create-piggy-bank.pick-date.step-3') }}'">
                            {{ __('Previous') }}
                        </x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

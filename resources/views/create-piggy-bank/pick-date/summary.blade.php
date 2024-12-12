<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Create New Piggy Bank') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="py-6 px-8">
                    <h1 class="text-2xl font-semibold mb-6">{{ __('Summary') }}</h1>

                    <!-- Product Information Section -->
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Product Details') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Product Name') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ $summary['pick_date_step1']['name'] }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Product Price') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">
                                        {{-- Using the Money object's built-in formatting --}}
                                        {{ $summary['pick_date_step1']['price']->formatTo('en_US') }}
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Starting Amount') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">
                                        {{-- Using the Money object's built-in formatting --}}
                                        {{ $summary['pick_date_step1']['starting_amount']->formatTo('en_US') }}
                                    </p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Product Link') }}</h3>
                                    <a href="{{ $summary['pick_date_step1']['link'] }}" target="_blank" class="mt-1 text-base text-blue-600 hover:text-blue-800 break-all">{{ $summary['pick_date_step1']['link'] }}</a>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Notes') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ $summary['pick_date_step1']['details'] }}</p>
                                </div>
                            </div>

                            <div class="w-full md:w-48 mt-1">
                                <div class="aspect-square md:aspect-auto md:h-48 relative overflow-hidden rounded-lg shadow-sm bg-gray-50">
                                    <div class="relative w-full h-full">
                                        <img
                                            src="{{ $summary['pick_date_step1']['preview']['image'] }}"
                                            alt="{{ $summary['pick_date_step1']['name'] }}"
                                            class="absolute inset-0 w-full h-full object-contain"
                                        />
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Savings Plan Section -->
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Savings Plan') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Target Date') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ \Carbon\Carbon::parse($summary['pick_date_step3']['date'])->format('F j, Y') }}</p>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">{{ __('Saving Frequency') }}</h3>
                                    <p class="mt-1 text-base text-gray-900">{{ ucfirst($summary['pick_date_step3']['selected_frequency']) }}</p>
                                </div>

                                @if($dateMessage)
                                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                        <p class="text-blue-700">{{ $dateMessage }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-4">

                                @if($summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['message'])
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500">{{ __('Additional Information') }}</h3>
                                        <p class="mt-1 text-base text-gray-900">{{ $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['message'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary Section -->
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Financial Summary') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Extra Savings') }}</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['extra_savings']['formatted_amount'] }}
                                    {{ $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['extra_savings']['currency'] }}
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Target Amount') }}</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['target_amount']['formatted_amount'] }}
                                    {{ $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['target_amount']['currency'] }}
                                </p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-gray-500">{{ __('Total Savings') }}</h3>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['total_savings']['formatted_amount'] }}
                                    {{ $summary['pick_date_step3']['calculations'][$summary['pick_date_step3']['selected_frequency']]['total_savings']['currency'] }}
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- Payment Schedule Section (add this before the Action Buttons div) -->
                    @if(isset($paymentSchedule))
                        <div class="mb-8">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Payment Schedule') }}</h2>
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


                    <!-- Action Buttons -->
                    <div class="flex justify-between mt-8">
                        <x-danger-button type="button" onclick="if(confirm('{{ __('Are you sure you want to cancel?') }}')) { window.location='{{ route('dashboard') }}'; }">
                            {{ __('Cancel') }}
                        </x-danger-button>

                        <div class="space-x-4">
                            <x-secondary-button type="button" onclick="window.location='{{ route('create-piggy-bank.pick-date.step-3') }}'">
                                {{ __('Previous') }}
                            </x-secondary-button>

                            <x-primary-button type="button" onclick="window.location='{{ route('dashboard') }}'">
                                {{ __('Create Piggy Bank') }}
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


{{--<x-app-layout>--}}
{{--     Temporary debug output to see the structure--}}
{{--    @dump(session('debug_summary'))--}}

{{--    <pre>--}}
{{--    @php--}}
{{--        var_dump(session('debug_summary'));--}}
{{--    @endphp--}}
{{--    </pre>--}}


{{--    @php--}}
{{--        $debugSummary = session('debug_summary');--}}
{{--        echo '<pre>';--}}
{{--        print_r($debugSummary);--}}
{{--        echo '</pre>';--}}
{{--    @endphp--}}


{{--    <x-slot name="header">--}}
{{--        <h2 class="font-semibold text-xl text-gray-900 leading-tight">--}}
{{--            {{ __('Create New Piggy Bank') }}--}}
{{--        </h2>--}}
{{--    </x-slot>--}}

{{--    <div class="py-4 px-4">--}}
{{--        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">--}}
{{--            <div class="bg-white overflow-hidden shadow-sm rounded-lg">--}}
{{--                <div class="py-4 px-6">--}}
{{--                    <h1 class="text-lg font-semibold mb-4">{{ __('Summary') }}</h1>--}}


{{--                    <div class="space-y-6">--}}
{{--                        @php--}}
{{--                            $debugData = session('debug_summary');--}}
{{--                        @endphp--}}

{{--                        @if($debugData)--}}
{{--                             Step 1 Data--}}
{{--                            @if(isset($debugData['pick_date_step1']))--}}
{{--                                <div class="bg-gray-50 p-4 rounded-lg">--}}
{{--                                    <h2 class="font-semibold text-lg mb-3">Step 1 Data</h2>--}}
{{--                                    <div class="space-y-2">--}}
{{--                                        @foreach($debugData['pick_date_step1'] as $key => $value)--}}
{{--                                            <div class="flex">--}}
{{--                                                <span class="font-medium w-32">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>--}}
{{--                                                <span>{{ is_array($value) ? json_encode($value) : $value }}</span>--}}
{{--                                            </div>--}}
{{--                                        @endforeach--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            @endif--}}

{{--                             Step 3 Data--}}
{{--                            @if(isset($debugData['pick_date_step3']))--}}
{{--                                <div class="bg-gray-50 p-4 rounded-lg">--}}
{{--                                    <h2 class="font-semibold text-lg mb-3">Step 3 Data</h2>--}}
{{--                                    <div class="space-y-2">--}}
{{--                                        <div class="flex">--}}
{{--                                            <span class="font-medium w-32">Date:</span>--}}
{{--                                            <span>{{ $debugData['pick_date_step3']['date'] }}</span>--}}
{{--                                        </div>--}}

{{--                                        @php--}}
{{--                                            // Get the selected frequency from the data--}}
{{--                                            $selectedFrequency = $debugData['pick_date_step3']['selected_frequency'];--}}
{{--                                            // Get the calculations data for the selected frequency--}}
{{--                                            $selectedCalculation = $debugData['pick_date_step3']['calculations'][$selectedFrequency];--}}
{{--                                        @endphp--}}

{{--                                         Display only the selected frequency's calculations--}}
{{--                                        <div class="mt-4">--}}
{{--                                            <span class="font-medium">Your Saving Plan:</span>--}}
{{--                                            <div class="ml-4 mt-2">--}}
{{--                                                <div class="border-b pb-4">--}}
{{--                                                    <h3 class="font-medium text-lg mb-2">{{ ucfirst($selectedFrequency) }}</h3>--}}
{{--                                                    <div class="ml-4 space-y-1">--}}
{{--                                                        <p>Amount: {{ $selectedCalculation['amount']['formatted_amount'] }} {{ $selectedCalculation['amount']['currency'] }}</p>--}}
{{--                                                        <p>Frequency: {{ $selectedCalculation['frequency'] }}</p>--}}
{{--                                                        <p>Target Amount: {{ $selectedCalculation['target_amount']['formatted_amount'] }} {{ $selectedCalculation['target_amount']['currency'] }}</p>--}}
{{--                                                        <p>Extra Savings: {{ $selectedCalculation['extra_savings']['formatted_amount'] }} {{ $selectedCalculation['extra_savings']['currency'] }}</p>--}}
{{--                                                        <p>Total Savings: {{ $selectedCalculation['total_savings']['formatted_amount'] }} {{ $selectedCalculation['total_savings']['currency'] }}</p>--}}

{{--                                                        @if(isset($dateMessage))--}}
{{--                                                            <div class="mt-4 p-4 bg-blue-50 text-blue-700 rounded-lg">--}}
{{--                                                                {{ $dateMessage }}--}}
{{--                                                            </div>--}}
{{--                                                        @endif--}}

{{--                                                        @if(isset($paymentSchedule))--}}
{{--                                                            <div class="mt-6">--}}
{{--                                                                <h4 class="font-medium text-lg mb-3">{{ __('Payment Schedule') }}</h4>--}}
{{--                                                                <div class="overflow-x-auto">--}}
{{--                                                                    <table class="min-w-full divide-y divide-gray-200">--}}
{{--                                                                        <thead class="bg-gray-50">--}}
{{--                                                                        <tr>--}}
{{--                                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">--}}
{{--                                                                                {{ __('Payment #') }}--}}
{{--                                                                            </th>--}}
{{--                                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">--}}
{{--                                                                                {{ __('Date') }}--}}
{{--                                                                            </th>--}}
{{--                                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">--}}
{{--                                                                                {{ __('Amount') }}--}}
{{--                                                                            </th>--}}
{{--                                                                        </tr>--}}
{{--                                                                        </thead>--}}
{{--                                                                        <tbody class="bg-white divide-y divide-gray-200">--}}
{{--                                                                        @foreach($paymentSchedule as $payment)--}}
{{--                                                                            <tr>--}}
{{--                                                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">--}}
{{--                                                                                    {{ $payment['payment_number'] }}--}}
{{--                                                                                </td>--}}
{{--                                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">--}}
{{--                                                                                    {{ $payment['formatted_date'] }}--}}
{{--                                                                                </td>--}}
{{--                                                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">--}}
{{--                                                                                    {{ $payment['amount'] }}--}}
{{--                                                                                </td>--}}
{{--                                                                            </tr>--}}
{{--                                                                        @endforeach--}}
{{--                                                                        </tbody>--}}
{{--                                                                    </table>--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}
{{--                                                        @endif--}}



{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            @endif--}}
{{--                        @else--}}
{{--                            <div class="text-gray-500 italic">--}}
{{--                                No debug data available in session.--}}
{{--                            </div>--}}
{{--                        @endif--}}
{{--                    </div>--}}

{{--                    <!-- Action Buttons -->--}}
{{--                    <div class="flex justify-between mt-6">--}}
{{--                        <x-danger-button type="button" onclick="if(confirm('{{ __('Are you sure you want to cancel?') }}')) { window.location='{{ route('dashboard') }}'; }">--}}
{{--                            {{ __('Cancel') }}--}}
{{--                        </x-danger-button>--}}
{{--                        <x-secondary-button type="button" onclick="window.location='{{ route('create-piggy-bank.pick-date.step-3') }}'">--}}
{{--                            {{ __('Previous') }}--}}
{{--                        </x-secondary-button>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</x-app-layout>--}}

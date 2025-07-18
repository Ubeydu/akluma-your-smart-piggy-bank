@php use App\Helpers\MoneyFormatHelper; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative">

            <div class="mb-6">
            <!-- Money Left to Save -->
            <div class="p-5 border rounded-lg shadow-md bg-white relative">
                <h3 class="text-md font-bold text-gray-900 mb-3">{{ __('Left to Save') }}</h3>

                <div>
                    @if(!empty($leftToSaveData['amounts']))
                        @foreach($leftToSaveData['amounts'] as $currency => $amount)
                            <div class="mb-2">
                                <span class="text-2xl font-bold text-gray-900">
                                    {{ MoneyFormatHelper::format($amount, $currency) }}
                                </span>
                                                <span class="text-sm text-gray-500 ml-2">
                                    ({{ $leftToSaveData['counts'][$currency] ?? 0 }} {{ __('piggy banks') }})
                                </span>
                                @if(in_array($currency, ['XOF', 'XAF']))
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ __(config('app.currencies.' . $currency . '.name')) }}
                                    </div>
                                @endif
                                <div class="mt-2">
                                    <div class="w-full h-2.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="bg-indigo-500 h-full rounded-full"
                                             style="width: {{ $progressPercentages[$currency] ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 mt-1 block">{{ $progressPercentages[$currency] ?? 0 }}% {{ __('of goal completed') }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <span class="text-2xl font-bold text-gray-900">{{ __('No active savings') }}</span>
                    @endif
                </div>
            </div>
            </div>

            <!-- Dashboard content with blur overlay -->
            <div class="relative rounded-lg overflow-visible">
                <!-- Single Coming Soon Badge for the entire dashboard -->
                <span class="absolute right-0 z-20"
                      style="top: -12px;">
                <span class="inline-block bg-linear-to-r from-yellow-400 to-orange-500 text-sm text-gray-700 font-medium px-4 py-2 rounded-full shadow-lg"
                  style="background: linear-gradient(to right, #FBBF24, #F97316);">
                {{ __('Coming Soon ✨') }}
                </span>
                </span>

                <!-- No blur, just opacity -->
                <div class="absolute inset-0 bg-white/40 z-10"></div>

                <div class="py-4 px-4">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <!-- Metric Cards Section -->
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Overview') }}</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <!-- Piggy Bank Status Counts -->
                                <div class="p-5 border rounded-lg shadow-md bg-white">
                                    <h3 class="text-md font-bold text-gray-900 mb-3">{{ __('Piggy Bank Status') }}</h3>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="mb-2">
                                            <span class="text-xs text-gray-500 block">{{ __('Active') }}</span>
                                            <span class="text-sm font-semibold text-gray-900">3</span>
                                        </div>

                                        <div class="mb-2">
                                            <span class="text-xs text-gray-500 block">{{ __('Paused') }}</span>
                                            <span class="text-sm font-semibold text-gray-900">1</span>
                                        </div>

                                        <div class="mb-2">
                                            <span class="text-xs text-gray-500 block">{{ __('Done') }}</span>
                                            <span class="text-sm font-semibold text-gray-900">2</span>
                                        </div>

                                        <div class="mb-2">
                                            <span class="text-xs text-gray-500 block">{{ __('Cancelled') }}</span>
                                            <span class="text-sm font-semibold text-gray-900">0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Money Saved -->
                                <div class="p-5 border rounded-lg shadow-md bg-white relative">


                                    <h3 class="text-md font-bold text-gray-900 mb-3">{{ __('Total Saved') }}</h3>

                                    <div>
                                        <span class="text-2xl font-bold text-gray-900">$1,250.00</span>
                                        <div class="mt-2">
                                <span class="text-xs text-green-500 inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1"
                                         fill="currentColor"
                                         viewBox="0 0 20 20"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                              d="M12 7a1 1 0 01-1 1H9a1 1 0 01-1-1V6a1 1 0 011-1h2a1 1 0 011 1v1zm-3 5a1 1 0 011-1h2a1 1 0 011 1v1a1 1 0 01-1 1H9a1 1 0 01-1-1v-1z"
                                              clip-rule="evenodd"></path>
                                    </svg>
                                    {{ __('+15% from last month') }}
                                </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Monthly Savings Required -->
                                <div class="p-5 border rounded-lg shadow-md bg-white relative">


                                    <h3 class="text-md font-bold text-gray-900 mb-3">{{ __('Monthly Target') }}</h3>

                                    <div>
                                        <span class="text-2xl font-bold text-gray-900">$350.00</span>
                                        <div class="mt-2 text-xs text-gray-500">
                                            {{ __('Based on your active piggy banks') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity Section -->
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Recent Activity') }}</h2>

                            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                                <div class="p-5">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between pb-3 border-b">
                                            <div>
                                                <p class="font-medium text-gray-900">New iPhone 15 Pro</p>
                                                <p class="text-sm text-gray-500">{{ __('Saved') }} $50.00</p>
                                            </div>
                                            <div class="text-sm text-right">
                                                <p class="text-gray-900">{{ __('March 15, 2025') }}</p>
                                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('saved') }}
                                    </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between pb-3 border-b">
                                            <div>
                                                <p class="font-medium text-gray-900">Gaming PC</p>
                                                <p class="text-sm text-gray-500">{{ __('Saved') }} $100.00</p>
                                            </div>
                                            <div class="text-sm text-right">
                                                <p class="text-gray-900">{{ __('March 10, 2025') }}</p>
                                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('saved') }}
                                    </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">Vacation Fund</p>
                                                <p class="text-sm text-gray-500">{{ __('Saved') }} $75.00</p>
                                            </div>
                                            <div class="text-sm text-right">
                                                <p class="text-gray-900">{{ __('March 5, 2025') }}</p>
                                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('saved') }}
                                    </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Metrics Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Upcoming Scheduled Payments -->
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Upcoming Payments') }}</h2>

                                <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                                    <div class="p-5">
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between pb-3 border-b">
                                                <div>
                                                    <p class="font-medium text-gray-900">New iPhone 15 Pro</p>
                                                    <p class="text-sm text-gray-500">${{ number_format(50, 2) }}</p>
                                                </div>
                                                <div class="text-sm text-right">
                                                    <p class="text-gray-900">{{ __('March 25, 2025') }}</p>
                                                    <p class="text-xs text-gray-500">5 {{ __('days left') }}</p>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between pb-3 border-b">
                                                <div>
                                                    <p class="font-medium text-gray-900">Gaming PC</p>
                                                    <p class="text-sm text-gray-500">${{ number_format(100, 2) }}</p>
                                                </div>
                                                <div class="text-sm text-right">
                                                    <p class="text-gray-900">April 1, 2025</p>
                                                    <p class="text-xs text-gray-500">12 {{ __('days left') }}</p>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="font-medium text-gray-900">Vacation Fund</p>
                                                    <p class="text-sm text-gray-500">${{ number_format(75, 2) }}</p>
                                                </div>
                                                <div class="text-sm text-right">
                                                    <p class="text-gray-900">April 5, 2025</p>
                                                    <p class="text-xs text-gray-500">16 {{ __('days left') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Savings Distribution -->
                            <div class="relative">


                                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Savings Distribution') }}</h2>

                                <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                                    <div class="p-5">
                                        <div class="flex items-center justify-center h-48">
                                            <!-- Placeholder for pie chart -->
                                            <div class="text-center">
                                                <div class="rounded-full w-32 h-32 mx-auto mb-4"
                                                     style="background: conic-gradient(#4F46E5 0% 40%, #10B981 40% 65%, #F59E0B 65% 85%, #6B7280 85% 100%);"></div>
                                                <div class="grid grid-cols-2 gap-2 text-sm">
                                                    <div class="flex items-center">
                                                        <span class="w-3 h-3 bg-indigo-600 rounded-full mr-2"></span>
                                                        <span>{{ __('Electronics') }}</span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span class="w-3 h-3 bg-emerald-500 rounded-full mr-2"></span>
                                                        <span>{{ __('Travel') }}</span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span class="w-3 h-3 bg-amber-500 rounded-full mr-2"></span>
                                                        <span>{{ __('Gifts') }}</span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <span class="w-3 h-3 bg-gray-500 rounded-full mr-2"></span>
                                                        <span>{{ __('Other') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Completed Piggy Banks Section -->
                        <div class="mt-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('Achievement Stats') }}</h2>


                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-5 border rounded-lg shadow-md bg-white">
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">{{ __('Completed Piggy Banks') }}</h3>
                                    <p class="text-2xl font-bold text-gray-900">2</p>
                                </div>

                                <div class="p-5 border rounded-lg shadow-md bg-white">
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">{{ __('Total Successfully Saved') }}</h3>
                                    <p class="text-2xl font-bold text-gray-900">$3,250.00</p>
                                </div>

                                <div class="p-5 border rounded-lg shadow-md bg-white">
                                    <h3 class="text-sm font-medium text-gray-500 mb-1">{{ __('Longest Saving Streak') }}</h3>
                                    <p class="text-2xl font-bold text-gray-900">8 {{ __('weeks') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>

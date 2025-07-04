@php
    $successMessage = session('success');
    $errorMessage = session('error');
    $warningMessage = session('warning');
    $infoMessage = session('info');

    // Get custom duration if provided, otherwise use default
    $successDuration = session('success_duration', 5000);

    if ($successMessage || $errorMessage || $warningMessage || $infoMessage) {
        session()->forget(['success', 'error', 'warning', 'info', 'success_duration']);
    }

    // Use a simpler structure for AJAX requests without Alpine.js
    $isAjaxRequest = isset($ajax_request) && $ajax_request;
@endphp

@if ($successMessage || $errorMessage || $warningMessage || $infoMessage)
    <div
        @if(!$isAjaxRequest)
            x-data="{
                show: true,
                autoClose() {
                    setTimeout(() => { this.show = false }, {{ $successMessage ? $successDuration : 5000 }})
                }
            }"
        x-init="autoClose()"
        x-show="show"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @endif
        class="fixed inset-x-0 top-4 z-50 mx-4 sm:mx-auto sm:max-w-md"
    >
        @if ($successMessage)
            <div class="relative rounded-md bg-green-100 border border-green-200 p-4 shadow-md">
                @if(!$isAjaxRequest)
                    <button
                        @click="show = false"
                        class="absolute top-2 right-2 text-green-600 hover:text-green-800 cursor-pointer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
                <p class="text-green-800 text-sm font-medium pr-6">
                    {{ $successMessage }}
                </p>
            </div>
        @endif

        @if ($errorMessage)
            <div class="relative rounded-md bg-red-100 border border-red-200 p-4 shadow-md">
                @if(!$isAjaxRequest)
                    <button
                        @click="show = false"
                        class="absolute top-2 right-2 text-red-600 hover:text-red-800 cursor-pointer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
                <p class="text-red-800 text-sm font-medium pr-6">
                    {{ $errorMessage }}
                </p>
            </div>
        @endif

        @if ($warningMessage)
            <div class="relative rounded-md bg-yellow-100 border border-yellow-200 p-4 shadow-md">
                @if(!$isAjaxRequest)
                    <button
                        @click="show = false"
                        class="absolute top-2 right-2 text-yellow-600 hover:text-yellow-800 cursor-pointer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
                <p class="text-yellow-800 text-sm font-medium pr-6">
                    {{ $warningMessage }}
                </p>
            </div>
        @endif

        @if ($infoMessage)
            <div class="relative rounded-md bg-blue-100 border border-blue-200 p-4 shadow-md">
                @if(!$isAjaxRequest)
                    <button
                        @click="show = false"
                        class="absolute top-2 right-2 text-blue-600 hover:text-blue-800 cursor-pointer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
                <p class="text-blue-800 text-sm font-medium pr-6">
                    {{ $infoMessage }}
                </p>
            </div>
        @endif
    </div>
@endif

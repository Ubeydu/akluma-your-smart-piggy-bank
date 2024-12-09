@php
    $successMessage = session('success');
    $errorMessage = session('error');
    if ($successMessage || $errorMessage) {
        session()->forget(['success', 'error']);
    }
@endphp

@if ($successMessage || $errorMessage)
    <div
        x-data="{
            show: true,
            autoClose() {
                setTimeout(() => { this.show = false }, 5000)
            }
        }"
        x-init="autoClose()"
        x-show="show"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-x-0 top-4 z-50 mx-4 sm:mx-auto sm:max-w-md"
    >
        @if ($successMessage)
            <div class="relative rounded-md bg-green-100 border border-green-200 p-4 shadow-md">
                <button
                    @click="show = false"
                    class="absolute top-2 right-2 text-green-600 hover:text-green-800"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
                <p class="text-green-800 text-sm font-medium pr-6">
                    {{ $successMessage }}
                </p>
            </div>
        @endif

        @if ($errorMessage)
                <div class="relative rounded-md bg-red-100 border border-red-200 p-4 shadow-md">
                    <button
                        @click="show = false"
                        class="absolute top-2 right-2 text-red-600 hover:text-red-800"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <p class="text-red-800 text-sm font-medium pr-6">
                        {{ $errorMessage }}
                    </p>
                </div>
        @endif
    </div>
@endif

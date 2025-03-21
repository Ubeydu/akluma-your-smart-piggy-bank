<div
    x-cloak
    x-show="showConfirmCancel"
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
>
    <!-- Dark overlay -->
    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:p-0">
        <div
            class="fixed inset-0 bg-gray-500/40 transition-opacity"
            @click="showConfirmCancel = false"
        ></div>

        <!-- Dialog box -->
        <div class="relative inline-block transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
            <!-- Content -->
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                    <h3 class="text-gray-600">
                        {{ $title }}
                    </h3>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="mt-6">
                {{ $actions }}
            </div>
        </div>
    </div>
</div>

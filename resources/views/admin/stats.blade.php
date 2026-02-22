<x-admin-layout>
    @section('title', 'Stats')

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_users']) }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ number_format($stats['new_users_this_month']) }} joined this month</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Admin Users</p>
            <p class="mt-2 text-3xl font-bold text-violet-700 dark:text-violet-300">{{ number_format($stats['admin_users']) }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Suspended Users</p>
            <p class="mt-2 text-3xl font-bold text-red-500 dark:text-red-400">{{ number_format($stats['suspended_users']) }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Piggy Banks</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_piggy_banks']) }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ number_format($stats['active_piggy_banks']) }} active</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Scheduled Savings</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_scheduled_savings']) }}</p>
        </div>

    </div>
</x-admin-layout>

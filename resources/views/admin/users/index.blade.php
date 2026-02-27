<x-admin-layout>
    @section('title', 'Users')

    <div class="space-y-4">

        {{-- Search --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-3"
              x-data="{ search: '{{ request('search') }}' }"
              x-ref="form">
            <input
                type="text"
                name="search"
                x-model="search"
                x-on:input.debounce.400ms="$refs.form.submit()"
                x-init="$el.focus(); $el.setSelectionRange($el.value.length, $el.value.length);"
                value="{{ request('search') }}"
                placeholder="Search by name or email…"
                class="w-80 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-xs focus:border-violet-500 focus:ring-violet-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500"
            >
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Clear
                </a>
            @endif
        </form>

        {{-- Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Language</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Piggy Banks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors cursor-pointer"
                            onclick="window.location='{{ route('admin.users.show', $user) }}'">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-bold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $user->name }}
                                            @if($user->isAdmin())
                                                <span class="ml-1 inline-flex items-center rounded-full bg-violet-100 px-1.5 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">Admin</span>
                                            @endif
                                        </p>
                                        <p class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $user->email }}
                                            @if($user->google_id)
                                                <x-google-icon class="size-3.5 shrink-0" title="Google sign-in" />
                                            @endif
                                        </p>
                                        @if(!$user->google_id)
                                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                                @if($user->email_verified_at)
                                                    Verified: {{ $user->email_verified_at->format('M j, Y') }}
                                                @else
                                                    <span class="text-amber-500 dark:text-amber-400">Not verified</span>
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $user->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 uppercase">
                                {{ $user->language ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900 dark:text-white">{{ $user->piggy_banks_count }} total · {{ $user->active_piggy_banks_count }} active</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $user->connected_piggy_banks_count }} active in {{ $user->vaults_count }} {{ Str::plural('vault', $user->vaults_count) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->isSuspended())
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                        Suspended
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.users.show', $user) }}"
                                   class="text-sm font-medium text-violet-600 hover:text-violet-800 dark:text-violet-400 dark:hover:text-violet-200">
                                    View →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>

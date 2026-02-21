<x-admin-layout>
    @section('title', $user->name)

    <div class="space-y-6 max-w-2xl">

        {{-- Back link --}}
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            ← Back to Users
        </a>

        {{-- User card --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-4">
                <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xl font-bold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        {{ $user->name }}
                        @if($user->isAdmin())
                            <span class="inline-flex items-center rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">Admin</span>
                        @endif
                        @if($user->isSuspended())
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">Suspended</span>
                        @endif
                    </h2>
                    <p class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                        {{ $user->email }}
                        @if($user->google_id)
                            <x-google-icon class="size-4 shrink-0" title="Google sign-in" />
                        @endif
                    </p>
                </div>
            </div>

            <dl class="mt-6 grid grid-cols-2 gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Joined</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Language</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white uppercase">{{ $user->language ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Currency</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->currency ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Timezone</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->timezone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Piggy Banks</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->piggy_banks_count }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase text-gray-400">Vaults</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->vaults_count }}</dd>
                </div>
                @if($user->isSuspended())
                    <div class="col-span-2">
                        <dt class="text-xs font-medium uppercase text-gray-400">Suspended At</dt>
                        <dd class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $user->suspended_at->format('M j, Y H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Actions --}}
        @if(!$user->is(auth()->user()))
            <div class="flex flex-wrap gap-3">

                @if($user->isSuspended())
                    <form method="POST" action="{{ route('admin.users.unsuspend', $user) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="cursor-pointer rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                            Unsuspend User
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="cursor-pointer rounded-lg bg-amber-500 px-4 py-2 text-sm font-medium text-white hover:bg-amber-600 transition-colors">
                            Suspend User
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                      onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="cursor-pointer rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                        Delete User
                    </button>
                </form>

            </div>
        @else
            <p class="text-sm text-gray-400">You cannot modify your own account.</p>
        @endif

    </div>
</x-admin-layout>

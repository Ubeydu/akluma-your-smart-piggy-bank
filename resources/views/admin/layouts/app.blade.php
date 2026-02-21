<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') — Akluma Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 font-sans antialiased">

<div class="flex h-full min-h-screen">

    {{-- Sidebar --}}
    <aside class="flex w-64 shrink-0 flex-col bg-violet-700 bg-linear-to-b from-violet-600 via-purple-600 to-fuchsia-600 dark:from-violet-900 dark:via-purple-900 dark:to-fuchsia-900">

        {{-- Logo --}}
        <div class="flex h-16 items-center gap-3 px-6">
            <x-application-logo class="h-8 w-8 shrink-0 text-white" />
            <div>
                <p class="text-sm font-bold text-white">Akluma</p>
                <p class="text-xs font-medium text-white/70">Admin Panel</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 space-y-1 px-3 py-4">
            <a href="{{ route('admin.stats') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.stats') ? 'bg-white/30 text-white font-semibold border-l-2 border-white' : 'text-purple-100 hover:bg-white/10 hover:text-white' }}">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Stats
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.users.*') ? 'bg-white/30 text-white font-semibold border-l-2 border-white' : 'text-purple-100 hover:bg-white/10 hover:text-white' }}">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Users
            </a>
        </nav>

        {{-- Bottom: back to app, user info, logout --}}
        <div class="border-t border-white/20 px-3 py-4 space-y-1">
            <a href="{{ localizedRoute('localized.dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-purple-100 hover:bg-white/10 hover:text-white transition-colors">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to App
            </a>

            <div class="flex items-center gap-3 rounded-lg px-3 py-2.5">
                <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-white/20 text-xs font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-xs font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-white/60">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <form method="POST" action="{{ localizedRoute('localized.logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-purple-100 hover:bg-white/10 hover:text-white transition-colors">
                    <svg class="size-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Log Out
                </button>
            </form>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex flex-1 flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="flex h-16 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-6 dark:border-gray-800 dark:bg-gray-900">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">@yield('title', 'Admin Panel')</h1>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mx-6 mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mx-6 mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/30 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>

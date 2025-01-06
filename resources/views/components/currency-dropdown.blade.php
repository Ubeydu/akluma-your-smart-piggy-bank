<x-dropdown>
    <x-slot name="trigger">
        <div x-data="{ open: false }" @click="open = !open" class="block flex w-full px-4 py-2 text-start text-sm leading-5 text-gray-600 hover:bg-gray-100 hover:rounded-sm focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out cursor-pointer items-center justify-between whitespace-nowrap">
            <span class="flex">
                {{ __('Currency') }}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.3" stroke="currentColor" class="size-5 ml-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
            </span>
            <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.23a.75.75 0 011.06 0L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 010-1.06z" clip-rule="evenodd" />
            </svg>
        </div>
    </x-slot>

    <x-slot name="content">
        @foreach (config('app.currencies') as $code => $currency)
            <x-dropdown-link :href="route('currency.switch', ['currency' => $code])"
                             :class="session('currency') == $code ? 'font-bold text-gray-900' : ''">
                {{ __($currency['name']) }}
            </x-dropdown-link>
        @endforeach
    </x-slot>
</x-dropdown>

<x-dropdown>
    <x-slot name="trigger">
        <div x-data="{ open: false }" @click="open = !open" class="block flex w-full px-4 py-2 text-start text-sm leading-5 text-gray-600 hover:bg-gray-100 hover:rounded-xs focus:outline-hidden focus:bg-gray-100 transition duration-150 ease-in-out cursor-pointer items-center justify-between whitespace-nowrap">
            <span class="flex" >{{ __('Language') }}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.3" stroke="currentColor" class="size-5 ml-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
            </svg>
            </span>
            <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.23a.75.75 0 011.06 0L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 010-1.06z" clip-rule="evenodd" />
            </svg>
        </div>
    </x-slot>

    <x-slot name="content">
{{--        <div class="border-t border-gray-100"></div>--}}
        @foreach (config('app.available_languages') as $language => $locale)
            <x-dropdown-link :href="route('language.switch', ['locale' => $locale])"
                             :class="App::getLocale() == $locale ? 'font-bold text-gray-900' : ''">
                {{ __($language) }}
            </x-dropdown-link>
        @endforeach
    </x-slot>
</x-dropdown>

<x-dropdown>
    <x-slot name="trigger">
        <div x-data="{ open: false }" @click="open = !open" class="block flex w-full px-4 py-2 text-start text-sm leading-5 text-gray-500 hover:bg-gray-100 hover:rounded-sm focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out cursor-pointer items-center justify-between whitespace-nowrap">
            <span>{{ __('Language') }}</span>
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

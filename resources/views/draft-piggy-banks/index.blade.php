<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-900 leading-tight">
                {{ __('My Draft Piggy Banks') }}
            </h2>
            <a href="{{ localizedRoute('localized.create-piggy-bank.step-1') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                <span class="hidden sm:inline">{{ __('Create New Piggy Bank') }}</span>
                <span class="sm:hidden">{{ __('Create') }}</span>
            </a>
        </div>
    </x-slot>

    <div class="py-4 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xs rounded-lg">
                <div class="py-4 px-4">
                    <div class="mt-4">
                        {{-- Empty State --}}
                        @if($drafts->isEmpty())
                            <x-empty-state
                                :title="__('draft.empty_state.title')"
                                :message="__('draft.empty_state.message')"
                                :buttonText="__('draft.empty_state.button_text')"
                                buttonLink="{{ localizedRoute('localized.create-piggy-bank.step-1') }}"
                            />
                        @else
                            {{-- Draft Cards Grid --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($drafts as $draft)
                                    <x-draft-card :draft="$draft" />
                                @endforeach
                            </div>

                            {{-- Pagination --}}
                            @if($drafts->hasPages())
                                <div class="mt-6">
                                    {{ $drafts->links() }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

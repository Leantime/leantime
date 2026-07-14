@props([
    'title' => '',
    'icon' => null,
    'defaultOpen' => true,
    'id' => null,
])

<div
    x-data="{ open: {{ $defaultOpen ? 'true' : 'false' }} }"
    @if($id) id="{{ $id }}" @endif
    class="tw-bg-white tw-rounded-xl tw-border tw-border-[#E8ECF0] tw-overflow-hidden"
>
    {{-- Header --}}
    <button
        @click="open = !open"
        class="tw-w-full tw-flex tw-items-center tw-gap-3 tw-px-5 tw-py-3.5 tw-cursor-pointer tw-select-none"
        :class="open ? 'tw-border-b tw-border-[#F0F1F3]' : ''"
    >
        @if($icon)
            <span class="tw-text-sm">{{ $icon }}</span>
        @endif
        <span class="tw-text-sm tw-font-bold tw-text-[#1A1A2E]">{{ $title }}</span>

        {{-- Collapsed summary slot --}}
        <span x-show="!open" x-cloak class="tw-flex tw-items-center tw-gap-2 tw-ml-2">
            {{ $collapsedSummary ?? '' }}
        </span>

        <svg
            class="tw-ml-auto tw-w-4 tw-h-4 tw-text-[#9CA3AF] tw-transition-transform tw-duration-200"
            :class="open ? 'tw-rotate-180' : ''"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Content --}}
    <div
        x-show="open"
        x-collapse
        class="tw-px-5 tw-py-4"
    >
        {{ $slot }}
    </div>
</div>

{{-- Board view menu on the kanban nav bar: per-user choice of which optional fields the cards
     show (#1859) and how cards are sorted within a column (#1536). Saved via HTMX; the board
     reloads because cards render server-side.
     Lives outside the #ticketSearch form so it never leaks into the filter query string. --}}
@php
    $kanbanFields = $kanbanView['fields'] ?? [];
    $kanbanSort = $kanbanView['sort'] ?? \Leantime\Domain\Tickets\Services\KanbanViewSettings::DEFAULT_SORT;
    $kanbanSortOptions = array_keys(\Leantime\Domain\Tickets\Services\KanbanViewSettings::SORT_OPTIONS);
@endphp

{{-- keep-open: clicks inside the menu must not close it after every checkbox. --}}
<x-global::actions.dropdown variant="filter" menu-as="div" menu-class="tw-p-3" menu-style="min-width:220px;" keep-open class="kanbanViewMenu" style="vertical-align: bottom; margin-bottom:20px;">
    <x-slot:trigger class="btn-link" data-tippy-content="{{ __('label.kanban_view_menu') }}">
        <span class="fa-solid fa-sliders"></span> {{ __('label.kanban_view_menu') }}
        @if ($kanbanSort !== \Leantime\Domain\Tickets\Services\KanbanViewSettings::DEFAULT_SORT)
            <span class="badge badge-primary">1</span>
        @endif
    </x-slot:trigger>
    <form hx-post="{{ BASE_URL }}/hx/tickets/kanbanView/save" hx-swap="none">
        {{-- The board's own project: the session project can change in another tab. --}}
        <input type="hidden" name="projectId" value="{{ (int) ($kanbanViewProjectId ?? 0) }}" />
        <div class="tw-font-bold tw-mb-1">{{ __('label.kanban_card_fields') }}</div>
        @foreach ($kanbanFields as $fieldName => $isVisible)
            <span class="checkbox tw-block">
                <input type="checkbox"
                       name="fields[]"
                       value="{{ $fieldName }}"
                       id="kanbanField-{{ $fieldName }}"
                       @checked($isVisible) />
                <label for="kanbanField-{{ $fieldName }}">{{ __('label.kanban_field_'.$fieldName) }}</label>
            </span>
        @endforeach

        <div class="tw-font-bold tw-mb-1 tw-mt-2">{{ __('label.kanban_sort') }}</div>
        @foreach ($kanbanSortOptions as $sortOption)
            <span class="radio tw-block">
                <input type="radio"
                       name="sort"
                       value="{{ $sortOption }}"
                       id="kanbanSort-{{ $sortOption }}"
                       @checked($kanbanSort === $sortOption) />
                <label for="kanbanSort-{{ $sortOption }}">{{ __('label.kanban_sort_'.$sortOption) }}</label>
            </span>
        @endforeach

        <div class="tw-mt-2 tw-text-right">
            <button type="submit" class="btn btn-primary">{{ __('buttons.save') }}</button>
        </div>
    </form>
</x-global::actions.dropdown>

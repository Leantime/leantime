{{-- Board view menu on the kanban nav bar: per-user choice of which optional fields the cards
     show (#1859) and how cards are sorted within a column (#1536). Saved via HTMX; the board
     reloads because cards render server-side.
     Lives outside the #ticketSearch form so it never leaks into the filter query string. --}}
@php
    $kanbanFields = $kanbanView['fields'] ?? [];
    $kanbanSort = $kanbanView['sort'] ?? \Leantime\Domain\Tickets\Services\KanbanViewSettings::DEFAULT_SORT;
    $kanbanSortOptions = array_keys(\Leantime\Domain\Tickets\Services\KanbanViewSettings::SORT_OPTIONS);
@endphp

<div class="btn-group viewDropDown kanbanViewMenu" style="vertical-align: bottom; margin-bottom:20px;">
    <button class="btn btn-link dropdown-toggle" type="button" data-toggle="dropdown" data-tippy-content="{{ __('label.kanban_view_menu') }}">
        <span class="fa-solid fa-sliders"></span> {{ __('label.kanban_view_menu') }}
        @if ($kanbanSort !== \Leantime\Domain\Tickets\Services\KanbanViewSettings::DEFAULT_SORT)
            <span class="badge badge-primary">1</span>
        @endif
    </button>
    {{-- Clicks inside the menu must not bubble to Bootstrap's document handler, which would
         close the menu after every checkbox. --}}
    <div class="dropdown-menu tw-p-3" style="min-width:220px;" onclick="event.stopPropagation();">
        <form hx-post="{{ BASE_URL }}/hx/tickets/kanbanView/save" hx-swap="none">
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
    </div>
</div>

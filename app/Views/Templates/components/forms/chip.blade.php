@props([
    'type',                   // status | priority | effort | milestone | user | sprint | relates — the {type}Dropdown class + toggle id
    'adapter' => 'ticket',    // how a pick is saved: ticket | canvas | goal | idea (see core/chips.js)
    'entityId',               // id of the ticket / canvas item / goal / idea
    'field',                  // the column the pick writes (status, priority, storypoints, milestoneid, editorId, sprint, relates, box)
    'value' => '',            // the current raw value
    'label' => '',            // the current value's text (default toggle content)
    'toggleClass' => '',      // classes the toggle always has today (e.g. "label-default effort f-left")
    'colorClass' => '',       // the current value's color class (swapped on change), e.g. "label-info" / "priority-bg-2"
    'color' => '',            // the current value's background color (milestones), e.g. "#1B75BB"
    'header' => '',           // menu heading, e.g. __('dropdown.choose_status')
    'align' => 'start',       // end -> the menu opens right-aligned (pull-right)
    'canvasType' => null,     // canvas adapter: the board type the item must belong to
])

{{--
    forms.chip — a colored value picker bound to one field of one entity (status, priority, milestone…).

    Renders the same markup the chips had before (wrapper `.ticketDropdown.{type}Dropdown`, toggle
    `#{type}DropdownMenuLink{id}`, `.label-*` / `priority-bg-*` colors) so the kanban, card coloring and
    CSS keep working. Saving is handled by ONE delegated handler in public/assets/js/app/core/chips.js,
    read from the data-lt-chip attributes below — no per-page init call, and HTMX-rendered chips work.
    After a save it fires `lt:chip:changed` (bubbles) with {adapter, type, field, entityId, value, label}.

    Options are the slot: <x-global::forms.chip.option>. Pass a custom toggle body with
    <x-slot:toggle> (mark the name element with data-chip-label and the avatar <img> with
    data-chip-image so a pick updates them).

      <x-global::forms.chip type="status" field="status" :entity-id="$row['id']" :value="$row['status']"
          :label="$label['name']" :color-class="$label['class']" toggle-class="status f-left"
          :header="__('dropdown.choose_status')">
          @foreach ($statusLabels as $key => $label)
              <x-global::forms.chip.option :value="$key" :label="$label['name']" :color-class="$label['class']" />
          @endforeach
      </x-global::forms.chip>
--}}
@php
    $toggleId = $type.'DropdownMenuLink'.$entityId;
    $toggleClasses = trim('dropdown-toggle '.$toggleClass.' '.$colorClass);
@endphp
<div {{ $attributes->class(['dropdown', 'ticketDropdown', $type.'Dropdown', 'show']) }} data-lt-chip="{{ $adapter }}" data-chip-type="{{ $type }}" data-entity-id="{{ $entityId }}" data-field="{{ $field }}" data-current-value="{{ $value }}"@if ($canvasType) data-canvas-type="{{ $canvasType }}"@endif>
    <a class="{{ $toggleClasses }}" href="javascript:void(0);" role="button" id="{{ $toggleId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"@if ($color !== '') style="background-color:{{ $color }}"@endif>
        @isset($toggle){{ $toggle }}@else<span class="text">{{ $label }}</span>@endisset
        &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
    </a>
    <ul @class(['dropdown-menu', 'pull-right' => $align === 'end']) aria-labelledby="{{ $toggleId }}">
        @if ($header !== '')
            <li class="nav-header border">{{ $header }}</li>
        @endif
        {{ $slot }}
    </ul>
</div>

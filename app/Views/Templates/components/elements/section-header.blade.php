{{--
    elements.section-header — the title of a section inside a page, card or dialog
    ("Subtasks", "Discussion", "Team members").

    Section title 16 / bold with an optional icon; the eyebrow and description are 12px (type rule in
    COMPONENTS.md). Not the page title (pageheader) and not a dialog's first row (actions.modal-header).

    Props:
      icon         string  Font Awesome classes for a leading icon, e.g. 'fa fa-users'.
      eyebrow      string  Small uppercase label above the title ("Step 2", "Program").
      description  string  One muted line under the title.
      as           string  Heading tag, default 'h4'; 'h2'-'h6' to fit the page's heading outline.

    Slots:
      default  The title. Markup is allowed (a translation with a help icon).
      actions  Right-aligned items on the title row: a button, a dropdown, a count badge.

    Anything else (id, data-*, extra classes) passes through to the wrapper.
--}}
@props([
    'icon' => null,
    'eyebrow' => null,
    'description' => null,
    'as' => 'h4',
])

@php
    $as = in_array($as, ['h2', 'h3', 'h4', 'h5', 'h6'], true) ? $as : 'h4';
@endphp

<div {{ $attributes->class(['lt-section-hd']) }}>
    <div class="lt-section-hd-text">
        @if ($eyebrow)
            <div class="lt-section-hd-eyebrow">{{ $eyebrow }}</div>
        @endif
        <{{ $as }} class="lt-section-hd-title">
            @if ($icon)
                <i class="{{ $icon }}" aria-hidden="true"></i>
            @endif
            {{ $slot }}
        </{{ $as }}>
        @if ($description)
            <p class="lt-section-hd-desc">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="lt-section-hd-actions">{{ $actions }}</div>
    @endisset
</div>

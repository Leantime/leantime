{{--
    elements.accordion — a collapsible section on native <details>/<summary>
    (keyboard and screen-reader support built in, no JS needed to open/close).

    Props:
      title    string  Summary text (escaped). For markup use the `title` slot.
      icon     string  Font Awesome classes before the title.
      count    int     Optional number shown as a counter after the title ("Open items 4").
      open     bool    Open on first render. With `id`, the user's last choice wins.
      id       string  Remembers open/closed per user (same store as the old accordion, key
                       "accordion_content-{id}"). Omit for a section that always starts as `open` says.
      card     bool    Bordered card look instead of a plain section.
      onPage   bool    The section sits on the page background (outside the content box, e.g. the
                       Project Hub): the summary uses the page-title colour so it stays readable.

    Slots:
      default  The content.
      title    Summary with markup.
      actions  Items at the right of the summary row (they don't toggle the section).

    Anything else (class, data-*) passes through to the wrapper.
--}}
@props([
    'title' => null,
    'icon' => null,
    'count' => null,
    'open' => false,
    'id' => null,
    'card' => false,
    'onPage' => false,
])

@php
    $isOpen = (bool) $open;
    if ($id !== null) {
        $savedState = session('usersettings.submenuToggle.accordion_content-' . $id);
        if ($savedState === 'open' || $savedState === 'closed') {
            $isOpen = $savedState === 'open';
        }
    }
    $titleContent = $title instanceof \Illuminate\View\ComponentSlot ? $title : e($title ?? '');
@endphp

<div {{ $attributes->class(['lt-accordion', 'lt-accordion--card' => $card, 'lt-accordion--on-page' => $onPage]) }}>
    {{-- Before <details> so focus reaches the actions before the body; CSS places them on the summary row. --}}
    @isset($actions)
        <div class="lt-accordion-actions">{{ $actions }}</div>
    @endisset
    <details @if ($isOpen) open @endif @if ($id !== null) data-lt-accordion="{{ $id }}" data-lt-open="{{ $isOpen ? 'open' : 'closed' }}" @endif>
        <summary @class(['lt-accordion-summary', 'lt-accordion-summary--actions' => isset($actions)])>
            <i class="fa fa-chevron-right lt-accordion-chevron" aria-hidden="true"></i>
            @if ($icon)
                <i class="{{ $icon }}" aria-hidden="true"></i>
            @endif
            <span class="lt-accordion-title">{!! $titleContent !!}</span>
            @if ($count !== null)
                <span class="lt-badge lt-badge--count">{{ $count }}</span>
            @endif
        </summary>
        <div class="lt-accordion-body">
            {{ $slot }}
        </div>
    </details>
</div>

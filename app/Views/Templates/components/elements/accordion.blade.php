{{--
    elements.accordion — a collapsible section on native <details>/<summary>
    (keyboard and screen-reader support built in, no JS needed to open/close).

    Props:
      title    string  Summary text (escaped). For markup use the `title` slot.
      icon     string  Font Awesome classes before the title.
      subtitle string  A small line under the title ("Start: Oct 1 • End: Oct 30"). Escaped.
      color    string  Title colour: a hex value or a CSS variable ("#1b75bb", "var(--green)"),
                       e.g. a milestone's colour. Anything else is ignored.
      count    int     Optional number shown as a counter after the title ("Open items 4").
      open     bool    Open on first render. With `id`, the user's last choice wins.
      id       string  Remembers open/closed per user (UiState, same key as the old accordion,
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
    'subtitle' => null,
    'color' => null,
    'count' => null,
    'open' => false,
    'id' => null,
    'card' => false,
    'onPage' => false,
])

@php
    $isOpen = (bool) $open;
    // The state id (also sent by core/snippets.js when the user toggles) escapes anything outside
    // [A-Za-z0-9_-], so "0.5" and "0" stay different keys; ids without such characters stay as they
    // are, so their saved choices carry over. States live in UiState (one database row each).
    $stateId = $id === null ? null : preg_replace_callback('/[^A-Za-z0-9_-]/', fn ($m) => '~' . bin2hex($m[0]), (string) $id);
    if ($stateId !== null) {
        $savedState = app(\Leantime\Core\UI\UiState::class)->get('accordion_content-' . $stateId);
        if ($savedState === 'open' || $savedState === 'closed') {
            $isOpen = $savedState === 'open';
        }
    }
    $titleContent = $title instanceof \Illuminate\View\ComponentSlot ? $title : e($title ?? '');
    // The colour lands in a style attribute: allow only a plain colour value, nothing that could
    // close the declaration and add others.
    $titleColor = is_string($color) && preg_match('/^(#[0-9a-fA-F]{3,8}|var\(--[\w-]+\))$/', trim($color)) ? trim($color) : null;
    $hasSubtitle = trim((string) $subtitle) !== '';
@endphp

<div {{ $attributes->class(['lt-accordion', 'lt-accordion--card' => $card, 'lt-accordion--on-page' => $onPage]) }}>
    {{-- Before <details> so focus reaches the actions before the body; CSS places them on the summary row. --}}
    @isset($actions)
        <div class="lt-accordion-actions">{{ $actions }}</div>
    @endisset
    <details @if ($isOpen) open @endif @if ($stateId !== null) data-lt-accordion="{{ $stateId }}" data-lt-open="{{ $isOpen ? 'open' : 'closed' }}" @endif>
        <summary @class(['lt-accordion-summary', 'lt-accordion-summary--actions' => isset($actions)]) @if ($titleColor) style="--lt-accordion-title-color: {{ $titleColor }}" @endif>
            <i class="fa fa-chevron-right lt-accordion-chevron" aria-hidden="true"></i>
            @if ($icon)
                <i class="{{ $icon }}" aria-hidden="true"></i>
            @endif
            @if ($hasSubtitle)
                <span class="lt-accordion-heading">
                    <span class="lt-accordion-title">{!! $titleContent !!}</span>
                    <span class="lt-accordion-subtitle">{{ $subtitle }}</span>
                </span>
            @else
                <span class="lt-accordion-title">{!! $titleContent !!}</span>
            @endif
            @if ($count !== null)
                <span class="lt-badge lt-badge--count">{{ $count }}</span>
            @endif
        </summary>
        <div class="lt-accordion-body">
            {{ $slot }}
        </div>
    </details>
</div>

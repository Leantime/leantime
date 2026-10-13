{{--
    elements.empty-state — what a list, board or panel shows when it has nothing yet:
    an icon or illustration, a title, a hint and the action that fills it.

    Title 16 / semibold (14 when compact), hint 12px muted (type rule in COMPONENTS.md).

    Props:
      title        string  What is empty ("No milestones yet").
      description  string  One hint line ("Milestones group your to-dos into deliverables.").
                           For markup use the default slot instead.
      icon         string  Font Awesome classes for a round muted icon, e.g. 'fa fa-flag'.
      image        string  An undraw illustration file from dist/images/svg (instead of the icon).
      compact      bool    Left-aligned, no padding, small type: an empty row inside a list or card.
      titleAs      string  Tag for the title: 'div' (default) or 'h2'-'h6' when the empty state stands
                           in for a page section and should appear in the heading outline.

    Slots:
      default  The hint with markup.
      actions  Buttons that fill the empty state ("Add milestone").

    Anything else (id, data-*, extra classes) passes through to the wrapper.
--}}
@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'image' => null,
    'compact' => false,
    'titleAs' => 'div',
])

@php
    $imagePath = $image ? ROOT . '/dist/images/svg/' . basename($image) : null;
    $hasImage = $imagePath !== null && file_exists($imagePath);
    $hasSlot = trim((string) $slot) !== '';
    $titleAs = in_array($titleAs, ['h2', 'h3', 'h4', 'h5', 'h6', 'div'], true) ? $titleAs : 'div';
@endphp

<div {{ $attributes->class(['lt-empty', 'lt-empty--compact' => $compact]) }}>
    @if ($hasImage && ! $compact)
        <div class="lt-empty-image" aria-hidden="true">{!! file_get_contents($imagePath) !!}</div>
    @elseif ($icon)
        <span class="lt-empty-icon" aria-hidden="true"><i class="{{ $icon }}"></i></span>
    @endif
    @if ($title)
        <{{ $titleAs }} class="lt-empty-title">{{ $title }}</{{ $titleAs }}>
    @endif
    @if ($description || $hasSlot)
        <div class="lt-empty-desc">{{ $description }}{{ $slot }}</div>
    @endif
    @isset($actions)
        <div class="lt-empty-actions">{{ $actions }}</div>
    @endisset
</div>

{{--
    elements.badge — a small status label: "Active", "Trial", "3 open", "FTE".

    One box everywhere (20px tall, 12px / semibold) so badges next to chips and other badges line up.
    Soft (tinted) is for read-only status, solid for emphasis; only the colour differs.
    A badge is not clickable; a pick-and-save pill is forms.chip.

    Props:
      tone     string  'default' | 'accent' | 'success' | 'warning' | 'danger' | 'info' | 'muted'.
      variant  string  'soft' (default) | 'solid'.
      dot      bool    A small dot in the tone colour before the text ("● Live").
      icon     string  Font Awesome classes for a leading icon, e.g. 'fa fa-clock'.
      count    bool    Counter look: centred, at least as wide as it is tall ("3", "12").
      href     string  Renders an <a> (a tag filter link); otherwise a <span>.

    Anything else (title, id, data-*, extra classes) passes through to the badge.
--}}
@props([
    'tone' => 'default',
    'variant' => 'soft',
    'dot' => false,
    'icon' => null,
    'count' => false,
    'href' => null,
])

@php
    $tones = ['default', 'accent', 'success', 'warning', 'danger', 'info', 'muted'];
    $tone = in_array($tone, $tones, true) ? $tone : 'default';
    $variant = $variant === 'solid' ? 'solid' : 'soft';
    $tag = $href !== null ? 'a' : 'span';
@endphp

<{{ $tag }} @if ($href !== null) href="{{ $href }}" @endif {{ $attributes->class([
    'lt-badge',
    'lt-badge--' . $tone,
    'lt-badge--solid' => $variant === 'solid',
    'lt-badge--count' => $count,
]) }}>
    @if ($dot)
        <span class="lt-badge-dot" aria-hidden="true"></span>
    @endif
    @if ($icon)
        <i class="{{ $icon }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</{{ $tag }}>

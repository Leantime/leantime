{{--
    One KPI stat tile — THE shared tile, value-first (design call 2026-09-07).

    Replaces the three implementations that had drifted apart: the report deck's
    `.rd-kcell`, the core reports' `.reportStatTile`, and Resource Allocation's
    `.ra-scell`. Use it anywhere a "big number + caption" belongs.

    Props:
      value  mixed   REQUIRED. The number (or short string) that reads first.
      label  string  REQUIRED. The quiet caption beneath it.
      unit   string  Optional trailing subscript on the value — a denominator
                     ("/6"), a unit ("h"), or a qualifier ("planned").
      delta  array   Optional period-over-period change, rendered as a colored
                     pill inline with the value:
                       ['value' => float,          // signed change
                        'goodWhenUp' => bool|null, // null = no good/bad meaning
                        'label' => string|null]    // e.g. "vs. prior period"
      tone   string  'default' | 'risk' (colors the value red).
      icon   string  Optional Font Awesome classes shown before the label.
      sub    mixed   Optional detail line(s) under the label — a string, or an
                     array of strings for a second, smaller line ("of 40h/wk",
                     "0h of 4200h over 105 weeks").
                     NOTE: rendered UNESCAPED so a line can carry a `<span
                     class="risk">` fragment. Pass translated strings and
                     formatted numbers only — never raw user input.
      subTone string 'default' | 'risk' (ambers the sub-line, e.g. "2h over plan").
      muted  bool    Empty/unset state: dims the value and sub-line so a tile
                     with no data reads as absent rather than as a real zero.

    Anything else (id, tabindex, data-*, extra classes) passes through to the
    tile element, so drill-down behavior can be attached with `class="has-detail"`.
--}}
@props([
    'value',
    'label',
    'unit' => null,
    'delta' => null,
    'tone' => 'default',
    'icon' => null,
    'sub' => null,
    'subTone' => 'default',
    'muted' => false,
])

@php
    $deltaValue = $delta['value'] ?? null;
    $goodWhenUp = $delta['goodWhenUp'] ?? null;

    // Direction drives the arrow; good/bad drives the color. They are separate:
    // "overdue went up" is an increase AND bad, so an up arrow on a red pill.
    if ($deltaValue === null || (float) $deltaValue == 0.0) {
        $deltaTone = 'flat';
    } elseif ($goodWhenUp === null) {
        $deltaTone = 'flat';
    } else {
        $deltaTone = (((float) $deltaValue > 0) === (bool) $goodWhenUp) ? 'up' : 'down';
    }

    $deltaArrow = $deltaValue === null || (float) $deltaValue == 0.0
        ? null
        : ((float) $deltaValue > 0 ? 'fa-caret-up' : 'fa-caret-down');

    // A single sub-line and a list of them are the same thing to the markup.
    $subLines = $sub === null ? [] : (is_array($sub) ? array_values(array_filter($sub, fn ($l) => $l !== null && $l !== '')) : [$sub]);

    $tileClasses = 'lt-stat'
        .($tone === 'risk' ? ' risk' : '')
        .($muted ? ' is-muted' : '');
@endphp

<div {{ $attributes->merge(['class' => $tileClasses]) }}>
    <div class="lt-stat-value">
        <span>{{ $value }}</span>
        @if ($unit !== null && $unit !== '')
            <span class="lt-stat-unit">{{ $unit }}</span>
        @endif

        @if ($deltaValue !== null)
            <span class="lt-stat-delta {{ $deltaTone }}"
                  @if (! empty($delta['label'])) data-tippy-content="{{ $delta['label'] }}" @endif>
                @if ($deltaArrow)<i class="fa {{ $deltaArrow }}" aria-hidden="true"></i>@endif
                @if ((float) $deltaValue == 0.0)
                    ±0
                @else
                    {{ (float) $deltaValue > 0 ? '+' : '−' }}{{ \Illuminate\Support\Number::format(abs((float) $deltaValue), maxPrecision: 1) }}
                @endif
            </span>
        @endif
    </div>

    <div class="lt-stat-label">
        @if ($icon)<i class="fa {{ $icon }}" aria-hidden="true"></i>@endif
        <span>{{ $label }}</span>
    </div>

    @foreach ($subLines as $subIndex => $subLine)
        <div class="lt-stat-sub @if ($subIndex > 0) lt-stat-sub-more @endif @if ($subTone === 'risk' && $subIndex === 0) risk @endif">{!! $subLine !!}</div>
    @endforeach

    {{ $slot }}
</div>

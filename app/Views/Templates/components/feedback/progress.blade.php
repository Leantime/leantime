{{--
    feedback.progress — a progress bar (project, milestone, goal, board completion).

    Renders the existing `.progress` / `.progress-bar` look with the ARIA progressbar contract,
    so callers never hand-write the role / aria-value / sr-only markup.

    Props:
      value      float   Percent done, 0–100 (clamped).
      tone       string  'default' (theme gradient) | 'info' | 'warning' | 'danger'.
      color      string  Optional custom bar colour (CSS colour, e.g. a milestone's '#1b75bb').
      size       string  'default' | 'sm' (6px, for rows and cards that repeat).
      label      string  Accessible text; default "N% complete".
      showLabel  bool    Also print the label under the bar (12px, muted).

    Anything else (style, id, data-tippy-content, extra classes) passes through to the track.
--}}
@props([
    'value' => 0,
    'tone' => 'default',
    'color' => null,
    'size' => 'default',
    'label' => null,
    'showLabel' => false,
])

@php
    $percent = max(0, min(100, (float) $value));
    $tone = in_array($tone, ['info', 'warning', 'danger'], true) ? $tone : 'success';
    $label = $label ?? sprintf(__('text.percent_complete'), round($percent));
    $barStyle = 'width: ' . $percent . '%;' . ($color ? ' background: ' . $color . ';' : '');
@endphp

<div {{ $attributes->class(['progress', 'lt-progress', 'lt-progress--sm' => $size === 'sm']) }}>
    <div class="progress-bar progress-bar-{{ $tone }}" role="progressbar"
        aria-valuenow="{{ round($percent) }}" aria-valuemin="0" aria-valuemax="100"
        aria-label="{{ $label }}" style="{{ $barStyle }}"></div>
</div>
@if ($showLabel)
    <div class="lt-progress-label">{{ $label }}</div>
@endif

{{--
    navigation.steps — where you are in a multi-step flow (wizard, onboarding, import).

    Numbered dots joined by a line; finished steps show a check, the current one is highlighted
    (aria-current="step"). Labels 12 / semibold (type rule in COMPONENTS.md).

    Props:
      steps        array  The steps, in order. Each is a label string, or
                          ['label' => string, 'href' => ?string, 'description' => ?string].
                          With href a finished step links back to it.
      current      int    1-based position of the current step. Steps before it are done;
                          a value past the last step marks every step done.
      orientation  string 'horizontal' (default) | 'vertical' (a rail down the side of a flow).
      label        string Accessible name of the list, default "Progress".

    Anything else (id, class, data-*) passes through to the <ol>.
--}}
@props([
    'steps' => [],
    'current' => 1,
    'orientation' => 'horizontal',
    'label' => null,
])

@php
    $isVertical = $orientation === 'vertical';
    $current = (int) $current;
    $position = 0;
@endphp

<ol aria-label="{{ $label ?? __('label.progress') }}"
    {{ $attributes->class(['lt-steps', 'lt-steps--vertical' => $isVertical]) }}>
    @foreach ($steps as $step)
        @php
            $position++;
            $step = is_array($step) ? $step : ['label' => $step];
            $isDone = $position < $current;
            $isCurrent = $position === $current;
            $href = $isDone ? ($step['href'] ?? null) : null;
        @endphp
        <li @class(['lt-step', 'lt-step--done' => $isDone, 'lt-step--current' => $isCurrent])
            @if ($isCurrent) aria-current="step" @endif>
            @if ($href)<a href="{{ $href }}" class="lt-step-link">@endif
                <span class="lt-step-dot" aria-hidden="true">
                    @if ($isDone)
                        <i class="fa fa-check"></i>
                    @else
                        {{ $position }}
                    @endif
                </span>
                <span class="lt-step-text">
                    <span class="lt-step-label">{{ $step['label'] }}</span>
                    @if (! empty($step['description']))
                        <span class="lt-step-desc">{{ $step['description'] }}</span>
                    @endif
                    @if ($isDone)
                        <span class="sr-only">({{ __('status.done') }})</span>
                    @endif
                </span>
            @if ($href)</a>@endif
        </li>
    @endforeach
</ol>

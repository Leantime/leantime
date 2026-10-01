{{--
    One tab button inside x-global::navigation.tabs.

    Props:
      name      string  REQUIRED. Pairs the tab with its panel
                        ({group}-tab-{name} controls {group}-panel-{name}).
      icon      string  Optional Font Awesome classes (e.g. "fa-users").
      count     mixed   Optional count badge; countLabel gives it an
                        accessible name ("7 people" instead of bare "7").
      selected  bool    Server-rendered initial selection (exactly one tab
                        per group should pass true).
--}}
@props([
    'name',
    'icon' => null,
    'count' => null,
    'countLabel' => null,
    'selected' => false,
])
@aware(['group'])

<button type="button"
        role="tab"
        id="{{ $group }}-tab-{{ $name }}"
        aria-controls="{{ $group }}-panel-{{ $name }}"
        aria-selected="{{ $selected ? 'true' : 'false' }}"
        tabindex="{{ $selected ? '0' : '-1' }}"
        data-tab-name="{{ $name }}"
        {{ $attributes->merge(['class' => 'lt-tab'.($selected ? ' on' : '')]) }}>
    @if ($icon)<i class="fa {{ $icon }}" aria-hidden="true"></i> @endif{{ $slot }}@if ($count !== null) <span class="ct" @if ($countLabel) aria-label="{{ $countLabel }}" @endif>{{ $count }}</span>@endif
</button>

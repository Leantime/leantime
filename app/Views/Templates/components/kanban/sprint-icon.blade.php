@props([
    'label' => '',
    'size' => 'md'
])

@php
$sizes = ['sm' => '14px', 'md' => '16px', 'lg' => '20px'];
$fontSize = $sizes[$size] ?? '16px';
@endphp

<span {{ $attributes->merge(['class' => 'sprint-icon']) }}
      style="font-size: {{ $fontSize }}; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; width: 24px;"
      data-tippy-content="{{ sprintf(__('label.sprint_x'), $label) }}"
      role="img"
      aria-label="{{ sprintf(__('label.sprint_x'), $label) }}">🏃</span>

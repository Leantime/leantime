@props([
    'type' => null
])

@php
if (!$type) {
    return;
}

$configs = [
    'dueSoon' => ['icon' => '⏳', 'label' => 'Due Soon - Within 3 days'],
    'overdue' => ['icon' => '⏰', 'label' => 'Overdue - Past due date'],
    'stale' => ['icon' => '💤', 'label' => 'Stale - No activity for 14+ days']
];

$config = $configs[$type] ?? null;
if (!$config) {
    return;
}
@endphp

<span {{ $attributes->merge(['class' => 'time-indicator']) }}
      style="font-size: 16px; min-width: 24px; min-height: 24px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"
      title="{{ $config['label'] }}"
      aria-label="{{ $config['label'] }}">{{ $config['icon'] }}</span>

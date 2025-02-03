@props([
    'icon' => '',
    'fill' => false,
    'size' => 'md',
])

@php
    $leantimeEntityTypeMaps = [
        'bug' => 'bug_report',
        'subtask' => 'account_tree',
        'story' => 'book',
        'task' => 'task',
    ];

    if (key_exists($icon, $leantimeEntityTypeMaps)) {
        $iconMapped = $leantimeEntityTypeMaps[$icon];
    } else {
        $iconMapped = '<i class=' . $icon . '></i>';
    }

@endphp

<span
    {{ $attributes->merge(['class' => 'h-' . $size . ' w-' . $size . ' icon material-symbols-rounded' . ($fill !== false ? ' fill ' : '')]) }}>{!! $iconMapped !!}</span>

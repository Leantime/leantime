@props([
    'icon' => ''
])

@php
    $leantimeEntityTypeMaps = [
        'bug' => 'bug_report',
        'subtask' => 'account_tree',
        'story' => 'book',
        'task' => 'task',
    ];

    if(key_exists($icon, $leantimeEntityTypeMaps)) {
        $iconMapped = $leantimeEntityTypeMaps[$icon];
    }else{
        $iconMapped = $icon;
    }

@endphp

<span {{ $attributes->merge([ 'class' => 'material-symbols-rounded' ]) }}>{{ $iconMapped }}</span>

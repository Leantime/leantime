@props([
    'userId' => null,
    'username' => '',
    'size' => 'md',
])

@php
    $sizeMap = [
        'xs' => ['dim' => '24px', 'font' => '11px'],
        'sm' => ['dim' => '28px', 'font' => '13px'],
        'md' => ['dim' => '32px', 'font' => '14px'],
        'lg' => ['dim' => '40px', 'font' => '16px'],
        'xl' => ['dim' => '50px', 'font' => '20px'],
    ];

    $s = $sizeMap[$size] ?? $sizeMap['md'];

    $initials = '';
    if ($username) {
        $parts = explode(' ', trim($username));
        $initials = strtoupper(substr($parts[0], 0, 1));
        if (count($parts) > 1) {
            $initials .= strtoupper(substr(end($parts), 0, 1));
        }
    }

    $useColor = $username && $username !== 'Unassigned';
    $defaultBg = '#D1D5DB';
    $textColor = $useColor ? '#FFFFFF' : '#6B7280';
@endphp

<div {{ $attributes->merge(['class' => 'user-avatar']) }}
     style="display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; flex-shrink: 0; background: {{ $useColor ? 'var(--accent1)' : $defaultBg }}; width: {{ $s['dim'] }}; height: {{ $s['dim'] }}; font-size: {{ $s['font'] }};"
     @if($username) data-tippy-content="{{ $username }}" @endif>
    <span style="font-weight: 600; color: {{ $textColor }};">{{ $initials }}</span>
</div>

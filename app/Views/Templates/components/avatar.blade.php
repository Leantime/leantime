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
    $defaultColor = ['bg' => '#D1D5DB', 'text' => '#6B7280'];
    $colors = $useColor ? ['text' => '#FFFFFF'] : $defaultColor;

    // Deterministic avatar gradient based on username hash — avoids dependency on theme accent colors
    $avatarGradients = [
        ['#3A5F8A', '#5B8DB8'],
        ['#5B4B85', '#8B7BB5'],
        ['#3A6B4A', '#5E9B6E'],
        ['#7A5F3A', '#A88B5E'],
        ['#6A4B5A', '#9B7B8A'],
        ['#3A6A7A', '#5E9BAA'],
        ['#5E5A7E', '#8E8AAE'],
        ['#4A6A5E', '#7A9A8E'],
    ];
    $hashIndex = $useColor ? (crc32($username) & 0x7FFFFFFF) % count($avatarGradients) : 0;
    $bgStyle = $useColor
        ? 'linear-gradient(135deg, ' . $avatarGradients[$hashIndex][0] . ' 0%, ' . $avatarGradients[$hashIndex][1] . ' 100%)'
        : $defaultColor['bg'];
@endphp

<div {{ $attributes->merge(['class' => 'user-avatar']) }}
     style="display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; flex-shrink: 0; background: {{ $bgStyle }}; width: {{ $s['dim'] }}; height: {{ $s['dim'] }}; font-size: {{ $s['font'] }};"
     @if($username) data-tippy-content="{{ $username }}" @endif>
    <span style="font-weight: 600; color: {{ $colors['text'] }};">{{ $initials }}</span>
</div>

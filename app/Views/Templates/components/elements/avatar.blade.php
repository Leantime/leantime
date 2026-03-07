@props([
    'userId' => null,
    'username' => '',
    'size' => 'md',
    'scale' => null,
])

@php
    $resolvedSize = match($scale ?? $size) {
        'xs' => 'xs',
        's', 'sm' => 'sm',
        'm', 'md' => 'md',
        'l', 'lg' => 'lg',
        'xl' => 'xl',
        default => 'md',
    };

    $sizeMap = [
        'xs' => ['dim' => '24px', 'font' => '11px'],
        'sm' => ['dim' => '28px', 'font' => '13px'],
        'md' => ['dim' => '32px', 'font' => '14px'],
        'lg' => ['dim' => '40px', 'font' => '16px'],
        'xl' => ['dim' => '50px', 'font' => '20px'],
    ];

    $s = $sizeMap[$resolvedSize] ?? $sizeMap['md'];

    $initials = '';
    if ($username) {
        $parts = explode(' ', trim($username));
        $initials = strtoupper(substr($parts[0], 0, 1));
        if (count($parts) > 1) {
            $initials .= strtoupper(substr(end($parts), 0, 1));
        }
    }

    $colorPalette = [
        ['bg' => '#6B7A4D', 'text' => '#FFFFFF'],
        ['bg' => '#5C8A8A', 'text' => '#FFFFFF'],
        ['bg' => '#8A6B5C', 'text' => '#FFFFFF'],
        ['bg' => '#7A6B8A', 'text' => '#FFFFFF'],
        ['bg' => '#6B8A7A', 'text' => '#FFFFFF'],
        ['bg' => '#8A7A6B', 'text' => '#FFFFFF'],
    ];

    $defaultColor = ['bg' => '#D1D5DB', 'text' => '#6B7280'];

    if ($username && $username !== 'Unassigned') {
        $hash = crc32($username);
        $colorIndex = abs($hash) % count($colorPalette);
        $colors = $colorPalette[$colorIndex];
    } else {
        $colors = $defaultColor;
    }
@endphp

<div {{ $attributes->merge(['class' => 'user-avatar tw:inline-flex tw:items-center tw:justify-center tw:rounded-full tw:shrink-0']) }}
     style="background-color: {{ $colors['bg'] }}; width: {{ $s['dim'] }}; height: {{ $s['dim'] }}; font-size: {{ $s['font'] }};"
     @if($username) data-tippy-content="{{ $username }}" @endif>
    @if($userId)
        <img src="{{ BASE_URL }}/api/users?profileImage={{ $userId }}"
             alt="{{ $username }}"
             class="tw:w-full tw:h-full tw:rounded-full tw:object-cover"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
             loading="lazy">
        <span style="font-weight: 600; color: {{ $colors['text'] }}; display: none;">{{ $initials }}</span>
    @else
        <span style="font-weight: 600; color: {{ $colors['text'] }};">{{ $initials }}</span>
    @endif
</div>

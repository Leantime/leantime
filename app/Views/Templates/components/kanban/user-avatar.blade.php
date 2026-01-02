@props([
    'userId' => null,
    'username' => '',
    'size' => 'md'
])

@php
$sizeMap = [
    'sm' => ['width' => '28px', 'height' => '28px', 'fontSize' => '13px'],
    'md' => ['width' => '32px', 'height' => '32px', 'fontSize' => '14px'],
    'lg' => ['width' => '40px', 'height' => '40px', 'fontSize' => '16px']
];

$sizeStyles = $sizeMap[$size] ?? $sizeMap['md'];

$initials = '';
if ($username) {
    $parts = explode(' ', $username);
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr($parts[1], 0, 1));
    }
}

// Generate consistent color based on username
$colorPalette = [
    ['bg' => '#6B7A4D', 'text' => '#FFFFFF'], // Olive green
    ['bg' => '#5C8A8A', 'text' => '#FFFFFF'], // Teal
    ['bg' => '#8A6B5C', 'text' => '#FFFFFF'], // Brown
    ['bg' => '#7A6B8A', 'text' => '#FFFFFF'], // Purple
    ['bg' => '#6B8A7A', 'text' => '#FFFFFF'], // Sage
    ['bg' => '#8A7A6B', 'text' => '#FFFFFF'], // Tan
];

$defaultColor = ['bg' => '#D1D5DB', 'text' => '#6B7280']; // Gray for unassigned

if ($username && $username !== 'Unassigned') {
    // Use hash to consistently assign color to same user
    $hash = crc32($username);
    $colorIndex = abs($hash) % count($colorPalette);
    $colors = $colorPalette[$colorIndex];
} else {
    $colors = $defaultColor;
}
@endphp

<div {{ $attributes->merge(['class' => 'user-avatar']) }}
     style="display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background-color: {{ $colors['bg'] }}; width: {{ $sizeStyles['width'] }}; height: {{ $sizeStyles['height'] }}; font-size: {{ $sizeStyles['fontSize'] }}; flex-shrink: 0;"
     title="{{ $username }}">
    @if($userId)
        <img src="{{ BASE_URL }}/api/users?profileImage={{ $userId }}"
             alt="{{ $username }}"
             style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
             loading="lazy">
        <span style="font-weight: 600; color: {{ $colors['text'] }}; display: none;">{{ $initials }}</span>
    @else
        <span style="font-weight: 600; color: {{ $colors['text'] }};">{{ $initials }}</span>
    @endif
</div>

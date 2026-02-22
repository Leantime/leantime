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

$useGradient = $username && $username !== 'Unassigned';
$defaultColor = ['bg' => '#D1D5DB', 'text' => '#6B7280'];
$colors = $useGradient ? ['bg' => '', 'text' => '#FFFFFF'] : $defaultColor;
@endphp

<div {{ $attributes->merge(['class' => 'user-avatar']) }}
     style="display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: {{ $useGradient ? 'var(--element-gradient)' : $colors['bg'] }}; width: {{ $sizeStyles['width'] }}; height: {{ $sizeStyles['height'] }}; font-size: {{ $sizeStyles['fontSize'] }}; flex-shrink: 0;"
     data-tippy-content="{{ $username }}">
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

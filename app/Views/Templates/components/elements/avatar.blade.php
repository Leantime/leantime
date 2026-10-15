{{--
    elements.avatar — a user's round profile picture.

    The image comes from /api/users?profileImage=…, which already falls back to the user's initials
    when they have no photo, and to a neutral placeholder when there is no user (unassigned).

    Props:
      userId   int|string  The user; empty / 0 shows the unassigned placeholder.
      name     string      Display name: alt text and tooltip.
      size     string      'xs' 20px | 'sm' 24px | 'md' 32px (default) | 'lg' 40px | 'xl' 64px.
      version  mixed       The user's `modified` date or timestamp, to refresh the cached image after an upload.

    For an overlapping row (a project's team) wrap avatars in `<div class="lt-avatar-stack">`.
    Anything else (class, data-*) passes through to the <img>.
--}}
@props([
    'userId' => null,
    'name' => '',
    'size' => 'md',
    'version' => null,
])

@php
    $hasUser = $userId !== null && $userId !== '' && (string) $userId !== '0';
    $size = in_array($size, ['xs', 'sm', 'md', 'lg', 'xl'], true) ? $size : 'md';
    $src = BASE_URL . '/api/users?profileImage=' . ($hasUser ? rawurlencode((string) $userId) : 'false');
    if ($hasUser && $version !== null && $version !== '') {
        $src .= '&v=' . (is_numeric($version) ? $version : format($version)->timestamp());
    }
@endphp

<img src="{{ $src }}" alt="{{ $name }}" loading="lazy"
    @if ($name !== '') data-tippy-content="{{ $name }}" @endif
    {{ $attributes->class(['lt-avatar', 'lt-avatar--' . $size]) }} />

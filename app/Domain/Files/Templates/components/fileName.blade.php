{{--
    A stored file's display name: the original name, with the extension appended only when it
    is missing (uploads since v3.5.4 store the full name, so always appending showed "x.txt.txt").
    Usage: <x-files::fileName :file="$file" />
--}}
@props(['file'])

@php
    $displayName = \Leantime\Core\Files\FileManager::displayName((string) ($file['realName'] ?? ''), (string) ($file['extension'] ?? ''));
@endphp

<span {{ $attributes->merge(['class' => 'filename']) }} title="{{ $displayName }}">{{ $displayName }}</span>

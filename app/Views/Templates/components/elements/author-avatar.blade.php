@props([
    'userId' => null,  // author's user id (empty = no author)
    'name' => '',      // author's display name, shown as a tooltip
])

{{--
    elements.author-avatar — who created a canvas / goal / idea item, shown at the bottom of its card.
    Read-only: an item's author can't be changed (the server rejects it), so this is not a picker.
    Keeps the card-footer classes the author chip used, so cards look the same; the picture is
    elements.avatar (sm).
--}}
@php
    $hasAuthor = $userId !== null && $userId !== '' && (string) $userId !== '0';
@endphp
<div {{ $attributes->class(['authorAvatar', 'ticketDropdown', 'userDropdown', 'noBg', 'show', 'right', 'lastDropdown']) }}>
    <a class="f-left" @if ($name !== '') title="{{ $name }}" aria-label="{{ $name }}" @endif>
        <span class="text"><x-global::elements.avatar :user-id="$hasAuthor ? $userId : null" size="sm" /></span>
    </a>
</div>

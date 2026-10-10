@props([
    'itemId' => '',
    'title' => '',
    'description' => '', // rich text (HTML); sanitized here before output
    'editUrl' => '',
    'deleteUrl' => '',
    'commentUrl' => '',
    'commentCount' => 0,
    'avatarUrl' => '',
    'authorId' => null,
    'authorName' => '',
    'dotColor' => 'grey',
    'canEdit' => false,
])

@php
    $dotClass = match($dotColor) {
        'blue' => 'sf-dot--blue',
        'orange' => 'sf-dot--orange',
        'green' => 'sf-dot--green',
        'red' => 'sf-dot--red',
        default => 'sf-dot--grey',
    };
@endphp

<div class="sf-item" id="item_{{ $itemId }}">
    @if ($canEdit && $editUrl)
        <x-global::actions.dropdown style="float:right; margin-left:4px;">
            <li class="nav-header">{{ __('subtitles.edit') }}</li>
            <li><a href="{{ $editUrl }}" data="item_{{ $itemId }}">{!! __('links.edit_canvas_item') !!}</a></li>
            @if ($deleteUrl)
                <li><a href="{{ $deleteUrl }}" class="delete" data="item_{{ $itemId }}">{!! __('links.delete_canvas_item') !!}</a></li>
            @endif

        </x-global::actions.dropdown>
    @endif

    <div class="sf-item-title">
        <span class="sf-dot {{ $dotClass }}"></span>
        @if ($editUrl)
            <a href="{{ $editUrl }}" data="item_{{ $itemId }}">{{ $title }}</a>
        @else
            {{ $title }}
        @endif
    </div>

    @if ($description)
        <div class="sf-item-desc">{!! app(\Leantime\Core\UI\Template::class)->escapeMinimal($description) !!}</div>
    @endif

    <div class="sf-item-foot">
        @if ($authorId || $authorName)
            <x-global::avatar :userId="$authorId" :username="$authorName" size="sm" />
        @elseif ($avatarUrl)
            <img class="sf-avatar" src="{{ $avatarUrl }}" width="18" />
        @endif
        @if ($commentCount > 0 && $commentUrl)
            <span class="sf-meta">
                <a href="{{ $commentUrl }}" class="commentCountLink" data="item_{{ $itemId }}">
                    <i class="fa-regular fa-comment"></i>
                </a>
                {{ $commentCount }}
            </span>
        @endif
        {{ $slot }}
    </div>
</div>

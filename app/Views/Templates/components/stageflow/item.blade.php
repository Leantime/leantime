@props([
    'itemId' => '',
    'title' => '',
    'description' => '',
    'editUrl' => '',
    'deleteUrl' => '',
    'commentUrl' => '',
    'commentCount' => 0,
    'avatarUrl' => '',
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
        <div class="inlineDropDownContainer" style="float:right; margin-left:4px;">
            <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
            </a>
            <ul class="dropdown-menu">
                <li class="nav-header">{{ __('subtitles.edit') }}</li>
                <li><a href="{{ $editUrl }}" data="item_{{ $itemId }}">{{ __('links.edit_canvas_item') }}</a></li>
                @if ($deleteUrl)
                    <li><a href="{{ $deleteUrl }}" class="delete" data="item_{{ $itemId }}">{{ __('links.delete_canvas_item') }}</a></li>
                @endif
            </ul>
        </div>
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
        <div class="sf-item-desc">{!! $description !!}</div>
    @endif

    <div class="sf-item-foot">
        @if ($avatarUrl)
            <img class="sf-avatar" src="{{ $avatarUrl }}" width="22" />
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

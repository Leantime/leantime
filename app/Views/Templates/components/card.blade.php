@props([
    'itemId' => '',
    'title' => '',
    'description' => '',
    'variant' => '',
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

{{--
    Card — a reusable content tile (board item, dashboard to-do, ticket/goal/
    milestone card, …). Standalone: it does NOT depend on <x-global::column> and
    can be dropped into any list. Type-specific cards live as variants under
    components/card/ (e.g. <x-global::card.ticket>) and pass `variant` so the
    surface picks up a `lt-card--{variant}` modifier.

    The `sf-item*` classes are kept alongside the `lt-*` ones so the StrategyPro
    plugin (which scrapes the rendered board for PNG/PDF export) keeps matching
    during the migration window — remove them once the plugin is updated.
--}}
@include('global::components.card-styles')

@php
    $dotModifier = match ($dotColor) {
        'blue' => 'lt-card-dot--blue',
        'orange' => 'lt-card-dot--orange',
        'green' => 'lt-card-dot--green',
        'red' => 'lt-card-dot--red',
        default => 'lt-card-dot--grey',
    };
@endphp

<div {{ $attributes->merge(['class' => 'lt-card sf-item' . ($variant ? ' lt-card--' . $variant : '')]) }} id="item_{{ $itemId }}">
    @if ($canEdit && $editUrl)
        <div class="inlineDropDownContainer" style="float:right; margin-left:4px;">
            <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
            </a>
            <ul class="dropdown-menu">
                <li class="nav-header">{{ __('subtitles.edit') }}</li>
                <li><a href="{{ $editUrl }}" data="item_{{ $itemId }}">{!! __('links.edit_canvas_item') !!}</a></li>
                @if ($deleteUrl)
                    <li><a href="{{ $deleteUrl }}" class="delete" data="item_{{ $itemId }}">{!! __('links.delete_canvas_item') !!}</a></li>
                @endif
            </ul>
        </div>
    @endif

    <div class="lt-card-title sf-item-title">
        <span class="lt-card-dot {{ $dotModifier }}"></span>
        @if ($editUrl)
            <a href="{{ $editUrl }}" data="item_{{ $itemId }}">{{ $title }}</a>
        @else
            {{ $title }}
        @endif
    </div>

    @if ($description)
        <div class="lt-card-desc sf-item-desc">{!! $description !!}</div>
    @endif

    <div class="lt-card-foot sf-item-foot">
        @if ($authorId || $authorName)
            <x-global::avatar :userId="$authorId" :username="$authorName" size="sm" />
        @elseif ($avatarUrl)
            <img class="lt-card-avatar" src="{{ $avatarUrl }}" width="18" />
        @endif
        @if ($commentCount > 0 && $commentUrl)
            <span class="lt-card-meta sf-meta">
                <a href="{{ $commentUrl }}" class="commentCountLink" data="item_{{ $itemId }}">
                    <i class="fa-regular fa-comment"></i>
                </a>
                {{ $commentCount }}
            </span>
        @endif
        {{ $slot }}
    </div>
</div>

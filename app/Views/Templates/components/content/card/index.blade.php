@props([
    'image' => '',
    'cardTitle' => '',
    'cardAction' => '',
    'contentRole' => 'default',
    'variation' => 'item',
    'cardContextButtons' => ''
])

@php
    switch($contentRole) {
        case "primary":
            $contentClass = "bg-primary text-primary-content";
            break;
        case "secondary":
             $contentClass = "bg-primary text-primary-content";
            break;
        case "ghost":
        case "tertiary":
             $contentClass = "bg-ghost text-ghost-content";
            break;
        case "accent":
             $contentClass = "bg-accent text-accent-content";
            break;
       default:
           $contentClass = "";
    }
@endphp

@switch($variation)
    @case('item')
        <div class="card bg-base-100 card-compact shadow-md mb-2 border {{ $contentClass }}">
        @break
    @case('content')
        <div class="card bg-base-100/90 mix-blend-luminosity card-compact shadow-lg mb-2 border {{ $contentClass }}">
        @break
    @case('widget')
        <div class="card bg-base-100/90 card-compact shadow-md mb-2 border {{ $contentClass }}">
        @break
@endswitch

    @if($image)
        <figure>
            {{ $image }}
        </figure>
    @endif
    <div class="card-body">
        @if($cardContextButtons)
            <div class="top-2.5 right-2 absolute">
                {!! $cardContextButtons !!}
            </div>
        @endif
        @if(!empty($cardTitle))
            <h2 class="card-title text-base-content font-normal">{{ $cardTitle }}</h2>
        @endif
        {{ $slot }}
        @if($cardAction)
            <div class="card-actions justify-end">
                {{ $cardAction }}
            </div>
        @endif
    </div>
</div>

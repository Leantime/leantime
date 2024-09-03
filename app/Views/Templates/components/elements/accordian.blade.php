@props([
    'title' => 'Accordion Title',  
    'open' => false,              
])

<div class="collapse {{ $open ? 'collapse-open' : 'collapse-close' }} {{ $attributes->merge(['class' => 'border border-base-300 bg-base-100 rounded-box']) }}">
    <input type="checkbox" class="peer" {{ $open ? 'checked' : '' }} />
    <div class="collapse-title text-xl font-medium">
        {{ $title }}
    </div>
    <div class="collapse-content">
        {{ $slot }}
    </div>
</div>

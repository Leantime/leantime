@props([])

<div {{ $attributes->merge(['class' => 'maincontent']) }}>
    <div class="maincontentinner">
        {{ $slot }}
    </div>
</div>

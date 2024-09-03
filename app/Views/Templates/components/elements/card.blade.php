@props([
    'title' => '',
    'content' => '',
    'image' => '',
    'footer' => '',
])

<div {{ $attributes->merge(['class' => 'card bg-base-100 shadow-xl']) }}>
    @if($image)
        <figure>
            <img src="{{ $image }}" alt="{{ $title }}" />
        </figure>
    @endif
    <div class="card-body">
        @if($title)
            <h2 class="card-title">{{ $title }}</h2>
        @endif
        <p>{{ $content }}</p>
        <div class="card-actions justify-end">
            {{ $footer }}
        </div>
    </div>
</div>


{{-- Basic Card with Title, Content, and Footer --}}

{{-- <x-card 
    title="Card Title"
    content="This is the content of the card."
    image="https://via.placeholder.com/150"
    class="max-w-sm"
>
    <x-slot:footer>
        <button class="btn btn-primary">Learn More</button>
    </x-slot:footer>
</x-card> --}}


{{-- Card Without an Image --}}

{{-- <x-card 
    title="Another Card Title"
    content="This card does not have an image."
    class="max-w-md"
>
    <x-slot:footer>
        <a href="#" class="btn btn-secondary">Get Started</a>
    </x-slot:footer>
</x-card> --}}


@props([
    'src' => '',                 // URL for the avatar image
    'alt' => 'Avatar',           // Alt text for the avatar image
    'size' => 'w-12 h-12',       // Size of the avatar (default is medium size)
    'shape' => 'rounded-full',   // Shape of the avatar (default is circular)
    'placeholder' => false,      // Whether to show a placeholder avatar
    'status' => null,            // Status indicator (e.g., 'online', 'offline', etc.)
    'stacked' => false,          // Whether the avatar is part of a stacked group
    'border' => false,           // Whether to show a border around the avatar
])

@if ($stacked)
<div {{ $attributes->merge(['class' => 'avatar-group -space-x-6']) }}>
@endif

<div class="{{ $stacked ? '' : 'avatar' }}">
    <div class="{{ $size }} {{ $shape }} {{ $border ? 'ring ring-primary ring-offset-base-100 ring-offset-2' : '' }}">
        @if ($placeholder && !$src)
            <!-- Avatar with Placeholder -->
            <div class="bg-neutral-focus text-neutral-content {{ $size }}">
                <span>{{ strtoupper($alt[0] ?? 'A') }}</span> <!-- Show the first letter of the alt text -->
            </div>
        @elseif ($src)
            <!-- Basic Avatar -->
            <img src="{{ $src }}" alt="{{ $alt }}" />
        @endif

        @if ($status)
            <!-- Avatar with Status Indicator -->
            <span class="indicator-item indicator-bottom badge badge-{{ $status }}"></span>
        @endif
    </div>
</div>

@if ($stacked)
</div>
@endif

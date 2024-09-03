@props([
    'id' => 'modal-id',           // Unique ID for the modal
    'title' => 'Modal Title',     // Title for the modal header
    'size' => '',                 // Size of the modal (e.g., 'modal-sm', 'modal-lg')
    'closeable' => true,          // Whether the modal can be closed with a button
])

<input type="checkbox" id="{{ $id }}" class="modal-toggle" />

<div class="modal {{ $size }}">
    <div class="modal-box relative">
        @if ($closeable)
            <!-- Close Button -->
            <label for="{{ $id }}" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
        @endif

        <!-- Modal Title -->
        @if ($title)
            <h3 class="text-lg font-bold">{{ $title }}</h3>
        @endif

        <!-- Modal Content -->
        <div class="py-4">
            {{ $slot }}
        </div>

        <!-- Modal Actions -->
        @if (isset($actions))
            <div class="modal-action">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>

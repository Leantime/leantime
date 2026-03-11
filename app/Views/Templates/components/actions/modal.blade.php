@props([
    'id' => null,
    'title' => '',
    'size' => null,
    'scale' => null,
    'closeable' => true,
    'mode' => 'dialog',      // dialog (standalone <dialog>) | content (inside #global-modal-content)
])

@php
    // Naming-doc alias: scale takes precedence, then size
    $resolvedScale = $scale ?? $size ?? 'md';

    // Normalize naming-doc scale to size keywords
    $normalizedSize = match($resolvedScale) {
        's', 'sm' => 'sm',
        'm', 'md' => 'md',
        'l', 'lg' => 'lg',
        'xl'      => 'xl',
        default   => 'md',
    };

    $sizeStyle = match ($normalizedSize) {
        'sm' => 'max-width: 400px;',
        'md' => 'max-width: 600px;',
        'lg' => 'max-width: 900px;',
        'xl' => 'max-width: 1140px;',
        default => 'max-width: 600px;',
    };
@endphp

@if($mode === 'content')
    {{-- Content wrapper for use inside the global modal (#global-modal-content).
         Provides consistent heading and optional footer. --}}
    <div {{ $attributes->merge(['class' => 'modal-content-wrapper']) }}>
        @if ($title)
            <div class="modal-content-header">
                <h1 class="modal-content-title">{{ $title }}</h1>
            </div>
        @endif

        <div class="modal-content-body">
            {{ $slot }}
        </div>

        @isset($actions)
            <div class="modal-content-footer">
                {{ $actions }}
            </div>
        @endisset
    </div>
@else
    {{-- Standalone inline <dialog> element --}}
    <dialog {{ $attributes->merge(['id' => $id, 'class' => 'modal-dialog']) }} style="{{ $sizeStyle }}">
        <div class="modal-content lt-glass">

            @if ($title || $closeable)
                <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center;">
                    @if ($title)
                        <h4 class="modal-title">{{ $title }}</h4>
                    @endif

                    @if ($closeable)
                        <form method="dialog" style="margin: 0;">
                            <x-globals::forms.button variant="icon-only" element="button" leading-visual="close" aria-label="{{ __('label.close') }}" />
                        </form>
                    @endif
                </div>
            @endif

            <div class="modal-body">
                {{ $slot }}
            </div>

            @isset($actions)
                <div class="modal-footer">
                    {{ $actions }}
                </div>
            @endisset
        </div>

        @if ($closeable)
            <form method="dialog" class="modal-backdrop" style="position:fixed;inset:0;background:rgba(0,0,0,0.3);z-index:-1;">
                <button style="opacity:0;width:100%;height:100%;cursor:default;">close</button>
            </form>
        @endif
    </dialog>
@endif

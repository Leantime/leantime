@props([
    'id',
    'title' => '',
    'size' => 'md',
    'closeable' => true,
])

@php
    $sizeClass = match ($size) {
        'sm' => 'tw:max-w-sm',
        'md' => 'tw:max-w-lg',
        'lg' => 'tw:max-w-3xl',
        'xl' => 'tw:max-w-5xl',
        default => 'tw:max-w-lg',
    };
@endphp

<dialog {{ $attributes->merge(['id' => $id, 'class' => 'tw:modal']) }}>
    <div class="tw:modal-box {{ $sizeClass }}">

        @if ($title || $closeable)
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                @if ($title)
                    <h3 style="font-size: var(--font-size-l); font-weight: bold; margin: 0;">{{ $title }}</h3>
                @endif

                @if ($closeable)
                    <form method="dialog" style="margin: 0;">
                        <button class="tw:btn tw:btn-sm tw:btn-circle tw:btn-ghost" aria-label="{{ __('label.close') }}">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </form>
                @endif
            </div>
        @endif

        {{ $slot }}

        @isset($actions)
            <div class="tw:modal-action">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @if ($closeable)
        <form method="dialog" class="tw:modal-backdrop">
            <button>close</button>
        </form>
    @endif
</dialog>

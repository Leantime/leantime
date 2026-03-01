@props([
    'action',
    'message' => null,
    'buttonLabel' => null,
])

<form method="post" action="{{ $action }}">
    @if($message)
        <p style="margin-bottom: 15px;">{{ $message }}</p>
    @else
        {{ $slot }}
    @endif

    <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px;">
        <button type="button" class="btn btn-default" onclick="leantime.modals.closeModal()">
            {{ __('buttons.cancel') }}
        </button>
        <input type="hidden" name="del" value="1" />
        <button type="submit" class="btn btn-danger">
            <x-global::elements.icon name="delete" />
            {{ $buttonLabel ?? __('buttons.delete') }}
        </button>
    </div>
</form>

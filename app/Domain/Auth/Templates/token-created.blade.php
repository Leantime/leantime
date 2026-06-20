<h4 class="widgettitle title-light">{{ __('headlines.token_created') }}</h4>
<p>{{ __('text.copy_token_now') }}</p>
<div class="form-group">
    <input type="text" class="form-control" value="{{ $newToken }}" onclick="this.select();" />
</div>

<div class="align-right">
    <button type="button" class="btn btn-default"
            onclick="leantime.modals.closeModal();">
        {{ __('buttons.close') }}
    </button>
    <button type="button" class="btn btn-primary"
            onclick="leantime.snippets.copyToClipboard('{{ $newToken }}')">
        {{ __('buttons.copy_to_clipboard') }}
    </button>
</div>

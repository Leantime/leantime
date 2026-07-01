<h4 class="widgettitle title-light">{{ __('headlines.token_created') }}</h4>
<p>{{ __('text.copy_token_now') }}</p>
<div class="form-group">
    <x-global::forms.text-input value="{{ $newToken }}" onclick="this.select();" />
</div>

<div class="align-right">
    <x-global::forms.button inputType="button" contentRole="default" onclick="leantime.modals.closeModal();">{{ __('buttons.close') }}</x-global::forms.button>
    <x-global::forms.button inputType="button" contentRole="primary" onclick="leantime.snippets.copyToClipboard('{{ $newToken }}')">{{ __('buttons.copy_to_clipboard') }}</x-global::forms.button>
</div>

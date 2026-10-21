<div id="tokenModal">
<x-global::actions.modal-header>
    <x-slot:title>{{ __('headlines.create_access_token') }}</x-slot:title>
</x-global::actions.modal-header>
<form hx-post="{{ BASE_URL }}/hx/auth/personalTokens/create"
          hx-target="#tokenModal" id="newToken">

        <div class="form-group">
            <x-global::forms.label for="tokenName">{{ __('label.token_name') }}</x-global::forms.label>
            <x-global::forms.text-input id="tokenName" name="name" required />
            <small class="form-text text-muted">
                <br/>{{ __('text.token_name_description') }}
            </small>
        </div>
        <br />
        <div class="align-right">
            <x-global::forms.button inputType="button" contentRole="default" onclick="jQuery('#modal').modal('hide');">{{ __('buttons.close') }}</x-global::forms.button>
            <x-global::forms.button inputType="submit" contentRole="primary">{{ __('buttons.create_token') }}</x-global::forms.button>
        </div>
    </form>
</div>

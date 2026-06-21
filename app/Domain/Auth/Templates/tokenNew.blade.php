<div id="tokenModal">
<h4 class="widgettitle title-light">{{ __('headlines.create_access_token') }}</h4>

    <form hx-post="{{ BASE_URL }}/hx/auth/personalTokens/create"
          hx-target="#tokenModal" id="newToken">

        <div class="form-group">
            <label for="tokenName">{{ __('label.token_name') }}</label>
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

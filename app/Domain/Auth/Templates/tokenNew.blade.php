<div id="tokenModal">
<h4 class="widgettitle title-light">{{ __('headlines.create_access_token') }}</h4>

    <form hx-post="{{ BASE_URL }}/hx/auth/personalTokens/create"
          hx-target="#tokenModal" id="newToken">

        <div class="form-group">
            <label for="tokenName">{{ __('label.token_name') }}</label>
            <input type="text" class="form-control" id="tokenName"
                   name="name" required>
            <small class="form-text text-muted">
                <br/>{{ __('text.token_name_description') }}
            </small>
        </div>
        <br />
        <div class="align-right">
            <button type="button" class="btn btn-default"
                    onclick="jQuery('#modal').modal('hide');">
                {{ __('buttons.close') }}
            </button>
            <button type="submit" class="btn btn-primary">
                {{ __('buttons.create_token') }}
            </button>
        </div>
    </form>
</div>

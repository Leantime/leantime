@fragment('tokens-table')
<div class="row">
    <div class="col-md-12">
        <div>
            <h5 class="subtitle">{{ __('headlines.personal_access_tokens') }}</h5>
            <p>{{ __('text.create_tokens_to_authenticate') }}</p>
            <br />

            <x-global::forms.button tag="a" contentRole="primary" link="#/auth/tokenNew">{{ __('buttons.create_token') }}</x-global::forms.button> <br />

            <div class="clearfix"></div>

            <table class="table table-bordered" id="tokens-table">
                <thead>
                    <tr>
                        <th>{{ __('label.name') }}</th>
                        <th>{{ __('label.last_used') }}</th>
                        <th>{{ __('label.created_on') }}</th>
                        <th>{{ __('label.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($tokens as $token)
                    <tr>
                        <td>{{ $token['name'] }}</td>
                        <td>{{ $token['last_used_at'] ? format($token['last_used_at'])->date() . ' ' . format($token['last_used_at'])->time(): 'Never' }}</td>
                        <td>{{ format($token['created_at'])->date(). ' ' . format($token['created_at'])->time() }}</td>
                        <td>
                            <x-global::forms.button state="danger" class="btn-sm" hx-delete="{{ BASE_URL }}/hx/auth/personalTokens/delete/{{ $token['id'] }}" hx-confirm="{{ __('notifications.confirm_token_delete') }}" hx-target="#personalTokens"><i class="fa fa-trash"></i></x-global::forms.button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endfragment

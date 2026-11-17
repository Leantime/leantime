<div class="row tw-mt-6">
    <div class="col-md-12">
        <x-global::elements.section-header as="h5">{{ __('oauth.headline.connected_apps') }}</x-global::elements.section-header>
        <p>{{ __('oauth.text.connected_apps_intro') }}</p>

        @if (empty($connectedApps))
            <p class="tw-mt-2">{{ sprintf(__('oauth.text.no_connected_apps'), '') }}<code>{{ $mcpUrl }}</code></p>
        @else
            <table class="table table-bordered" id="connected-apps-table">
                <thead>
                    <tr>
                        <th>{{ __('oauth.label.app') }}</th>
                        <th>{{ __('oauth.label.redirects_to') }}</th>
                        <th>{{ __('oauth.label.last_active') }}</th>
                        <th>{{ __('label.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($connectedApps as $connectedApp)
                    <tr>
                        <td>{{ $connectedApp['name'] }}</td>
                        <td>{{ $connectedApp['redirectHost'] }}</td>
                        <td>{{ format($connectedApp['lastActiveAt'])->date() }} {{ format($connectedApp['lastActiveAt'])->time() }}</td>
                        <td>
                            <x-global::forms.button
                                state="danger"
                                class="btn-sm"
                                hx-delete="{{ BASE_URL }}/hx/oauth/connectedApps/disconnect/{{ $connectedApp['clientId'] }}"
                                hx-confirm="{{ sprintf(__('oauth.text.disconnect_confirm'), $connectedApp['name']) }}"
                                hx-target="#connectedApps"
                                :labelText="__('oauth.buttons.disconnect')" />
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

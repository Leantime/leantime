@if($plugin->type !== "system")
    <div style="display: flex; align-items: center; gap: 12px; white-space: nowrap;">
        @if (!$plugin->enabled)
            <a href="{{ BASE_URL }}/plugins/myapps?enable={{ $plugin->id }}"><x-global::elements.icon name="electrical_services" /> {{ __('buttons.enable') }}</a>
            <a href="{{ BASE_URL }}/plugins/myapps?remove={{ $plugin->id }}" class="delete"><x-global::elements.icon name="delete" /> {{ __('buttons.remove') }}</a>
        @else
            <a href="{{ BASE_URL }}/plugins/myapps?disable={{ $plugin->id }}" class="delete"><x-global::elements.icon name="power_off" /> {{ __('buttons.disable') }}</a>
        @endif

        @if ($plugin->enabled && file_exists(APP_ROOT . '/app/Plugins/' . $plugin->foldername . '/Controllers/Settings.php'))
            <a href="{{ BASE_URL }}/{{ $plugin->foldername }}/settings"><x-global::elements.icon name="settings" /> Settings</a>
        @endif
    </div>
@else
    <span style="font-size: var(--font-size-s); color: var(--secondary-font-color); white-space: nowrap;">System Plugin</span>
@endif

@if($plugin->type !== "system")
    <div style="display: flex; align-items: center; gap: 12px; white-space: nowrap;">
        @if (!$plugin->enabled)
            <a href="{{ BASE_URL }}/plugins/myapps?enable={{ $plugin->id }}"><i class="fa-solid fa-plug-circle-check"></i> {{ __('buttons.enable') }}</a>
            <a href="{{ BASE_URL }}/plugins/myapps?remove={{ $plugin->id }}" class="delete"><i class="fa fa-trash"></i> {{ __('buttons.remove') }}</a>
        @else
            <a href="{{ BASE_URL }}/plugins/myapps?disable={{ $plugin->id }}" class="delete"><i class="fa-solid fa-plug-circle-xmark"></i> {{ __('buttons.disable') }}</a>
        @endif

        @if ($plugin->enabled && file_exists(APP_ROOT . '/app/Plugins/' . $plugin->foldername . '/Controllers/Settings.php'))
            <a href="{{ BASE_URL }}/{{ $plugin->foldername }}/settings"><i class="fa fa-cog"></i> Settings</a>
        @endif
    </div>
@else
    <span style="font-size: var(--font-size-s); color: var(--secondary-font-color); white-space: nowrap;">System Plugin</span>
@endif

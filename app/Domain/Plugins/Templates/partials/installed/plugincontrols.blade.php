@if($plugin->type !== "system")
    <div style="padding-top:10px; padding-left:0px;">
        @if (!$plugin->enabled)
            <a href="{{ BASE_URL }}/plugins/myapps?enable={{ $plugin->id }}" class=""><i class="fa-solid fa-plug-circle-check"></i> {{ __('buttons.enable') }}</a> |
            <a href="{{ BASE_URL }}/plugins/myapps?remove={{ $plugin->id }}" class="delete"><i class="fa fa-trash"></i> {{ __('buttons.remove')  }}</a>
        @else
            <a href="{{ BASE_URL }}/plugins/myapps?disable={{ $plugin->id }}" class="delete"><i class="fa-solid fa-plug-circle-xmark"></i> {{ __('buttons.disable')  }}</a>
        @endif

        @if ($plugin->enabled && file_exists(APP_ROOT . '/app/Plugins/' . $plugin->foldername . '/Controllers/Settings.php'))
            <a href="{{ BASE_URL }}/{{ $plugin->foldername }}/settings"><i class="fa fa-cog"></i> Settings</a>
        @endif
    </div>
@else
    <p>System Plugin, cannot be disabled or removed</p>
@endif

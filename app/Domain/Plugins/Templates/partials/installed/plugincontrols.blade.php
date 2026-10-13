@if($plugin->type !== "system")
    <div style="padding-top:10px; padding-left:0px;">
        @if ($plugin->compatible === false)
            {{-- Core doesn't load it on this Leantime version (enabled or not), so it can't be enabled. --}}
            <x-global::elements.badge tone="danger" icon="fa fa-triangle-exclamation">{{ __('label.plugin_incompatible') }}</x-global::elements.badge>
            <div class="tw-text-sm tw-mt-xs tw-mb-xs">{{ sprintf(__('text.plugin_incompatible_hint'), $plugin->requires) }}</div>
        @endif

        @if (!$plugin->enabled)
            @if ($plugin->compatible !== false)
                <a href="javascript:void(0);" data-post-url="{{ BASE_URL }}/plugins/myapps" data-post-field="enable" data-post-value="{{ $plugin->id }}" class=""><i class="fa-solid fa-plug-circle-check"></i> {{ __('buttons.enable') }}</a> |
            @endif
            <a href="javascript:void(0);" data-post-url="{{ BASE_URL }}/plugins/myapps" data-post-field="remove" data-post-value="{{ $plugin->id }}" class="delete"><i class="fa fa-trash"></i> {{ __('buttons.remove')  }}</a>
        @else
            <a href="javascript:void(0);" data-post-url="{{ BASE_URL }}/plugins/myapps" data-post-field="disable" data-post-value="{{ $plugin->id }}" class="delete"><i class="fa-solid fa-plug-circle-xmark"></i> {{ __('buttons.disable')  }}</a>
        @endif

        @if ($plugin->enabled && $plugin->compatible !== false && file_exists(APP_ROOT . '/app/Plugins/' . $plugin->foldername . '/Controllers/Settings.php'))
            <a href="{{ BASE_URL }}/{{ $plugin->foldername }}/settings"><i class="fa fa-cog"></i> Settings</a>
        @endif
    </div>
@else
    <p>System Plugin, cannot be disabled or removed</p>
@endif

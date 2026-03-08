@if($plugin->type !== "system")
    <div class="tw:flex tw:items-center tw:gap-3 tw:whitespace-nowrap">
        @if (!$plugin->enabled)
            <x-globals::forms.button element="a" href="{{ BASE_URL }}/plugins/myapps?enable={{ $plugin->id }}" contentRole="secondary" leadingVisual="electrical_services">{{ __('buttons.enable') }}</x-globals::forms.button>
            <x-globals::forms.button element="a" href="{{ BASE_URL }}/plugins/myapps?remove={{ $plugin->id }}" state="danger" leadingVisual="delete">{{ __('buttons.remove') }}</x-globals::forms.button>
        @else
            <x-globals::forms.button element="a" href="{{ BASE_URL }}/plugins/myapps?disable={{ $plugin->id }}" contentRole="secondary" leadingVisual="power_off">{{ __('buttons.disable') }}</x-globals::forms.button>
        @endif

        @if ($plugin->enabled && file_exists(APP_ROOT . '/app/Plugins/' . $plugin->foldername . '/Controllers/Settings.php'))
            <x-globals::forms.button element="a" href="{{ BASE_URL }}/{{ $plugin->foldername }}/settings" contentRole="secondary" leadingVisual="settings">Settings</x-globals::forms.button>
        @endif
    </div>
@else
    <span class="tw:text-[color:var(--secondary-font-color)] tw:whitespace-nowrap">System Plugin</span>
@endif

{{--
    Project settings → Modules: switch features on or off for everyone in the project.
    Each switch posts modules[id]=0 from the hidden field and modules[id]=1 when checked.
--}}
<form method="post" action="{{ BASE_URL }}/modulemanager/projectModules/{{ $projectId }}">

    <p>{{ __('text.project_modules_intro') }}</p>

    @if (empty($modules))
        <p>{{ __('text.no_project_modules') }}</p>
    @else
        <div class="tw-flex tw-flex-col tw-gap-4 tw-my-4">
            @foreach ($modules as $module)
                <div>
                    <input type="hidden" name="modules[{{ $module['id'] }}]" value="0" />
                    <x-global::forms.checkbox
                        variant="toggle"
                        :name="'modules['.$module['id'].']'"
                        value="1"
                        :checked="$module['enabled']"
                        :description="$module['description']">
                        <i class="{{ $module['icon'] }}"></i> {{ $module['title'] }}
                    </x-global::forms.checkbox>
                </div>
            @endforeach
        </div>

        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" />
    @endif

</form>

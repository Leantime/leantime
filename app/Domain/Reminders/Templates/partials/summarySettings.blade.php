{{--
    "Morning summary" section of the Notifications tab (Hxcontrollers\SummarySettings).

    Fragment "form" is returned after a save and replaces the section.
--}}
@fragment('form')
<div id="reminderSettings">
    <x-global::elements.section-header>{{ __('reminders.settings_heading') }}</x-global::elements.section-header>
    <p><small>{{ __('reminders.settings_description') }}</small></p>

    @include('reminders::partials.schedulerWarning', ['show' => $showSchedulerWarning])

    @if (! $settings['workspaceEnabled'])
        <x-global::feedback.alert tone="info">{{ __('reminders.settings_workspace_off') }}</x-global::feedback.alert>
    @endif

    <form hx-post="{{ BASE_URL }}/hx/reminders/summarySettings/save" hx-target="#reminderSettings" hx-swap="outerHTML">
        <div class="form-group">
            <input type="hidden" name="summary" value="0" />
            <x-global::forms.checkbox variant="toggle" name="summary" value="1" id="reminderSummary" :checked="$settings['summary']"
                :description="__('reminders.settings_summary_description')">{{ __('reminders.settings_summary') }}</x-global::forms.checkbox>
        </div>

        <div class="form-group">
            <label for="reminderSummaryTime">{{ __('reminders.settings_time') }}</label>
            <input type="time" name="summaryTime" id="reminderSummaryTime" value="{{ $settings['summaryTime'] }}" step="60" required />
            <small>{{ sprintf(__('reminders.settings_timezone'), $settings['timezone']) }}</small>
        </div>

        <div class="form-group">
            <label for="reminderTaskPreset">{{ __('reminders.settings_task_preset') }}</label>
            <x-global::forms.select name="taskPreset" id="reminderTaskPreset">
                @foreach ($presets as $preset)
                    <option value="{{ $preset }}" @selected($settings['taskPreset'] === $preset)>{{ __('reminders.preset_'.$preset) }}</option>
                @endforeach
            </x-global::forms.select>
            <small>{{ __('reminders.settings_task_preset_description') }}</small>
        </div>

        @isset($savedMessage)
            <p role="status">{{ $savedMessage }}</p>
        @endisset
        @isset($errorMessage)
            <p role="alert">{{ $errorMessage }}</p>
        @endisset

        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" />
    </form>
</div>
@endfragment

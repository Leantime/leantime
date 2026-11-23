{{--
    "Morning summary" and "Weekly summary" sections of the Notifications tab (Hxcontrollers\SummarySettings).

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

        <x-global::elements.section-header>{{ __('reminders.weekly_settings_heading') }}</x-global::elements.section-header>
        <p><small>{{ __('reminders.weekly_settings_description') }}</small></p>

        <div class="form-group">
            <input type="hidden" name="weekly" value="0" />
            <x-global::forms.checkbox variant="toggle" name="weekly" value="1" id="reminderWeekly" :checked="$settings['weekly']"
                :description="__('reminders.settings_summary_description')">{{ __('reminders.weekly_settings_on') }}</x-global::forms.checkbox>
        </div>

        <div class="form-group">
            <label for="reminderWeeklyDay">{{ __('reminders.weekly_settings_day') }}</label>
            <x-global::forms.select name="weeklyDay" id="reminderWeeklyDay">
                @foreach ($weekDays as $isoDay => $dayName)
                    <option value="{{ $isoDay }}" @selected($settings['weeklyDay'] === $isoDay)>{{ $dayName }}</option>
                @endforeach
            </x-global::forms.select>
        </div>

        <div class="form-group">
            <label for="reminderWeeklyTime">{{ __('reminders.settings_time') }}</label>
            <input type="time" name="weeklyTime" id="reminderWeeklyTime" value="{{ $settings['weeklyTime'] }}" step="60" required />
            <small>{{ sprintf(__('reminders.settings_timezone'), $settings['timezone']) }}</small>
        </div>

        <div class="form-group">
            <input type="hidden" name="weeklyLookBack" value="0" />
            <x-global::forms.checkbox name="weeklyLookBack" value="1" id="reminderWeeklyLookBack" :checked="$settings['weeklyLookBack']">{{ __('reminders.weekly_settings_lookback') }}</x-global::forms.checkbox>
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

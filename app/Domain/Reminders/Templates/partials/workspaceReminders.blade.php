{{--
    "Reminders" section of company settings (Hxcontrollers\WorkspaceReminders).

    Fragment "section" is returned after a save and replaces the section.
--}}
@fragment('section')
<div id="workspaceReminders">
    <x-global::elements.section-header icon="fa fa-list-check">{{ __('reminders.workspace_heading') }}</x-global::elements.section-header>
    <p>{{ __('reminders.workspace_description') }}</p>

    @include('reminders::partials.schedulerWarning', ['show' => $showSchedulerWarning])

    <form hx-post="{{ BASE_URL }}/hx/reminders/workspaceReminders/save" hx-target="#workspaceReminders" hx-swap="outerHTML">
        <div class="form-group">
            <input type="hidden" name="enabled" value="0" />
            <x-global::forms.checkbox variant="toggle" name="enabled" value="1" id="workspaceRemindersEnabled" :checked="$workspaceEnabled"
                :description="__('reminders.workspace_switch_description')">{{ __('reminders.workspace_switch') }}</x-global::forms.checkbox>
        </div>

        @isset($savedMessage)
            <p role="status">{{ $savedMessage }}</p>
        @endisset

        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" />
    </form>
</div>
@endfragment

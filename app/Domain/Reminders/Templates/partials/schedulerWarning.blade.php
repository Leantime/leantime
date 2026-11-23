{{--
    Shown to people who can see company settings when the reminder job hasn't run in the last
    hour, so morning summaries aren't going out.
--}}
@if ($show)
    <x-global::feedback.alert tone="warning" :title="__('reminders.scheduler_warning_title')">
        {{ __('reminders.scheduler_warning') }} <code>php bin/leantime schedule:run</code>
    </x-global::feedback.alert>
@endif

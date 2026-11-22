{{--
    "Your reminders" in a to-do's ⋮ menu: the heading and the choices, which load when the menu
    opens (Hxcontrollers\TaskReminderMenu) and save in place. Used by the open to-do and the
    Coming up panel.
--}}
<li class="nav-header border">{{ __('reminders.your_reminders') }}</li>
<li id="taskReminderMenu-{{ $ticketId }}"
    hx-get="{{ BASE_URL }}/hx/reminders/taskReminderMenu/get?ticketId={{ $ticketId }}"
    hx-trigger="intersect once"
    hx-swap="outerHTML">
    <span class="tw-block tw-px-[10px] tw-py-[3px]">…</span>
</li>

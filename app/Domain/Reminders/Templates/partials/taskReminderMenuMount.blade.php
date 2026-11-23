{{--
    "Remind me" in a to-do's ⋮ menu: one item that drills into the choices in place
    (reminders::partials.taskReminderMenu, loaded when the menu opens). The one implementation used
    by the to-do cards and lists, the open to-do and the Coming up panel.

    Every render gets its own instance id, so the same to-do on a card and in the panel at once
    never share ids (htmx targets and aria references stay with their own menu).
--}}
@php
    $instance = bin2hex(random_bytes(4));
    $menuId = 'taskReminderMenu-'.$ticketId.'-'.$instance;
@endphp
<li id="{{ $menuId }}" class="lt-reminder-menu"
    hx-get="{{ BASE_URL }}/hx/reminders/taskReminderMenu/get?ticketId={{ $ticketId }}&amp;instance={{ $instance }}"
    hx-trigger="intersect once"
    hx-swap="outerHTML">
    <span class="lt-reminder-choice lt-reminder-loading">
        <i class="fa-regular fa-clock" aria-hidden="true"></i>
        <span>{{ __('reminders.remind_me') }}</span>
    </span>
</li>

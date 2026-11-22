{{--
    "Your reminders" in a to-do's ⋮ menu: the heading and the choices, which load when the menu
    opens (Hxcontrollers\TaskReminderMenu) and save in place. The one implementation used by the
    to-do cards and lists, the open to-do and the Coming up panel.

    Every render gets its own instance id, so the same to-do on a card and in the panel at once
    never share ids (htmx targets and aria references stay with their own menu).
--}}
@php
    $instance = bin2hex(random_bytes(4));
    $menuId = 'taskReminderMenu-'.$ticketId.'-'.$instance;
@endphp
<li class="nav-header border" id="{{ $menuId }}-label">{{ __('reminders.your_reminders') }}</li>
<li id="{{ $menuId }}" class="lt-reminder-menu"
    hx-get="{{ BASE_URL }}/hx/reminders/taskReminderMenu/get?ticketId={{ $ticketId }}&amp;instance={{ $instance }}"
    hx-trigger="intersect once"
    hx-swap="outerHTML">
    <div class="loading-text tw-px-[10px]"><p>{{ __('datatables.loadingRecords', 'Loading...') }}</p></div>
</li>

{{--
    "Remind me" inside a to-do's ⋮ menu (Hxcontrollers\TaskReminderMenu).

    Fragment "menu" is the one <li> the mount partial loads. At the top level it is one item,
    "Remind me: {current choice}" ›. Activating it drills in place (core/menus.js): the menu shows
    only the choices, under a "‹ Remind me" back button; Back, ArrowLeft or Escape return to the
    top level. The item is a disclosure (aria-expanded + aria-controls on the choices panel), not
    aria-haspopup: nothing pops up, the menu's own content changes.

    The choices are a radio group labelled "Remind me" (role="radio", aria-checked; not
    menuitemradio, which needs a role="menu" these mixed menus can't be). "Your default" is part of
    the choice's name; the explanation ("This to-do isn't yours…", "Add a due date…") is the group's
    description, not a choice. A tap saves and redraws the <li> at the top level with the new
    label; data-lt-announce is read out and focus returns to the item, the menu still open. Clicks
    inside don't reach Bootstrap, so the menu stays open while drilling and saving.

    Every other loaded menu for the same to-do redraws when its reminder changes (lt:reminders:
    reminder.updated#{to-do}), and every loaded menu when the person's defaults change
    (lt:reminders:defaults.updated); the menu that saved already shows the change.
--}}
@fragment('menu')
@php
    $choices = [
        'default' => 'reminders.preset_default',
        'more' => 'reminders.preset_more',
        'less' => 'reminders.preset_less',
        'off' => 'reminders.preset_off',
    ];
    $menuId = 'taskReminderMenu-'.$ticketId.'-'.$instance;
    $hints = array_values(array_filter([
        ! $reminder['hasDueDate'] ? __('reminders.picker_needs_due_date') : null,
        $reminder['hasDueDate'] && ! $reminder['workspaceEnabled'] ? __('reminders.picker_workspace_off') : null,
        $reminder['hasDueDate'] && $reminder['workspaceEnabled'] && ! $reminder['summary'] ? __('reminders.picker_summary_off') : null,
        $reminder['hasDueDate'] && ! ($reminder['isYours'] ?? true) ? __('reminders.picker_not_yours') : null,
    ]));
    $topLabel = $reminder['hasDueDate']
        ? sprintf(__('reminders.remind_me_current'), __('reminders.short_'.$reminder['preset']))
        : __('reminders.remind_me');
    $vals = fn (array $values) => json_encode(['ticketId' => (string) $ticketId, 'instance' => $instance] + $values, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
@endphp
<li id="{{ $menuId }}" class="lt-reminder-menu" data-lt-announce="{{ $announce ?? '' }}" onclick="event.stopPropagation();"
    hx-get="{{ BASE_URL }}/hx/reminders/taskReminderMenu/get?ticketId={{ $ticketId }}&amp;instance={{ $instance }}"
    hx-trigger="{{ \Leantime\Domain\Reminders\Events\TaskReminderUpdated::CLIENT_EVENT }}#{{ $ticketId }} from:body, {{ \Leantime\Domain\Reminders\Events\ReminderDefaultsUpdated::CLIENT_EVENT }} from:body"
    hx-swap="outerHTML">
    <a href="javascript:void(0);"
       class="lt-reminder-choice lt-reminder-top"
       role="button"
       data-lt-drill-open
       aria-expanded="false"
       aria-controls="{{ $menuId }}-panel">
        <i class="fa-regular fa-clock" aria-hidden="true"></i>
        <span class="lt-reminder-label">{{ $topLabel }}</span>
        <i class="fa fa-chevron-right lt-reminder-chevron" aria-hidden="true"></i>
    </a>

    <div id="{{ $menuId }}-panel" class="lt-reminder-panel" hidden>
        <button type="button" class="lt-reminder-choice lt-reminder-back" data-lt-drill-back aria-label="{{ __('reminders.back') }}">
            <i class="fa fa-chevron-left" aria-hidden="true"></i>
            <span id="{{ $menuId }}-label">{{ __('reminders.remind_me') }}</span>
        </button>

        @if ($hints !== [])
            <p id="{{ $menuId }}-hint" class="lt-reminder-hint">{{ implode(' ', $hints) }}</p>
        @endif

        @if ($reminder['hasDueDate'])
            <div role="radiogroup" aria-labelledby="{{ $menuId }}-label" @if ($hints !== []) aria-describedby="{{ $menuId }}-hint" @endif>
                @foreach ($choices as $preset => $labelKey)
                    <a href="javascript:void(0);"
                       class="lt-reminder-choice"
                       role="radio"
                       aria-checked="{{ $reminder['preset'] === $preset ? 'true' : 'false' }}"
                       hx-post="{{ BASE_URL }}/hx/reminders/taskReminderMenu/choose"
                       hx-vals='{!! $vals(['preset' => $preset]) !!}'
                       hx-target="#{{ $menuId }}"
                       hx-swap="outerHTML">
                        <i class="fa fa-check lt-reminder-check" aria-hidden="true"></i>
                        <span>{{ __($labelKey) }}@if ($reminder['inherited'] === $preset)<span class="lt-reminder-default"> · {{ __('reminders.picker_your_default') }}</span>@endif</span>
                    </a>
                @endforeach
            </div>

            @if ($reminder['chosen'] && ($reminder['isYours'] ?? true))
                <a href="javascript:void(0);"
                   class="lt-reminder-choice lt-reminder-for-all"
                   role="button"
                   hx-post="{{ BASE_URL }}/hx/reminders/taskReminderMenu/choose"
                   hx-vals='{!! $vals(['preset' => $reminder['preset'], 'forAllMyTodos' => '1']) !!}'
                   hx-target="#{{ $menuId }}"
                   hx-swap="outerHTML">
                    <i class="fa fa-check lt-reminder-check" aria-hidden="true"></i>
                    <span>{{ __('reminders.picker_for_all') }}</span>
                </a>
            @endif
        @endif

        @isset($errorMessage)
            <p class="lt-reminder-hint" role="alert">{{ $errorMessage }}</p>
        @endisset
    </div>
</li>
@endfragment

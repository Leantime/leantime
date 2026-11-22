{{--
    "Your reminders" choices inside a to-do's ⋮ menu (Hxcontrollers\TaskReminderMenu).

    Fragment "menu" is the one <li> the mount partial loads. Its choices are a radio group labelled
    by the menu's "Your reminders" heading (role="radio", aria-checked; not menuitemradio, which
    needs a role="menu" these mixed menus can't be). A tap saves and redraws the <li>. "Your
    default" is part of the choice's name; the explanation ("This to-do isn't yours…", "Add a due
    date…") is the group's description, not a choice. After a save, data-lt-announce is read out
    (core/menus.js) and focus returns to the checked choice.
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
    $vals = fn (array $values) => json_encode(['ticketId' => (string) $ticketId, 'instance' => $instance] + $values, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
@endphp
<li id="{{ $menuId }}" class="lt-reminder-menu" data-lt-announce="{{ $announce ?? '' }}">
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
</li>
@endfragment

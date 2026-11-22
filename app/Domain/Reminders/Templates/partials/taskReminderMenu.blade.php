{{--
    "Your reminders" items inside the open to-do's ⋮ menu (Hxcontrollers\TaskReminderMenu).

    Fragment "menu" is one <li> holding the choices as menu links. Tapping one saves it and swaps
    the <li> with the new state: a check marks what applies now, "Your default" marks what the
    to-do follows when you pick nothing. Without a due date there are no choices, only the hint.
    On someone else's to-do the default is off and "Use this for all my to-dos" is hidden.
--}}
@fragment('menu')
@php
    $choices = [
        'default' => 'reminders.preset_default',
        'more' => 'reminders.preset_more',
        'less' => 'reminders.preset_less',
        'off' => 'reminders.preset_off',
    ];
    $endpoint = BASE_URL.'/hx/reminders/taskReminderMenu/choose';
@endphp
<li id="taskReminderMenu-{{ $ticketId }}" class="lt-reminder-menu">
    @if (! $reminder['hasDueDate'])
        <span class="tw-block tw-px-[10px] tw-py-[3px] tw-text-[var(--secondary-font-color)]">{{ __('reminders.picker_needs_due_date') }}</span>
    @else
        @if (! $reminder['workspaceEnabled'])
            <span class="tw-block tw-px-[10px] tw-py-[3px] tw-text-[var(--secondary-font-color)]">{{ __('reminders.picker_workspace_off') }}</span>
        @elseif (! $reminder['summary'])
            <span class="tw-block tw-px-[10px] tw-py-[3px] tw-text-[var(--secondary-font-color)]">{{ __('reminders.picker_summary_off') }}</span>
        @endif
        @if (! ($reminder['isYours'] ?? true))
            <span class="tw-block tw-px-[10px] tw-py-[3px] tw-text-[var(--secondary-font-color)]">{{ __('reminders.picker_not_yours') }}</span>
        @endif

        @foreach ($choices as $preset => $labelKey)
            <a href="javascript:void(0);"
               role="menuitemradio"
               aria-checked="{{ $reminder['preset'] === $preset ? 'true' : 'false' }}"
               hx-post="{{ $endpoint }}"
               hx-vals='{"ticketId": "{{ $ticketId }}", "preset": "{{ $preset }}"}'
               hx-target="#taskReminderMenu-{{ $ticketId }}"
               hx-swap="outerHTML">
                <i @class(['fa fa-check', 'tw-invisible' => $reminder['preset'] !== $preset]) aria-hidden="true"></i>
                {{ __($labelKey) }}
                @if ($reminder['inherited'] === $preset)
                    <small class="tw-text-[var(--secondary-font-color)]">· {{ __('reminders.picker_your_default') }}</small>
                @endif
            </a>
        @endforeach

        @if ($reminder['chosen'] && ($reminder['isYours'] ?? true))
            <a href="javascript:void(0);"
               hx-post="{{ $endpoint }}"
               hx-vals='{"ticketId": "{{ $ticketId }}", "preset": "{{ $reminder['preset'] }}", "forAllMyTodos": "1"}'
               hx-target="#taskReminderMenu-{{ $ticketId }}"
               hx-swap="outerHTML">
                <i class="fa fa-check tw-invisible" aria-hidden="true"></i>
                {{ __('reminders.picker_for_all') }}
            </a>
        @endif

        @isset($errorMessage)
            <span role="alert" class="tw-block tw-px-[10px] tw-py-[3px]">{{ $errorMessage }}</span>
        @endisset
    @endif
</li>
@endfragment

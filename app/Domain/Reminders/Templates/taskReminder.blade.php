{{--
    "Remind me" modal (Controllers\TaskReminder), opened from a to-do's ⋮ menu.

    One radio per choice; the one that applies now is picked. The choice the to-do follows when
    you pick nothing is marked "Your default". Reminders arrive in the morning summary, so the
    modal says when the summary is off for you or for the workspace, and asks for a due date when
    the to-do has none. On someone else's to-do reminders are an opt-in for yourself: "Don't remind
    me" is the default and "Use this for all my to-dos" is hidden.
--}}
@php
    $choices = [
        'default' => 'reminders.preset_default',
        'more' => 'reminders.preset_more',
        'less' => 'reminders.preset_less',
        'off' => 'reminders.preset_off',
    ];
    $canPick = $reminder['hasDueDate'];
@endphp

<x-global::actions.modal-header>
    <x-slot:title>{{ __('reminders.remind_me') }}</x-slot:title>
</x-global::actions.modal-header>

<form method="post" action="{{ BASE_URL }}/reminders/taskReminder/{{ $ticket->id }}" class="formModal">
    <h3>{{ $ticket->headline }}</h3>
    <br />

    @if (! $reminder['workspaceEnabled'])
        <p class="tw-text-[var(--secondary-font-color)]">{{ __('reminders.picker_workspace_off') }}</p>
    @elseif (! $reminder['summary'])
        <p class="tw-text-[var(--secondary-font-color)]">
            {{ __('reminders.picker_summary_off') }}
            <a href="{{ BASE_URL }}/users/editOwn#notifications">{{ __('reminders.picker_summary_off_link') }}</a>
        </p>
    @endif

    @if (! $canPick)
        <p><strong>{{ __('reminders.picker_needs_due_date') }}</strong></p>
    @elseif (! ($reminder['isYours'] ?? true))
        <p>{{ __('reminders.picker_not_yours') }}</p>
    @endif

    <fieldset @disabled(! $canPick) class="tw-border-0 tw-p-0 tw-m-0">
        <legend class="tw-sr-only">{{ __('reminders.remind_me') }}</legend>
        @foreach ($choices as $preset => $labelKey)
            <div class="form-group">
                <x-global::forms.radio name="preset" :value="$preset" :checked="$reminder['preset'] === $preset"
                    :description="$reminder['inherited'] === $preset ? __('reminders.picker_your_default') : null">{{ __($labelKey) }}</x-global::forms.radio>
            </div>
        @endforeach

        @if ($reminder['isYours'] ?? true)
            <div class="form-group">
                <x-global::forms.checkbox name="forAllMyTodos" value="1">{{ __('reminders.picker_for_all') }}</x-global::forms.checkbox>
            </div>
        @endif
    </fieldset>

    <br />
    @if ($canPick)
        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" />
    @endif
    <x-global::forms.button tag="a" class="pull-right" link="javascript:void(0);" onclick="jQuery.nmTop().close();" contentRole="tertiary">{!! __('buttons.back') !!}</x-global::forms.button>
    <div class="clearall"></div>
    <br />
</form>

<script>
    @if ($closeModal)
        jQuery.nmTop().close();
    @endif
</script>

@props([
    'ticket'    => null,
    'showLabel' => false,
])

@php
    /**
     * Due date chip — displays the ticket due date as a colour-coded badge.
     * Clicking opens a hidden flatpickr input which fires HTMX on change.
     *
     * Urgency colour rules:
     *   < 2 days remaining  → state-critical (red)
     *   2–4 days remaining  → state-medium   (yellow)
     *   5+ days or no date  → state-default  (grey)
     */
    $rawDate  = $ticket->dateToFinish ?? '';
    $hasDate  = !empty($rawDate)
        && $rawDate !== '0000-00-00 00:00:00'
        && dtHelper()->isValidDateString($rawDate);

    $state       = 'state-default';
    $displayDate = __('text.anytime');

    if ($hasDate) {
        try {
            $dueDate   = dtHelper()->parseDbDateTime($rawDate)->setToUserTimezone();
            $userNow   = dtHelper()->userNow();
            $daysUntil = $userNow->floatDiffInDays($dueDate, false);

            if ($daysUntil < 2) {
                $state = 'state-critical';
            } elseif ($daysUntil < 5) {
                $state = 'state-medium';
            }

            $displayDate = format($rawDate)->date();
        } catch (\Exception $e) {
            // fall through — keep defaults
        }
    }

    $ticketId  = $ticket->id ?? '';
    $patchUrl  = BASE_URL . '/hx/tickets/ticket/patch/' . $ticketId;
    $inputId   = 'duedate-chip-' . $ticketId;
@endphp

@if($showLabel)
    <label class="control-label">
        <x-global::elements.icon name="alarm" />
        {!! __('label.due') !!}
    </label>
@endif

{{-- Trigger: styled badge, click opens the hidden flatpickr --}}
<span
    class="chip-badge {{ $state }}"
    style="cursor:pointer;"
    onclick="document.getElementById('{{ $inputId }}').dispatchEvent(new MouseEvent('click', {bubbles:true}));"
    data-tippy-content="{{ __('label.due') }}: {{ $displayDate }}"
>
    <span class="chip-icon material-symbols-rounded">alarm</span>
    <span id="{{ $inputId }}-display">{{ $displayDate }}</span>
</span>

{{-- Hidden date input — flatpickr attaches to this, HTMX fires on change --}}
<input
    type="text"
    id="{{ $inputId }}"
    name="dateToFinish"
    value="{{ $hasDate ? format($rawDate)->date() : '' }}"
    class="duedates secretInput"
    data-id="{{ $ticketId }}"
    hx-post="{{ $patchUrl }}"
    hx-trigger="change"
    hx-swap="none"
    autocomplete="off"
    style="position:absolute;opacity:0;pointer-events:none;width:1px;height:1px;"
/>

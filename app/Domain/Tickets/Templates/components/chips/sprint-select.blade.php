@props([
    'ticket'    => null,
    'sprints'   => [],
    'showLabel' => false,
])

@php
    $ticketId = $ticket->id ?? '';
    $patchUrl = BASE_URL . '/hx/tickets/ticket/patch/' . $ticketId;
    $hxVals   = json_encode(['id' => (string) $ticketId]);
@endphp

@if($showLabel)
    <label class="control-label">
        <x-global::elements.icon name="restart_alt" />
        {!! __('label.sprint') !!}
    </label>
@endif

<x-globals::forms.select
    variant="chip"
    name="sprint"
    :id="'sprint-chip-' . $ticketId"
    hx-post="{{ $patchUrl }}"
    hx-trigger="change"
    hx-swap="none"
    hx-vals="{{ $hxVals }}"
>
    @php
        $backlogLabel = __('label.backlog');
        $backlogHtml  = '<span class="chip-badge state-default"><span class="chip-icon material-symbols-rounded">restart_alt</span>' . e($backlogLabel) . '</span>';
        $backlogSel   = (($ticket->sprint ?? '') == '') ? 'selected' : '';
    @endphp
    <option value="" {{ $backlogSel }} data-chip-html="{{ $backlogHtml }}">{{ $backlogLabel }}</option>

    @foreach($sprints as $sprint)
        @if(!is_object($sprint))
            @continue
        @endif
        @php
            $label = e($sprint->name);
            if (!empty($sprint->startDate) && dtHelper()->isValidDateString($sprint->startDate)) {
                $label .= ' (' . format($sprint->startDate)->date() . ' – ' . format($sprint->endDate)->date() . ')';
            }
            $sel      = (string)($ticket->sprint ?? '') === (string)$sprint->id ? 'selected' : '';
            $chipHtml = '<span class="chip-badge state-default"><span class="chip-icon material-symbols-rounded">restart_alt</span>' . $label . '</span>';
        @endphp
        <option value="{{ $sprint->id }}" {{ $sel }} data-chip-html="{{ $chipHtml }}">{{ $label }}</option>
    @endforeach
</x-globals::forms.select>

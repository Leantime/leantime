@props([
    'ticket'     => null,
    'priorities' => [],
    'showLabel'  => false,
])

@php
    $ticketId = $ticket->id ?? '';
    $patchUrl = BASE_URL . '/hx/tickets/ticket/patch/' . $ticketId;
    $hxVals   = json_encode(['id' => (string) $ticketId]);

    $priorityState = [
        '1' => 'state-critical',
        '2' => 'state-high',
        '3' => 'state-medium',
        '4' => 'state-low',
        '5' => 'state-trivial',
    ];
@endphp

@if($showLabel)
    <label class="control-label">
        <x-globals::elements.icon name="emergency_heat" />
        {!! __('label.priority') !!}
    </label>
@endif

<x-globals::forms.select
    variant="chip"
    name="priority"
    :id="'priority-chip-' . $ticketId"
    :hx-post="!empty($ticketId) ? $patchUrl : null"
    :hx-trigger="!empty($ticketId) ? 'change' : null"
    :hx-swap="!empty($ticketId) ? 'none' : null"
    :hx-vals="!empty($ticketId) ? $hxVals : null"
    :hx-include="!empty($ticketId) ? 'this' : null"
>
    @php
        $emptyLabel  = __('label.priority_not_defined');
        $emptyHtml   = '<span class="chip-badge state-default"><span class="chip-icon material-symbols-outlined">local_fire_department</span>' . e($emptyLabel) . '</span>';
        $emptySel    = (($ticket->priority ?? '') == '') ? 'selected' : '';
    @endphp
    <option value="" {{ $emptySel }} data-chip-html="{{ $emptyHtml }}">{{ $emptyLabel }}</option>

    @foreach($priorities as $key => $label)
        @php
            $state    = $priorityState[(string)$key] ?? 'state-default';
            $sel      = (string)($ticket->priority ?? '') === (string)$key ? 'selected' : '';
            $chipHtml = '<span class="chip-badge ' . $state . '"><span class="chip-icon material-symbols-outlined">local_fire_department</span>' . e($label) . '</span>';
        @endphp
        <option value="{{ $key }}" {{ $sel }} data-chip-html="{{ $chipHtml }}">{{ $label }}</option>
    @endforeach
</x-globals::forms.select>

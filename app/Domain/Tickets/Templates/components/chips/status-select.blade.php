@props([
    'ticket'    => null,
    'statuses'  => [],
    'showLabel' => false,
])

@php
    $ticketId = $ticket->id ?? '';
    $patchUrl = BASE_URL . '/hx/tickets/ticket/patch/' . $ticketId;
    $hxVals   = json_encode(['id' => (string) $ticketId]);

    $iconMap = [
        'NEW'        => 'circle',
        'INPROGRESS' => 'clock_loader_40',
        'DONE'       => 'check_circle',
    ];
    $stateMap = [
        'NEW'        => 'state-new',
        'INPROGRESS' => 'state-inprogress',
        'DONE'       => 'state-done',
    ];
@endphp

@if($showLabel)
    <label class="control-label">
        <x-globals::elements.icon name="clock_loader_90" />
        {!! __('label.status') !!}
    </label>
@endif

<x-globals::forms.select
    variant="chip"
    name="status"
    :id="'status-chip-' . $ticketId"
    hx-post="{{ $patchUrl }}"
    hx-trigger="change"
    hx-swap="none"
    hx-vals="{{ $hxVals }}"
>
    @foreach($statuses as $key => $label)
        @php
            $type     = $label['statusType'] ?? 'NEW';
            $icon     = $iconMap[$type] ?? 'circle';
            $state    = $stateMap[$type] ?? 'state-default';
            $name     = $label['name'] ?? $key;
            $sel      = (string)($ticket->status ?? '') === (string)$key ? 'selected' : '';
            $chipHtml = '<span class="chip-badge ' . $state . '"><span class="chip-icon material-symbols-rounded">' . $icon . '</span>' . e($name) . '</span>';
        @endphp
        <option value="{{ $key }}" {{ $sel }} data-chip-html="{{ $chipHtml }}">{{ $name }}</option>
    @endforeach
</x-globals::forms.select>

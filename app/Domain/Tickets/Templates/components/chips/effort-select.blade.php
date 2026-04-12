@props([
    'ticket'    => null,
    'efforts'   => [],
    'showLabel' => false,
])

@php
    $ticketId = $ticket->id ?? '';
    $patchUrl = BASE_URL . '/hx/tickets/ticket/patch/' . $ticketId;
    $hxVals   = json_encode(['id' => (string) $ticketId]);
@endphp

@if($showLabel)
    <label class="control-label">
        <x-globals::elements.icon name="elevation" />
        {!! __('label.effort') !!}
    </label>
@endif

<x-globals::forms.select
    variant="chip"
    name="storypoints"
    :id="'effort-chip-' . $ticketId"
    :hx-post="!empty($ticketId) ? $patchUrl : null"
    :hx-trigger="!empty($ticketId) ? 'change' : null"
    :hx-swap="!empty($ticketId) ? 'none' : null"
    :hx-vals="!empty($ticketId) ? $hxVals : null"
    :hx-include="!empty($ticketId) ? 'this' : null"
>
    @php
        $emptyLabel = __('label.effort_not_defined');
        $emptyHtml  = '<span class="chip-badge state-default"><span class="chip-icon material-symbols-outlined">elevation</span>' . e($emptyLabel) . '</span>';
        $emptySel   = (($ticket->storypoints ?? '') == '') ? 'selected' : '';
    @endphp
    <option value="" {{ $emptySel }} data-chip-html="{{ $emptyHtml }}">{{ $emptyLabel }}</option>

    @foreach($efforts as $key => $label)
        @php
            $sel      = (string)($ticket->storypoints ?? '') === (string)$key ? 'selected' : '';
            $chipHtml = '<span class="chip-badge state-default"><span class="chip-icon material-symbols-outlined">elevation</span>' . e($label) . '</span>';
        @endphp
        <option value="{{ $key }}" {{ $sel }} data-chip-html="{{ $chipHtml }}">{{ $label }}</option>
    @endforeach
</x-globals::forms.select>

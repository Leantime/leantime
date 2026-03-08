@props([
    'ticket'      => null,
    'ticketTypes' => [],
    'showLabel'   => false,
])

@php
    $ticketId = $ticket->id ?? '';
    $patchUrl = BASE_URL . '/hx/tickets/ticket/patch/' . $ticketId;
    $hxVals   = json_encode(['id' => (string) $ticketId]);

    $typeIcon = [
        'task'      => 'task_alt',
        'subtask'   => 'subdirectory_arrow_right',
        'story'     => 'auto_stories',
        'bug'       => 'bug_report',
        'milestone' => 'flag',
    ];
@endphp

@if($showLabel)
    <label class="control-label">
        <x-globals::elements.icon name="category" />
        {!! __('label.todo_type') !!}
    </label>
@endif

<x-globals::forms.select
    variant="chip"
    name="type"
    :id="'type-chip-' . $ticketId"
    hx-post="{{ $patchUrl }}"
    hx-trigger="change"
    hx-swap="none"
    hx-vals="{{ $hxVals }}"
>
    @foreach($ticketTypes as $type)
        @php
            $key      = strtolower($type);
            $icon     = $typeIcon[$key] ?? 'category';
            $labelStr = __('label.' . $key);
            $sel      = strtolower($ticket->type ?? 'task') === $key ? 'selected' : '';
            $chipHtml = '<span class="chip-badge state-default"><span class="chip-icon material-symbols-rounded">' . $icon . '</span>' . e($labelStr) . '</span>';
        @endphp
        <option value="{{ $key }}" {{ $sel }} data-chip-html="{{ $chipHtml }}">{{ $labelStr }}</option>
    @endforeach
</x-globals::forms.select>

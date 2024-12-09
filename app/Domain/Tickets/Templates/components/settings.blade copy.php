<x-global::forms.select
    :label-text="$tpl->__('label.project')"
    name="projectId"
    content-role="secondary"
    hx-post="/tickets/showTicket/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none">
    @foreach ($allAssignedprojects as $project)
        <option value="{{ $project['id'] }}" @selected($ticket->projectId == $project['id'] || session('currentProject') == $project['id'])>
            {{ $tpl->escape($project['name']) }}
        </option>
    @endforeach
</x-global::forms.select>

<x-global::forms.select
    :label-text="$tpl->__('label.related_to')"
    name="dependingTicketId"
    content-role="secondary"
    hx-post="/tickets/showTicket/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none">
    <option value="">{{ $tpl->__('label.not_related') }}</option>
    @if(is_array($tpl->get('ticketParents')))
        @foreach($tpl->get('ticketParents') as $ticketRow)
            <option value="{{ $ticketRow->id }}" @selected($ticket->dependingTicketId == $ticketRow->id)>
                {{ $tpl->escape($ticketRow->headline) }}
            </option>
        @endforeach
    @endif
</x-global::forms.select>

<x-global::forms.select
    :label-text="$tpl->__('label.todo_status')"
    name="status"
    id="status-select"
    content-role="secondary"
    hx-post="/tickets/showTicket/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none">
    @foreach($statusLabels as $key => $label)
        <option value="{{ $key }}" @selected($ticket->status == $key)>
            {{ $tpl->escape($label['name']) }}
        </option>
    @endforeach
</x-global::forms.select>

<x-global::forms.select
    :label-text="$tpl->__('label.todo_type')"
    name="type"
    id="type"
    content-role="secondary"
    hx-post="/tickets/showTicket/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none">
    @foreach($ticketTypes as $types)
        <option value="{{ strtolower($types) }}" @selected(strtolower($types) == strtolower($ticket->type ?? ''))>
            {{ $tpl->__('label.' . strtolower($types)) }}
        </option>
    @endforeach
</x-global::forms.select>

<x-global::forms.select
    :label-text="$tpl->__('label.priority')"
    name="priority"
    id="priority"
    content-role="secondary"
    hx-post="/tickets/showTicket/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none">
    <option value="">{{ $tpl->__('label.priority_not_defined') }}</option>
    @foreach($tpl->get('priorities') as $priorityKey => $priorityValue)
        <option value="{{ $priorityKey }}" @selected($priorityKey == $ticket->priority)>
            {{ $priorityValue }}
        </option>
    @endforeach
</x-global::forms.select>

<x-global::forms.select
    :label-text="$tpl->__('label.effort')"
    name="storypoints"
    id="storypoints"
    content-role="secondary"
    hx-post="/tickets/showTicket/{{ $ticket->id }}"
    hx-trigger="change"
    hx-swap="none">
    <option value="">{{ $tpl->__('label.effort_not_defined') }}</option>
    @foreach($tpl->get('efforts') as $effortKey => $effortValue)
        <option value="{{ $effortKey }}" @selected($effortKey == $ticket->storypoints)>
            {{ $effortValue }}
        </option>
    @endforeach
</x-global::forms.select>

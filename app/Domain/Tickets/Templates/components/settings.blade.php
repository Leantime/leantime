<div class="form-control w-full">
    <label class="label">
        <span class="label-text">{{ $tpl->__('label.project') }}</span>
    </label>
    <select class="select select-bordered w-full" name="projectId" hx-post="/tickets/showTicket/{{ $ticket->id }}"
        hx-trigger="change" hx-swap="none">
        @foreach ($allAssignedprojects as $project)
            <option value="{{ $project['id'] }}" @selected($ticket->projectId == $project['id'] || session('currentProject') == $project['id'])>
                {{ $tpl->escape($project['name']) }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-control w-full">
    <label class="label">
        <span class="label-text">{{ $tpl->__('label.related_to') }}</span>
    </label>
    <select class="select select-bordered w-full" name="dependingTicketId"
        hx-post="/tickets/showTicket/{{ $ticket->id }}" hx-trigger="change" hx-swap="none">
        <option value="">{{ $tpl->__('label.not_related') }}</option>
        @if (is_array($tpl->get('ticketParents')))
            @foreach ($tpl->get('ticketParents') as $ticketRow)
                <option value="{{ $ticketRow->id }}" @selected($ticket->dependingTicketId == $ticketRow->id)>
                    {{ $tpl->escape($ticketRow->headline) }}
                </option>
            @endforeach
        @endif
    </select>
</div>

<div class="form-control w-full">
    <label class="label">
        <span class="label-text">{{ $tpl->__('label.todo_status') }}</span>
    </label>
    <select class="select select-bordered w-full" name="status" id="status-select"
        hx-post="/tickets/showTicket/{{ $ticket->id }}" hx-trigger="change" hx-swap="none">
        @foreach ($statusLabels as $key => $label)
            <option value="{{ $key }}" @selected($ticket->status == $key)>
                {{ $tpl->escape($label['name']) }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-control w-full">
    <label class="label">
        <span class="label-text">{{ $tpl->__('label.todo_type') }}</span>
    </label>
    <select class="select select-bordered w-full" name="type" id="type"
        hx-post="/tickets/showTicket/{{ $ticket->id }}" hx-trigger="change" hx-swap="none">
        @foreach ($ticketTypes as $types)
            <option value="{{ strtolower($types) }}" @selected(strtolower($types) == strtolower($ticket->type ?? ''))>
                {{ $tpl->__('label.' . strtolower($types)) }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-control w-full">
    <label class="label">
        <span class="label-text">{{ $tpl->__('label.priority') }}</span>
    </label>
    <select class="select select-bordered w-full" name="priority" id="priority"
        hx-post="/tickets/showTicket/{{ $ticket->id }}" hx-trigger="change" hx-swap="none">
        <option value="">{{ $tpl->__('label.priority_not_defined') }}</option>
        @foreach ($tpl->get('priorities') as $priorityKey => $priorityValue)
            <option value="{{ $priorityKey }}" @selected($priorityKey == $ticket->priority)>
                {{ $priorityValue }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-control w-full">
    <label class="label">
        <span class="label-text">{{ $tpl->__('label.effort') }}</span>
    </label>
    <select class="select select-bordered w-full" name="storypoints" id="storypoints"
        hx-post="/tickets/showTicket/{{ $ticket->id }}" hx-trigger="change" hx-swap="none">
        <option value="">{{ $tpl->__('label.effort_not_defined') }}</option>
        @foreach ($tpl->get('efforts') as $effortKey => $effortValue)
            <option value="{{ $effortKey }}" @selected($effortKey == $ticket->storypoints)>
                {{ $effortValue }}
            </option>
        @endforeach
    </select>
</div>


<div class="join join-vertical w-full">
  <div class="collapse collapse-arrow join-item border-base-300 border">
    <input type="radio" name="my-accordion-4" checked="checked" />
    <div class="collapse-title text-xl font-medium">Click to open this one and close others</div>
    <div class="collapse-content">
      <p>hello</p>
    </div>
  </div>
  <div class="collapse collapse-arrow join-item border-base-300 border">
    <input type="radio" name="my-accordion-4" />
    <div class="collapse-title text-xl font-medium">Click to open this one and close others</div>
    <div class="collapse-content">
      <p>hello</p>
    </div>
  </div>
  <div class="collapse collapse-arrow join-item border-base-300 border">
    <input type="radio" name="my-accordion-4" />
    <div class="collapse-title text-xl font-medium">Click to open this one and close others</div>
    <div class="collapse-content">
      <p>hello</p>
    </div>
  </div>
</div>




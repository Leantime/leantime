@props(['ticket'])

<div id="ticketSubtasks" hx-get="{{ BASE_URL }}/hx/tickets/subtasks/get?ticketId={{ $ticket->id }}"
    hx-trigger="load" hx-indicator=".subtaskIndicator"></div>
<div class="htmx-indicator subtaskIndicator">
    Loading Subtasks ...<br /><br />
</div>

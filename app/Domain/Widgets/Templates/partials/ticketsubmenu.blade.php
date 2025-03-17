<div class="ticket-submenu">
    <ul class="nav nav-pills">
        <li><a href="#/tickets/{{ $ticket["id"] }}" class=""><i class="fa-solid fa-eye"></i> {{ __("links.view_todo") }}</a></li>
        <li><a href="#/tickets/edit/{{ $ticket["id"] }}" class=""><i class="fa-solid fa-pencil"></i> {{ __("links.edit_todo") }}</a></li>
        <li><a href="#/tickets/delete/{{ $ticket["id"] }}" class=""><i class="fa-solid fa-trash"></i> {{ __("links.delete_todo") }}</a></li>
        @dispatchEvent("beforeMoveTicket", ["ticket"=>$ticket])
        <li><a href="#/tickets/moveTicket/{{ $ticket["id"] }}" class=""><i class="fa-solid fa-arrow-right-arrow-left"></i> {{ __("links.move_todo") }}</a></li>
        @if($allowSubtaskCreation)
        <li><a href="javascript:void(0);"
              onclick="jQuery('#subtask-form-{{$ticket['id']}}').slideToggle();"
              class="add-subtask-link">
              <i class="fa-solid fa-diagram-predecessor"></i>
              Add Subtask
        </a></li>
        @endif
    </ul>
</div>

<div class="ticket-submenu">
    <ul class="nav nav-pills">
        <li><a href="#/tickets/{{ $ticket["id"] }}" class=""><x-global::elements.icon name="visibility" /> {{ __("links.view_todo") }}</a></li>
        <li><a href="#/tickets/edit/{{ $ticket["id"] }}" class=""><x-global::elements.icon name="edit" /> {{ __("links.edit_todo") }}</a></li>
        <li><a href="#/tickets/delete/{{ $ticket["id"] }}" class=""><x-global::elements.icon name="delete" /> {{ __("links.delete_todo") }}</a></li>
        @dispatchEvent("beforeMoveTicket", ["ticket"=>$ticket])
        <li><a href="#/tickets/moveTicket/{{ $ticket["id"] }}" class=""><x-global::elements.icon name="swap_horiz" /> {{ __("links.move_todo") }}</a></li>
        @if($allowSubtaskCreation)
        <li><a href="javascript:void(0);"
              onclick="jQuery('#subtask-form-{{$ticket['id']}}').slideToggle();"
              class="add-subtask-link">
              <x-global::elements.icon name="arrow_back" />
              Add Subtask
        </a></li>
        @endif
    </ul>
</div>

@props([
    'ticket' => false,
    'onTheClock' => false
 ])

@if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor))

    <div class="inlineDropDownContainer" style="float:right;">

        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
        </a>
        <ul class="dropdown-menu">
            <li class="nav-header">{{ __("subtitles.todo") }}</li>
            <li><a href="#/tickets/showTicket/{{ $ticket["id"] }}" class=''><i class="fa fa-edit"></i> {{  __("links.edit_todo") }}</a></li>
            <li><a href="#/tickets/moveTicket/{{ $ticket["id"] }}" class=""><i class="fa-solid fa-arrow-right-arrow-left"></i> {{  __("links.move_todo") }}</a></li>
            <li><a href="#/tickets/delTicket/{{ $ticket["id"] }}" class="delete"><i class="fa fa-trash"></i> {{  __("links.delete_todo") }}</a></li>
            <li class="nav-header border">{{  __("subtitles.track_time") }}</li>
            @include('tickets::includes.timerLink', ['parentTicketId' => $ticket['id'], 'onTheClock' => $onTheClock])
        </ul>
    </div>

@endif

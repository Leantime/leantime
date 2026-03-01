@props([
    'ticket' => false,
    'onTheClock' => false,
    'allowSubtaskCreation' => false
 ])

@if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor))

    <x-globals::actions.dropdown-menu container-class="pull-right">
            <li class="nav-header border">{{ __("subtitles.todo") }}</li>
            @dispatchEvent("beforeShowTicket", ["ticket"=>$ticket])
            <li><a href="#/tickets/showTicket/{{ $ticket["id"] }}" class=''><x-global::elements.icon name="edit" /> {{  __("links.edit_todo") }}</a></li>
            @dispatchEvent("beforeMoveTicket", ["ticket"=>$ticket])
            <li><a href="#/tickets/moveTicket/{{ $ticket["id"] }}" class=""><x-global::elements.icon name="swap_horiz" /> {{  __("links.move_todo") }}</a></li>
            @if($allowSubtaskCreation)
            <li><a  href="javascript:void(0);" onclick="jQuery('#subtask-form-{{$ticket['id']}}').toggle();"
                    class="add-subtask-link">
                  <x-global::elements.icon name="arrow_back" /> Add Subtask</a></li>
            @endif
            @dispatchEvent("beforeDeleteTicket", ["ticket"=>$ticket])
            <li><a href="#/tickets/delTicket/{{ $ticket["id"] }}" class="delete"><x-global::elements.icon name="delete" /> {{  __("links.delete_todo") }}</a></li>


            @dispatchEvent("submenuSection", ["ticket"=>$ticket])

            <li class="nav-header border">{{  __("subtitles.track_time") }}</li>
            @dispatchEvent("beforeTimer", ["ticket"=>$ticket])
            <li class="timerContainer tw:px-[10px]">
                @include('tickets::partials.timerButton', ['parentTicketId' => $ticket['id'], 'onTheClock' => $onTheClock, 'style'=> 'full'])
            </li>
            @dispatchEvent("end")
    </x-globals::actions.dropdown-menu>

@endif

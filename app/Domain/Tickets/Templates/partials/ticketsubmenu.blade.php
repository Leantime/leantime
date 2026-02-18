@props([
    'ticket' => false,
    'onTheClock' => false,
    'allowSubtaskCreation' => false
 ])

@if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor))

    <x-global::elements.dropdown containerClass="tw:float-right">
            <li class="tw:menu-title">{{ __("subtitles.todo") }}</li>
            @dispatchEvent("beforeShowTicket", ["ticket"=>$ticket])
            <li><a href="#/tickets/showTicket/{{ $ticket["id"] }}" class=''><i class="fa fa-edit"></i> {{  __("links.edit_todo") }}</a></li>
            @dispatchEvent("beforeMoveTicket", ["ticket"=>$ticket])
            <li><a href="#/tickets/moveTicket/{{ $ticket["id"] }}" class=""><i class="fa-solid fa-arrow-right-arrow-left"></i> {{  __("links.move_todo") }}</a></li>
            @if($allowSubtaskCreation)
            <li><a  href="javascript:void(0);" onclick="jQuery('#subtask-form-{{$ticket['id']}}').toggle();"
                    class="add-subtask-link">
                  <i class="fa-solid fa-diagram-predecessor"></i> Add Subtask</a></li>
            @endif
            @dispatchEvent("beforeDeleteTicket", ["ticket"=>$ticket])
            <li><a href="#/tickets/delTicket/{{ $ticket["id"] }}" class="delete"><i class="fa fa-trash"></i> {{  __("links.delete_todo") }}</a></li>


            @dispatchEvent("submenuSection", ["ticket"=>$ticket])

            <li class="tw:menu-title border">{{  __("subtitles.track_time") }}</li>
            @dispatchEvent("beforeTimer", ["ticket"=>$ticket])
            <li class="timerContainer tw:px-[10px]">
                @include('tickets::partials.timerButton', ['parentTicketId' => $ticket['id'], 'onTheClock' => $onTheClock, 'style'=> 'full'])
            </li>
            @dispatchEvent("end")
    </x-global::elements.dropdown>

@endif

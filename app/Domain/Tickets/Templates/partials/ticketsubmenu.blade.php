@props([
    'ticket' => false,
    'onTheClock' => false,
    'allowSubtaskCreation' => false
 ])

@canany(['tickets.edit', 'tickets.delete', 'tickets.create', 'timesheets.create'], $ticket['projectId'] ?? null)

    <x-global::actions.dropdown style="float:right;">
        <li class="nav-header">{{ __("subtitles.todo") }}</li>
        @dispatchEvent("beforeShowTicket", ["ticket"=>$ticket])
        @can('tickets.edit', $ticket['projectId'] ?? null)
        <li><a href="#/tickets/showTicket/{{ $ticket["id"] }}" class=''><i class="fa fa-edit"></i> {{  __("links.edit_todo") }}</a></li>
        @dispatchEvent("beforeMoveTicket", ["ticket"=>$ticket])
        <li><a href="#/tickets/moveTicket/{{ $ticket["id"] }}" class=""><i class="fa-solid fa-arrow-right-arrow-left"></i> {{  __("links.move_todo") }}</a></li>
        @endcan
        @if($allowSubtaskCreation && \Illuminate\Support\Facades\Gate::allows('tickets.create', $ticket['projectId'] ?? null))
        <li><a  href="javascript:void(0);" onclick="jQuery('#subtask-form-{{$ticket['id']}}').toggle();"
                class="add-subtask-link">
              <i class="fa-solid fa-diagram-predecessor"></i> {{ __('label.addSubtask', 'Add Subtask') }}</a></li>
        @endif
        @can('tickets.delete', $ticket['projectId'] ?? null)
        @dispatchEvent("beforeDeleteTicket", ["ticket"=>$ticket])
        <li><a href="#/tickets/delTicket/{{ $ticket["id"] }}" class="delete" data-lt-permitted><i class="fa fa-trash"></i> {{  __("links.delete_todo") }}</a></li>
        @endcan


        @dispatchEvent("submenuSection", ["ticket"=>$ticket])

        @can('timesheets.create')
        @module('timesheets', $ticket['projectId'] ?? null)
            <li class="nav-header border">{{  __("subtitles.track_time") }}</li>
            @dispatchEvent("beforeTimer", ["ticket"=>$ticket])
            <li class="timerContainer tw-px-[10px]">
                @include('tickets::partials.timerButton', ['parentTicketId' => $ticket['id'], 'onTheClock' => $onTheClock, 'style'=> 'full'])
            </li>
        @endmodule
        @endcan
        @dispatchEvent("end")

    </x-global::actions.dropdown>

@endcanany

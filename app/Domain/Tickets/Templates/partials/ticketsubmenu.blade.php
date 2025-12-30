@props([
    'ticket' => false,
    'onTheClock' => false,
    'allowSubtaskCreation' => false
 ])

@if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor))
    <div class="inlineDropDownContainer" style="float:right;">

        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
        </a>
        <ul class="dropdown-menu"
            style="{{ ($isFirstColumn ?? false)
                ? 'left:0; right:auto;'
                : (($isLastColumn ?? false)
                    ? 'right:0; left:auto;'
                    : '') }}">
            <li class="nav-header">{{ __("subtitles.todo") }}</li>
            @dispatchEvent("beforeShowTicket", ["ticket"=>$ticket])
            <li><a href="#/tickets/showTicket/{{ $ticket["id"] }}" class=''><i class="fa fa-edit"></i> {{  __("links.edit_todo") }}</a></li>
            @dispatchEvent("beforeMoveTicket", ["ticket"=>$ticket])
            <li><a href="#/tickets/moveTicket/{{ $ticket["id"] }}" class=""><i class="fa-solid fa-arrow-right-arrow-left"></i> {{  __("links.move_todo") }}</a></li>
            @if($allowSubtaskCreation)
            <li><a  href="javascript:void(0);" onclick="jQuery('#subtask-form-{{$ticket['id']}}').toggle();"
                    class="add-subtask-link">
                  <i class="fa-solid fa-diagram-predecessor"></i> Add Subtask</a></li>
            @endif
            <li><form method="POST" action="<?= BASE_URL ?>/tickets/cloneTicket" >
        <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
        <input type="hidden" name="description" value="<?= htmlspecialchars($ticket['description']) ?>">
        <input type="hidden" name="projectId" value="<?= $ticket['projectId'] ?>">
        <input type="hidden" name="status" value="<?= $ticket['status'] ?>">
        <input type="hidden" name="priority" value="<?= $ticket['priority'] ?>">
        <input type="hidden" name="effort" value="<?= $ticket['storypoints'] ?>">
        <input type="hidden" name="dueDate" value="<?=  $ticket['dateToFinish'] ?>">
        <input type="hidden" name="tags" value="<?=  $ticket['tags'] ?>">
        <input type="hidden" name="milestone" value="<?=  $ticket['milestoneid'] ?>">
        <input type="hidden" name="relatedTo" value="<?=  $ticket['dependingTicketId'] ?>">
        <input type="hidden" name="workStart" value="<?=  $ticket['editFrom'] ?>">
        <input type="hidden" name="workEnd" value="<?=  $ticket['editTo'] ?>">
        <input type="hidden" name="planHours" value="<?=  $ticket['planHours'] ?>">
        <input type="hidden" name="hourRemaining" value="<?=  $ticket['hourRemaining'] ?>">
        <button type="submit" class="submit_button"><i class="fa fa-clone"></i> Clone To-Do</button>
    </form></li>
            <li><form method="POST" action="<?= BASE_URL ?>/tickets/pinTicket" >
        <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
        <?php
            $ticketService = app()->make(\Leantime\Domain\Tickets\Services\Tickets::class);
            $isPinned = $ticketService->isTicketPinned($ticket['id'], session('currentProject'));
        ?>
        <button type="submit" class="submit_button">
            <i class="fa fa-thumbtack" style="{{ $isPinned ? 'transform: rotate(45deg);' : '' }}"></i>
            {{ $isPinned ? 'Unpin Ticket' : 'Pin Ticket' }}
        </button>
    </form></li>
            @dispatchEvent("beforeDeleteTicket", ["ticket"=>$ticket])
            <li><a href="#/tickets/delTicket/{{ $ticket["id"] }}" class="delete"><i class="fa fa-trash"></i> {{  __("links.delete_todo") }}</a></li>


            @dispatchEvent("submenuSection", ["ticket"=>$ticket])

            <li class="nav-header border">{{  __("subtitles.track_time") }}</li>
            @dispatchEvent("beforeTimer", ["ticket"=>$ticket])
            <li class="timerContainer tw-px-[10px]">
                @include('tickets::partials.timerButton', ['parentTicketId' => $ticket['id'], 'onTheClock' => $onTheClock, 'style'=> 'full'])
            </li>
            @dispatchEvent("end")
        </ul>
    </div>

@endif
<style>
    .submit_button {
        background-color: var(--secondary-background);
        color: var(--primary-font-color);
        border: none;
        padding-left: 10px;
        width: 100%;
        text-align: left;
        height: 30px;
    }
    .submit_button:hover {
        background-color: var(--dropdown-link-hover-bg);
        cursor: pointer;
        color: var(--dropdown-link-hover-color);
    }

</style>

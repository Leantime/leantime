@props([
    'ticketSubtasks' => [],
    'ticket' => null,
    'priorities' => [],
    'efforts' => [],
    'statusLabels' => [],
])

<ul class="sortableTicketList">
    <li class="">
        <a href="javascript:void(0);" class="quickAddLink" id="subticket_new_link" onclick="jQuery('#subticket_new').removeClass('hideOnLoad').slideDown('fast', function() {jQuery(this).find('input[name=headline]').focus();}); jQuery(this).hide();"><x-globals::elements.icon name="add_circle" /> {{ __("links.add_task") }}</a>
        <div class="ticketBox hideOnLoad" id="subticket_new" >

            <form method="post" class="form-group"
                  hx-post="{{ BASE_URL }}/tickets/subtasks/save?ticketId={{ $ticket->id }}"
                hx-indicator=".htmx-indicator-small"
                hx-swap="none">
                <input type="hidden" value="new" name="subtaskId" />
                <input type="hidden" value="1" name="subtaskSave" />
                <input type="text" name="headline" class="form-control" style="margin-bottom:8px;" placeholder="{{ __("input.placeholders.what_are_you_working_on") }}" />
                <x-globals::forms.button submit type="primary" name="quickadd">{{ __("buttons.save") }}</x-globals::forms.button>
                <div class="htmx-indicator-small" role="status">
                    <x-globals::feedback.loading id="loadingthis" size="25px" />
                </div>
                <input type="hidden" name="dateToFinish" id="dateToFinish" value="" />
                <input type="hidden" name="status" value="3" />
                <input type="hidden" name="sprint" value="{{ session("currentSprint") }}" />
                <a href="javascript:void(0);" onclick="jQuery('#subticket_new').slideUp('fast'); jQuery('#subticket_new_link').show();">
                    {{ __("links.cancel") }}
                </a>
            </form>

            <div class="clearfix"></div>
        </div>
    </li>


    @php

        $sumPlanHours = 0;
        $sumEstHours = 0;

    @endphp


    @foreach ($ticketSubtasks as $subticket)

        @php
            $sumPlanHours = $sumPlanHours + $subticket['planHours'];
            $sumEstHours = $sumEstHours + $subticket['hourRemaining'];

            if ($subticket['dateToFinish'] == "0000-00-00 00:00:00" || $subticket['dateToFinish'] == "1969-12-31 00:00:00") {
                $date = __("text.anytime");
            } else {
                $date = format($subticket['dateToFinish'])->date();
            }

        @endphp

    <li class="ui-state-default" id="ticket_{{ $subticket['id'] }}" >
        <div class="ticketBox fixed priority-border-{{ $subticket['priority'] }}" data-val="{{ $subticket['id'] }}" aria-label="{{ __('label.priority') }}: {{ $priorities[$subticket['priority']] ?? $subticket['priority'] }}" >

            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding:0 10px;">
                <a href="#/tickets/showTicket/{{ $subticket['id'] }}" style="flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $subticket['headline'] }}</a>
                <div style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                    <x-tickets::chips.effort-select
                        :ticket="(object)$subticket"
                        :efforts="$efforts"
                    />
                    <x-tickets::chips.status-select
                        :ticket="(object)$subticket"
                        :statuses="$statusLabels"
                    />
                    @if($login::userIsAtLeast($roles::$editor))
                        <x-globals::actions.dropdown-menu>
                                <li><a href="javascript:void(0);" hx-delete="{{ BASE_URL }}/tickets/subtasks/delete?ticketId={{ $subticket["id"] }}&parentTicket={{ $ticket->id }}" hx-swap="none" class="delete"><x-globals::elements.icon name="delete" /> {{ __("links.delete_todo") }}</a></li>
                        </x-globals::actions.dropdown-menu>
                    @endif
                </div>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:8px 16px; padding:4px 10px 0; font-size:var(--font-size-s); color:var(--secondary-font-color);">
                <span>{{ __("label.due") }} <input type="text" title="{{ __("label.due") }}" value="{{ $date }}" class="duedates secretInput quickDueDates" data-id="{{ $subticket['id'] }}" name="date" style="width:90px;" /></span>
                <span>{{ __("label.planned_hours") }} <input type="text" value="{{ $subticket['planHours'] }}" name="planHours" data-label="planHours-{{ $subticket['id'] }}" class="small-input secretInput asyncInputUpdate" style="width:40px;" /></span>
                <span>{{ __("label.estimated_hours_remaining") }} <input type="text" value="{{ $subticket['hourRemaining'] }}" name="hourRemaining" data-label="hourRemaining-{{ $subticket['id'] }}" class="small-input secretInput asyncInputUpdate" style="width:40px;" /></span>
            </div>
        </div>
    </li>

    @endforeach
</ul>

<script>
    jQuery(document).ready(function(){
        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>

            leantime.ticketsController.initAsyncInputChange();
            leantime.ticketsController.initDueDateTimePickers();

        <?php } else { ?>

            leantime.authController.makeInputReadonly("#global-modal-content");

        <?php } ?>

    });

</script>

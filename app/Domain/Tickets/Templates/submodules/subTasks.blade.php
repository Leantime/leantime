<p>{!! __('text.what_are_subtasks') !!}<br /><br /></p>


<ul class="sortableTicketList" style="margin-bottom:120px;">
    <li class="">
        <a href="javascript:void(0);" class="quickAddLink" id="subticket_new_link" onclick="jQuery('#subticket_new').toggle('fast', function() {jQuery(this).find('input[name=headline]').focus();}); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> {!! __('links.quick_add_todo') !!}</a>
        <div class="ticketBox hideOnLoad" id="subticket_new" >

            <form method="post" class="form-group formModal" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}#substasks">
                <input type="hidden" value="new" name="subtaskId" />
                <input type="hidden" value="1" name="subtaskSave" />
                <x-global::forms.text-input name="headline" title="{{ __('label.headline') }}" style="width:100%" placeholder="{{ __('input.placeholders.what_are_you_working_on') }}" />
                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="quickadd" />
                <input type="hidden" name="dateToFinish" id="dateToFinish" value="" />
                <input type="hidden" name="status" value="3" />
                <input type="hidden" name="sprint" value="{{ session('currentSprint') }}" />
                <x-global::forms.button tag="a" link="javascript:void(0);" onclick="jQuery('#subticket_new').toggle('fast'); jQuery('#subticket_new_link').toggle('fast');" contentRole="tertiary">
                    {!! __('links.cancel') !!}
                </x-global::forms.button>
            </form>

            <div class="clearfix"></div>
        </div>
    </li>


@php
$sumPlanHours = 0;
$sumEstHours = 0;
@endphp
@foreach ($allSubTasks as $subticket)
    @php
    $sumPlanHours = $sumPlanHours + $subticket['planHours'];
    $sumEstHours = $sumEstHours + $subticket['hourRemaining'];

    if ($subticket['dateToFinish'] == '0000-00-00 00:00:00' || $subticket['dateToFinish'] == '1969-12-31 00:00:00' || empty($subticket['dateToFinish'])) {
        $date = __('text.anytime');
    } else {
        $date = format($subticket['dateToFinish'])->date(__('text.anytime'));
    }
    @endphp
    <li class="ui-state-default" id="ticket_{{ $subticket['id'] }}" >
        <div class="ticketBox fixed priority-border-{{ $subticket['priority'] }}" data-val="{{ $subticket['id'] }}" >

            <div class="row">
                <div class="col-md-12" style="padding:0 15px;">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <x-global::actions.dropdown>
                            <li><a href="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}?delSubtask={{ $subticket['id'] }}" class="delete formModal"><i class="fa fa-trash"></i> {!! __('links.delete_todo') !!}</a></li>


                        </x-global::actions.dropdown>
                    @endif

                    <a href="#/tickets/showTicket/{{ $subticket['id'] }}">{{ $tpl->escape($subticket['headline']) }}</a>

                </div>
            </div>
            <div class="row">
                <div class="col-md-9" style="padding:0 15px;">
                    <div class="row">
                        <div class="col-md-4">
                            {!! __('label.due') !!}<input type="text" title="{{ __('label.due') }}" value="{{ $date }}" class="duedates secretInput quickDueDates" data-id="{{ $subticket['id'] }}" name="date" />
                        </div>
                        <div class="col-md-4">
                            {!! __('label.planned_hours') !!}<input type="text" value="{{ $subticket['planHours'] }}" name="planHours" data-label="planHours-{{ $subticket['id'] }}" class="small-input secretInput asyncInputUpdate" style="width:40px"/>
                        </div>
                        <div class="col-md-4">
                            {!! __('label.estimated_hours_remaining') !!}<input type="text" value="{{ $subticket['hourRemaining'] }}" name="hourRemaining" data-label="hourRemaining-{{ $subticket['id'] }}" class="small-input secretInput asyncInputUpdate" style="width:40px"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-3" style="padding-top:3px;" >
                    <div class="right">
                        <x-tickets::chip-effort :ticket-id="$subticket['id']" :storypoints="$subticket['storypoints']" :efforts="$efforts" />

                        @php
                        if (isset($statusLabels[$subticket['status']])) {
                            $class = $statusLabels[$subticket['status']]['class'];
                            $name = $statusLabels[$subticket['status']]['name'];
                        } else {
                            $class = 'label-important';
                            $name = 'new';
                        }
                        @endphp
                        <x-tickets::chip-status :ticket-id="$subticket['id']" :status="$subticket['status']" :labels="$statusLabels" />

                    </div>
                </div>

            </div>
        </div>
    </li>

@endforeach
</ul>


<ul class="sortableTicketList tw:mb-[120px]">
    <li class="">
        <a href="javascript:void(0);" class="quickAddLink" id="subticket_new_link" onclick="jQuery('#subticket_new').toggle('fast', function() {jQuery(this).find('input[name=headline]').focus();}); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> {{ __("links.add_task") }}</a>
        <div class="ticketBox hideOnLoad" id="subticket_new" >

            <form method="post" class="form-group"
                  hx-post="{{ BASE_URL }}/tickets/subtasks/save?ticketId={{ $ticket->id }}"
                hx-indicator=".htmx-indicator-small"
                hx-target="#ticketSubtasks">
                <input type="hidden" value="new" name="subtaskId" />
                <input type="hidden" value="1" name="subtaskSave" />
                <x-global::forms.input name="headline" title="{{ __("label.headline") }}" class="tw:w-full" placeholder="{{ __("input.placeholders.what_are_you_working_on") }}" />
                <x-global::button submit type="primary" name="quickadd">{{ __("buttons.save") }}</x-global::button>
                <div class="htmx-indicator-small">
                    <x-global::loader id="loadingthis" size="25px" />
                </div>
                <input type="hidden" name="dateToFinish" id="dateToFinish" value="" />
                <input type="hidden" name="status" value="3" />
                <input type="hidden" name="sprint" value="{{ session("currentSprint") }}" />
                <a href="javascript:void(0);" onclick="jQuery('#subticket_new').toggle('fast'); jQuery('#subticket_new_link').toggle('fast');">
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
                $date = $tpl->__("text.anytime");
            } else {
                $date = format($subticket['dateToFinish'])->date();
            }

        @endphp

    <li class="ui-state-default" id="ticket_{{ $subticket['id'] }}" >
        <div class="ticketBox fixed priority-border-{{ $subticket['priority'] }}" data-val="{{ $subticket['id'] }}" >

            <div class="tw:px-4 tw:py-0">
                    @if($login::userIsAtLeast($roles::$editor))
                        <x-global::elements.dropdown>
                                <li><a href="javascript:void(0);" hx-delete="{{ BASE_URL }}/tickets/subtasks/delete?ticketId={{ $subticket["id"] }}&parentTicket={{ $ticket->id }}" hx-target="#ticketSubtasks" class="delete"><i class="fa fa-trash"></i> {{ __("links.delete_todo") }}</a></li>
                        </x-global::elements.dropdown>
                   @endif

                    <a href="#/tickets/showTicket/{{ $subticket['id'] }}">{{ $subticket['headline'] }}</a>

            </div>
            <div class="tw:grid tw:md:grid-cols-12 tw:gap-2">
                <div class="tw:md:col-span-9 tw:px-4 tw:py-0">
                    <div class="tw:grid tw:grid-cols-3">
                        <div>
                                {{ __("label.due") }}<input type="text" title="{{ __("label.due") }}" value="{{ $date }}" class="duedates secretInput quickDueDates" data-id="{{ $subticket['id'] }}" name="date" />
                        </div>
                        <div>
                                {{ __("label.planned_hours") }}<input type="text" value="{{ $subticket['planHours'] }}" name="planHours" data-label="planHours-{{ $subticket['id'] }}" class="small-input secretInput asyncInputUpdate" style="width:40px"/>
                        </div>
                        <div>
                                {{ __("label.estimated_hours_remaining") }}<input type="text" value="{{ $subticket['hourRemaining'] }}" name="hourRemaining" data-label="hourRemaining-{{ $subticket['id'] }}" class="small-input secretInput asyncInputUpdate" style="width:40px"/>
                        </div>
                    </div>
                </div>
                <div class="tw:md:col-span-3 tw:pt-[3px]" >
                    <div class="right">
                        <x-global::dropdownPill
                            type="effort"
                            :parentId="$subticket['id']"
                            selectedClass="label-default"
                            :selectedKey="'' . $subticket['storypoints']"
                            :options="$efforts"
                            headerLabel="{{ __('dropdown.how_big_todo') }}"
                        />

                        <x-global::dropdownPill
                            type="status"
                            :parentId="$subticket['id']"
                            :selectedClass="$statusLabels[$subticket['status']]['class'] ?? 'label-important'"
                            :selectedKey="$subticket['status']"
                            :options="$statusLabels"
                            :colorized="true"
                            headerLabel="{{ __('dropdown.choose_status') }}"
                        />
                    </div>
                </div>

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

            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initStatusDropdown();

        <?php } else { ?>

            leantime.authController.makeInputReadonly("#global-modal-content");

        <?php } ?>

    });

</script>


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
                        <div class="inlineDropDownContainer" >
                            <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="javascript:void(0);" hx-delete="{{ BASE_URL }}/tickets/subtasks/delete?ticketId={{ $subticket["id"] }}&parentTicket={{ $ticket->id }}" hx-target="#ticketSubtasks" class="delete"><i class="fa fa-trash"></i> {{ __("links.delete_todo") }}</a></li>
                            </ul>
                        </div>
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
                        <div class="dropdown ticketDropdown effortDropdown show">
                            <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink{{ $subticket['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text">@if ($subticket['storypoints'] != '' && $subticket['storypoints'] > 0 && isset($efforts[$subticket['storypoints']]))
                                                                                        {{ $efforts[$subticket['storypoints']] }}
                                                                                   @else
                                                                                           {{ __("label.story_points_unkown") }}
                                                                                    @endif
                                                                </span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink{{ $subticket['id'] }}">
                                <li class="nav-header border">{{ __("dropdown.how_big_todo") }}</li>
                                @foreach($efforts as $effortKey => $effortValue)
                                    <li class='dropdown-item'>
                                        <a href='javascript:void(0);' data-value='{{  $subticket['id'] }}_{{ $effortKey }}' id='ticketEffortChange{{ $subticket['id'] . $effortKey }}'> {{  $effortValue }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                            @php
                                if (isset($statusLabels[$subticket['status']])) {
                                    $class = $statusLabels[$subticket['status']]["class"];
                                    $name = $statusLabels[$subticket['status']]["name"];
                                } else {
                                    $class = 'label-important';
                                    $name = 'new';
                                }
                             @endphp
                        <div class="dropdown ticketDropdown statusDropdown colorized show">
                            <a class="dropdown-toggle f-left status {{ $class  }}" href="javascript:void(0);" role="button" id="statusDropdownMenuLink{{ $subticket['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text">{{$name }}
                                                                </span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $subticket['id'] }}">
                                <li class="nav-header border">{{ __('dropdown.choose_status') }}</li>

                                    @foreach ($statusLabels as $key => $label)
                                        <li class='dropdown-item'>
                                            <a href='javascript:void(0);' class='{{ $label["class"] }}' data-label='{{ $label["name"] }}' data-value='{{ $subticket['id'] }}_{{ $key }}_{{ $label["class"] }}' id='ticketStatusChange{{ $subticket['id'] . $key }}' >{{ $label["name"] }}</a>
                                        </li>
                                    @endforeach
                            </ul>
                        </div>

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

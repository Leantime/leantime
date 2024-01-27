@props([
    'includeTitle' => true,
    'tickets' => [],
    'onTheClock' => false,
    'groupBy' => '',
    'allProjects' => [],
    'allAssignedprojects' => [],
    'projectFilter' => '',

])

<div class="clear">
    <div class="row" id="yourToDoContainer">
        <div class="col-md-12">
            <div class="tw-mb-l">
                @include("dashboard::partials.createEntityButton")
                <form method="get">
                    @dispatchEvent("beforeTodoWidgetGroupByDropdown")
                    <div class="btn-group viewDropDown right">
                        <button class="btn btn-link dropdown-toggle" type="button" data-toggle="dropdown">{!! __("links.group_by") !!}: {{ __('groupByLabel.'.$groupBy) }}</button>
                        <ul class="dropdown-menu">
                            <li><span class="radio">
                                    <input type="radio" name="groupBy"
                                           @if($groupBy == "time") checked='checked' @endif
                                           value="time" id="groupByDate"
                                           hx-get="{{BASE_URL}}/widgets/myToDos/get"
                                           hx-trigger="click"
                                           hx-target="#yourToDoContainer"
                                           hx-swap="outerHTML"
                                           hx-indicator="#todos .htmx-indicator"
                                           hx-vals='{"projectFilter": {{ $projectFilter }}, "groupBy": "time" }'
                                        />
                                    <label for="groupByDate">{!! __("label.dates") !!}</label></span></li>
                            <li>
                                <span class="radio">
                                    <input type="radio"
                                           name="groupBy"
                                           @if($groupBy == "project") checked='checked' @endif
                                           value="project" id="groupByProject"
                                           hx-get="{{BASE_URL}}/widgets/myToDos/get"
                                           hx-trigger="click"
                                           hx-target="#yourToDoContainer"
                                           hx-swap="outerHTML"
                                           hx-indicator="#todos .htmx-indicator"
                                           hx-vals='{"projectFilter": {{ $projectFilter }}, "groupBy": "project" }'
                                    />
                                    <label for="groupByProject">{!! __("label.project") !!}</label>
                                </span>
                            </li>
                            <li>
                                <span class="radio">
                                    <input type="radio"
                                           name="groupBy"
                                           @if($groupBy == "sprint") checked='checked' @endif
                                           value="sprint" id="groupBySprint"
                                           hx-get="{{BASE_URL}}/widgets/myToDos/get"
                                           hx-trigger="click"
                                           hx-target="#yourToDoContainer"
                                           hx-swap="outerHTML"
                                           hx-indicator="#todos .htmx-indicator"
                                           hx-vals='{"projectFilter": {{ $projectFilter }}, "groupBy": "sprint" }'
                                    />
                                    <label for="groupBySprint">{!! __("label.list") !!}</label>
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="btn-group viewDropDown right">
                        <button class="btn btn-link dropdown-toggle" type="button" data-toggle="dropdown">
                            {!! __("links.filter") !!}
                            @if($projectFilter != '')
                                <span class='badge badge-primary'>1</span>
                                @endif
                        </button>
                        <ul class="dropdown-menu">
                            <li
                                @if($projectFilter == '')
                                    class='active'
                                @endif
                            ><a href=""
                                   hx-get="{{BASE_URL}}/widgets/myToDos/get"
                                   hx-trigger="click"
                                   hx-target="#yourToDoContainer"
                                   hx-swap="outerHTML"
                                   hx-indicator="#todos .htmx-indicator"
                                   hx-vals='{"projectFilter": "", "groupBy": "{{ $groupBy }}" }'

                                >{{ __('labels.all_projects') }}

                                </a></li>

                                @if($allAssignedprojects)
                                    @foreach($allAssignedprojects as $project)
                                        <li
                                                @if($projectFilter == $project['id'])
                                                    class='active'
                                            @endif
                                        ><a href=""
                                            hx-get="{{BASE_URL}}/widgets/myToDos/get"
                                            hx-trigger="click"
                                            hx-target="#yourToDoContainer"
                                            hx-swap="outerHTML"
                                            hx-indicator="#todos .htmx-indicator"
                                            hx-vals='{"projectFilter": "{{ $project['id'] }}", "groupBy": "{{ $groupBy }}" }'
                                            >{{ $project['name'] }}</a></li>
                                    @endforeach
                               @endif

                        </ul>
                    </div>
                    @dispatchEvent("afterTodoWidgetGroupByDropdown")
                    <div class="clearall"></div>
                </form>
            </div>
            <div class="htmx-indicator">
                <x-global::loadingText type="card" count="5" />
            </div>
            <div class="htmx-indicator htmx-loaded-content">
                @if($tickets !== null && count($tickets) == 0)
                <div class='center'>
                    <div  style='width:30%' class='svgContainer'>
                        {!! file_get_contents(ROOT . "/dist/images/svg/undraw_a_moment_to_relax_bbpa.svg") !!}
                    </div>
                    <br />
                    <h4>{{ __("headlines.no_todos_this_week") }}</h4>
                    {{ __("text.take_the_day_off") }}
                    <a href='{{ BASE_URL }}/tickets/showAll'>{{ __("links.goto_backlog") }}</a><br/><br/>
                </div>
                @endif
                @foreach ($tickets as $ticketGroup)

                    @php
                        //Get first duedate if exist
                        $ticketCreationDueDate = '';
                        if (isset($ticketGroup['tickets'][0]) && $ticketGroup['tickets'][0]['dateToFinish'] != "0000-00-00 00:00:00" && $ticketGroup['tickets'][0]['dateToFinish'] != "1969-12-31 00:00:00") {
                            //Use the first due date as the new due date
                            $ticketCreationDueDate = $ticketGroup['tickets'][0]['dateToFinish'];
                        }

                        $groupProjectId = $_SESSION['currentProject'];

                        if ($groupBy == 'project' && isset($ticketGroup['tickets'][0])) {
                            $groupProjectId = $ticketGroup['tickets'][0]['projectId'];
                        }

                    @endphp

                    <a class="anchor" id="accordion_anchor_{{ $loop->index }}"></a>

                    <x-global::accordion id="ticketBox1-{{ $loop->index }}">
                        <x-slot name="title">


                            {{ __($ticketGroup["labelName"]) }} ({{ count($ticketGroup["tickets"]) }})

                        </x-slot>
                        <x-slot name="actionlink">
                            <a href="javascript:void(0);"
                              id="ticket_new_link_<?=$loop->index?>"
                               onclick="jQuery('#ticket_new_link_<?=$loop->index?>').toggle('fast'); jQuery('#ticket_new_<?=$loop->index?>').toggle('fast', function() { jQuery(this).find('input[name=headline]').focus(); });">
                              <i class="fa fa-plus-circle"></i> Add To-Do
                            </a>
                        </x-slot>
                        <x-slot name="content">
                            <div class="hideOnLoad " id="ticket_new_<?=$loop->index?>" style="padding-top:5px; padding-bottom:15px;">

                                <form method="post"
                                    hx-post="{{ BASE_URL }}//widgets/myToDos/addTodo"
                                      hx-target="#yourToDoContainer"
                                      hx-swap="outerHTML"
                                      hx-indicator="#ticket_new_<?=$loop->index?> .htmx-indicator-small"
                                >
                                    <input type="hidden" name="quickadd" value="1"/>
                                    <div class="flex" style="display:flex; column-gap: 10px;">
                                    <input type="text" name="headline" style="width:100%;" placeholder="Enter To-Do Title" title="<?=$tpl->__("label.headline") ?>"/><br />

                                        @if($groupBy == 'project')
                                            <input type="hidden" name="projectId" value="{{ $ticketGroup["groupValue"] }}" />
                                        @else
                                            <label style="padding-top: 8px;">Project</label>
                                            <select name="projectId">
                                                @foreach($allAssignedprojects as $project)
                                                    <option value="{{ $project['id']  }}"
                                                    @if($groupBy == 'sprint')
                                                        {{ explode("-", $ticketGroup["groupValue"])[1] == $project['id'] ? 'selected' : '' }}
                                                    @else
                                                        {{ $_SESSION['currentProject'] == $project['id'] ? 'selected' : '' }}
                                                    @endif
                                                    >{{ $project["name"]  }}</option>
                                                @endforeach
                                            </select>
                                        @endif

                                        @if($groupBy == 'sprint')
                                            <input type="hidden" name="sprint" value="{{ explode("-", $ticketGroup["groupValue"])[0] }}" />
                                        @endif

                                        @if($groupBy == 'time')
                                            <input type="hidden" name="dateToFinish" value="{{ format($ticketGroup["groupValue"])->date() }}" />
                                        @endif
                                    </div>
                                    <input type="submit" value="Save" name="quickadd" />
                                    <a href="javascript:void(0);" class="btn btn-default" onclick="jQuery('#ticket_new_<?=$loop->index?>, #ticket_new_link_<?=$loop->index?>').toggle('fast');">
                                            <?=$tpl->__("links.cancel") ?>
                                    </a>
                                    <div class="htmx-indicator-small">
                                        <x-global::loader id="loadingthis" size="25px" />
                                    </div>
                                </form>

                                <div class="clearfix"></div>
                            </div>
                            <ul class="sortableTicketList {{ $ticketGroup["extraClass"] ?? '' }}">

                                @if (count($ticketGroup['tickets']) == 0)
                                    <em>Nothing to see here. Move on.</em><br /><br />
                                @endif

                                @foreach ($ticketGroup['tickets'] as $row)

                                    <li class="ui-state-default" id="ticket_{{ $row['id'] }}" >
                                        <div class="ticketBox fixed priority-border-{{ $row['priority'] }}" data-val="{{ $row['id'] }}">
                                            <div class="row">
                                                <div class="col-md-8 titleContainer">
                                                    <small>{{ $row['projectName'] }}</small><br />
                                                    @if($row['dependingTicketId'] > 0)
                                                        <a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}">{{ $row['parentHeadline'] }}</a> //
                                                    @endif
                                                    <strong><a href="#/tickets/showTicket/{{ $row['id'] }}" >{{ $row['headline'] }}</a></strong>

                                                </div>
                                                <div class="col-md-4 timerContainer" style="padding:5px 15px;" id="timerContainer-{{ $row['id'] }}">

                                                    @include("tickets::partials.ticketsubmenu", ["ticket" => $row, "onTheClock" => $onTheClock])
                                                    <div class="scheduler pull-right">
                                                        @if( $row['editFrom'] != "0000-00-00 00:00:00" && $row['editFrom'] != "1969-12-31 00:00:00")
                                                            <i class="fa-solid fa-calendar-check infoIcon tw-mr-xs" style="color:var(--accent2)" data-tippy-content="{{ __('text.schedule_to_start_on') }} {{ format($row['editFrom'])->date() }}"></i>
                                                        @else
                                                            <i class="fa-regular fa-calendar-xmark infoIcon tw-mr-xs" data-tippy-content="{{ __('text.not_scheduled_drag_ai') }}"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4" style="padding:0 15px;">

                                                   <i class="fa-solid fa-business-time infoIcon" data-tippy-content=" {{ __("label.due") }}"></i>
                                                   <input type="text" title="{{ __("label.due") }}" value="{{ format($row['dateToFinish'])->date(__("text.anytime")) }}" class="duedates secretInput" data-id="{{ $row['id'] }}" name="date" />
                                                </div>
                                                <div class="col-md-8 dropdownContainer" style="padding-top:5px;">
                                                    <div class="right">

                                                        <div class="dropdown ticketDropdown effortDropdown show">
                                                            <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">
                                                                @if ($row['storypoints'] != '' && $row['storypoints'] > 0)
                                                                    {{ $efforts["" . $row['storypoints']] ?? $row['storypoints'] }}
                                                                @else
                                                                    {{ __("label.story_points_unkown") }}
                                                                @endif
                                                            </span>
                                                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink{{ $row['id'] }}">
                                                                <li class="nav-header border">{{ __("dropdown.how_big_todo") }}</li>
                                                                @foreach($efforts as $effortKey => $effortValue)
                                                                    <li class='dropdown-item'>
                                                                        <a href='javascript:void(0);'
                                                                           data-value='{{ $row['id'] . "_" . $effortKey }}'
                                                                           id='ticketEffortChange{{ $row['id'] . $effortKey }}'>
                                                                            {{ $effortValue }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>


                                                        <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                                            <a style="background-color:{{ $row['milestoneColor'] }}"
                                                               class="dropdown-toggle f-left  label-default milestone"
                                                               href="javascript:void(0);"
                                                               role="button" id="milestoneDropdownMenuLink{{ $row['id'] }}"
                                                               data-toggle="dropdown"
                                                               aria-haspopup="true"
                                                               aria-expanded="false">
                                                                <span class="text">
                                                                    @if($row['milestoneid'] != "" && $row['milestoneid'] != 0)
                                                                        {{ $row['milestoneHeadline'] }}
                                                                    @else
                                                                        {{  __("label.no_milestone") }}
                                                                    @endif
                                                                </span>
                                                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                            </a>
                                                            <ul class="dropdown-menu pull-right" aria-labelledby="milestoneDropdownMenuLink{{ $row['id'] }}">
                                                                <li class="nav-header border">{{ __("dropdown.choose_milestone") }}</li>
                                                                <li class='dropdown-item'>
                                                                    <a style='background-color:#b0b0b0'
                                                                       href='javascript:void(0);'
                                                                       data-label="{{__("label.no_milestone") }}"
                                                                       data-value='{{ $row['id'] }}_0_#b0b0b0'>
                                                                        {{ __("label.no_milestone") }}
                                                                    </a>
                                                                </li>
                                                                @if(isset($milestones[$row['projectId']]))
                                                                    @foreach($milestones[$row['projectId']] as $milestone)
                                                                        @if(is_object($milestone))
                                                                        <li class='dropdown-item'>
                                                                            <a href='javascript:void(0);'
                                                                               data-label='{{ $milestone->headline }}'
                                                                               data-value='{{ $row['id'] }}_{{ $milestone->id }}_{{ $milestone->tags }}'
                                                                               id='ticketMilestoneChange{{ $row['id'] . $milestone->id }}'
                                                                               style='background-color:{{ $milestone->tags }}'>
                                                                                {{ $milestone->headline }}
                                                                            </a>
                                                                        </li>
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                            </ul>
                                                        </div>

                                                        <div class="dropdown ticketDropdown statusDropdown colorized show">
                                                            <a class="dropdown-toggle f-left status {{ $statusLabels[$row['projectId']][$row['status']]["class"] }}" href="javascript:void(0);" role="button" id="statusDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">
                                                                @if(isset($statusLabels[$row['projectId']][$row['status']]))
                                                                    {{ $statusLabels[$row['projectId']][$row['status']]["name"] }}
                                                                @else
                                                                    unknown
                                                                @endif
                                                            </span>
                                                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                            </a>
                                                            <ul class="dropdown-menu pull-right" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                                                                <li class="nav-header border">{{ __("dropdown.choose_status") }}</li>

                                                                @foreach ($statusLabels[$row['projectId']] as $key => $label)
                                                                    <li class='dropdown-item'>
                                                                        <a href='javascript:void(0);'
                                                                           class='{{ $label["class"] }}'
                                                                           data-label='{{ $label["name"] }}'
                                                                           data-value='{{ $row['id'] }}_{{ $key }}_{{ $label["class"] }}'
                                                                           id='ticketStatusChange{{$row['id'] . $key }}'>
                                                                            {{  $label["name"] }}
                                                                        </a>
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
                        </x-slot>
                    </x-global::accordion>
                @endforeach
                @dispatchEvent("afterTodoListWidgetBox")
            </div>
        </div>
    </div>
</div>




<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen');

    jQuery('.todaysDate').text(moment().format('LLLL'));

    jQuery(document).ready(function(){
        tippy('[data-tippy-content]');
        @if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor))
            leantime.dashboardController.prepareHiddenDueDate();
            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initStatusDropdown();
            leantime.ticketsController.initDueDateTimePickers();
        @else
            leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

    });

</script>



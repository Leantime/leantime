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
            <div class="mb-l">
                @if($allAssignedprojects)
                    <form method="get">
                    @dispatchEvent("beforeTodoWidgetGroupByDropdown")

                    <a href="javascript:void(0);"
                       id="ticket_new_link"
                        class="btn btn-primary"
                       onclick="jQuery('#ticket_new').toggle('fast', function() { jQuery(this).find('input[name=headline]').focus(); });">
                        <i class="fa fa-plus"></i> Add To-Do
                    </a>
                    <div class="flex align-center justify-end">
                        <x-global::actions.dropdown contentRole="ghost">
                            <x-slot name="labelText">
                                {!! __("links.group_by") !!}: {{ __('groupByLabel.'.$groupBy) }}
                            </x-slot>
                            <x-slot name="menu">
                                <li><span>
                                    <input type="radio" name="groupBy"
                                           @if($groupBy == "time") checked='checked' @endif
                                           value="time" id="groupByDate"
                                           hx-get="{{BASE_URL}}/hx/widgets/myToDos/get"
                                           hx-trigger="click"
                                           hx-target="#yourToDoContainer"
                                           hx-swap="outerHTML"
                                           hx-indicator="#todos .htmx-indicator"
                                           hx-vals='{"projectFilter": "{{ $projectFilter }}", "groupBy": "time" }'
                                        />
                                    <label for="groupByDate">{!! __("label.dates") !!}</label></span></li>
                            <li>
                                <span>
                                    <input type="radio"
                                           name="groupBy"
                                           @if($groupBy == "project") checked='checked' @endif
                                           value="project" id="groupByProject"
                                           hx-get="{{BASE_URL}}/hx/widgets/myToDos/get"
                                           hx-trigger="click"
                                           hx-target="#yourToDoContainer"
                                           hx-swap="outerHTML"
                                           hx-indicator="#todos .htmx-indicator"
                                           hx-vals='{"projectFilter": "{{ $projectFilter }}", "groupBy": "project" }'
                                    />
                                    <label for="groupByProject">{!! __("label.project") !!}</label>
                                </span>
                            </li>
                            <li>
                                <span>
                                    <input type="radio"
                                           name="groupBy"
                                           @if($groupBy == "priority") checked='checked' @endif
                                           value="priority" id="groupByPriority"
                                           hx-get="{{BASE_URL}}/hx/widgets/myToDos/get"
                                           hx-trigger="click"
                                           hx-target="#yourToDoContainer"
                                           hx-swap="outerHTML"
                                           hx-indicator="#todos .htmx-indicator"
                                           hx-vals='{"projectFilter": "{{ $projectFilter }}", "groupBy": "priority" }'
                                    />
                                    <label for="groupByPriority">{!! __("label.priority") !!}</label>
                                </span>
                            </li>
                            </x-slot>
                        </x-global::actions.dropdown>
                        <x-global::actions.dropdown contentRole="ghost">
                            <x-slot name="labelText">
                                {!! __("links.filter") !!}
                            </x-slot>
                            <x-slot name="menu">
                                <li
                                @if($projectFilter == '')
                                    class='active'
                                @endif
                            ><a href=""
                                hx-get="{{BASE_URL}}/hx/widgets/myToDos/get"
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
                                        hx-get="{{BASE_URL}}/hx/widgets/myToDos/get"
                                        hx-trigger="click"
                                        hx-target="#yourToDoContainer"
                                        hx-swap="outerHTML"
                                        hx-indicator="#todos .htmx-indicator"
                                        hx-vals='{"projectFilter": "{{ $project['id'] }}", "groupBy": "{{ $groupBy }}" }'
                                        >{{ $project['name'] }}</a></li>
                                @endforeach
                            @endif
                            </x-slot>
                        </x-global::actions.dropdown>
                    </div>

                    @dispatchEvent("afterTodoWidgetGroupByDropdown")
                    <div class="clearall"></div>
                </form>
                @endif
            </div>
            <div class="hideOnLoad " id="ticket_new" style="padding-top:5px; padding-bottom:15px;">

                <form method="post"
                      hx-post="{{ BASE_URL }}/hx/widgets/myToDos/addTodo"
                      hx-target="#yourToDoContainer"
                      hx-swap="outerHTML"
                      hx-indicator="#ticket_new .htmx-indicator-small"
                >
                    <input type="hidden" name="quickadd" value="1"/>
                    <div class="flex" style="display:flex; column-gap: 10px;">
                        <x-global::forms.text-input
                            type="text"
                            name="headline"
                            placeholder="Enter To-Do Title"
                            title="{!! $tpl->__('label.headline') !!}"
                            variant="title"
                            class="w-full"
                        />
                        <br />

                        <x-global::forms.select name="projectId" :labelText="'Project'">
                            @foreach($allAssignedprojects as $project)
                                <x-global::forms.select.select-option :value="$project['id']"
                                    :selected="($groupBy == 'sprint' && explode('-', $ticketGroup['groupValue'])[1] == $project['id']) || (session('currentProject') == $project['id'])"
                                >
                                    {{ $project['name'] }}
                                </x-global::forms.select.select-option>
                            @endforeach
                        </x-global::forms.select>

                    </div>
                    <input type="submit" value="Save" name="quickadd" />
                    <a href="javascript:void(0);" class="btn btn-default" onclick="jQuery('#ticket_new').toggle('fast');">
                        <?=$tpl->__("links.cancel") ?>
                    </a>
                    <div class="htmx-indicator-small">
                        <x-global::elements.loader id="loadingthis" size="25px" />
                    </div>
                </form>

                <div class="clearfix"></div>
            </div>
            <div class="htmx-indicator">
                <x-global::elements.loadingText type="card" count="5" />
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
                    @if($allAssignedprojects)
                        <a href='{{ BASE_URL }}/tickets/showAll'>{{ __("links.goto_backlog") }}</a><br/><br/>
                    @endif
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

                        $groupProjectId = session("currentProject");

                        if ($groupBy == 'project' && isset($ticketGroup['tickets'][0])) {
                            $groupProjectId = $ticketGroup['tickets'][0]['projectId'];
                        }

                    @endphp

                    <x-global::content.accordion id="ticketBox1-{{ $loop->index }}">
                        <x-slot name="title">


                            {{ __($ticketGroup["labelName"]) }} ({{ count($ticketGroup["tickets"]) }})

                        </x-slot>
                        <x-slot name="content">

                            <ul class="sortableTicketList {{ $ticketGroup["extraClass"] ?? '' }}">

                                @if (count($ticketGroup['tickets']) == 0)
                                    <em>Nothing to see here. Move on.</em><br /><br />
                                @endif

                                @foreach ($ticketGroup['tickets'] as $row)

                                    <li class="ui-state-default" id="ticket_{{ $row['id'] }}" >
                                        <div class="ticketBox priority-border-{{ $row['priority'] }}" data-val="{{ $row['id'] }}">
                                            <div class="row">
                                                <div class="col-md-8 titleContainer">
                                                    <small>{{ $row['projectName'] }}</small><br />
                                                    @if($row['dependingTicketId'] > 0)
                                                        <a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}">{{ $row['parentHeadline'] }}</a> //
                                                    @endif
                                                    <strong><a href="#/tickets/showTicket/{{ $row['id'] }}" >{{ $row['headline'] }}</a></strong>

                                                </div>
                                                <div class="col-md-4 timerContainer" style="padding:5px 15px;" id="timerContainer-{{ $row['id'] }}">

                                                    @include("tickets::includes.ticketsubmenu", ["ticket" => $row, "onTheClock" => $onTheClock])
                                                    <div class="scheduler pull-right">
                                                        @if( $row['editFrom'] != "0000-00-00 00:00:00" && $row['editFrom'] != "1969-12-31 00:00:00")
                                                            <i class="fa-solid fa-calendar-check infoIcon mr-xs" style="color:var(--accent2)" data-tippy-content="{{ __('text.schedule_to_start_on') }} {{ format($row['editFrom'])->date() }}"></i>
                                                        @else
                                                            <i class="fa-regular fa-calendar-xmark infoIcon mr-xs" data-tippy-content="{{ __('text.not_scheduled_drag_ai') }}"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4" style="padding:0 15px;">
                                                    <div class="date-picker-form-control">
                                                        <i class="fa-solid fa-business-time infoIcon" data-tippy-content="{{ __("label.due") }}"></i>
                                                        <input id="due-date-picker-{{ $row['id'] }}" type="text" title="{{ __("label.due") }}" value="{{ format($row['dateToFinish'])->date(__("text.anytime")) }}" class="duedates secretInput" style="margin-left:0px;" data-id="{{ $row['id'] }}" name="date" />
                                                        <button class="reset-button" data-id="{{ $row['id'] }}" id="reset-date-{{ $row['id'] }}"><span class="sr-only">{{ __("language.resetDate") }}</span><i class="fa fa-close"></i></button>
                                                    </div>
                                                </div>
                                                <div class="col-md-8 dropdownContainer" style="padding-top:5px;">
                                                    <div class="dropdown ticketDropdown statusDropdown colorized show right ">
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

                                                    <?php /*
                                                        <div class="dropdown ticketDropdown effortDropdown show right">
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
                                                    */ ?>

                                                    <div class="dropdown ticketDropdown milestoneDropdown colorized show right mr-sm">
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

                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </x-slot>
                    </x-global::content.accordion>
                @endforeach
                @dispatchEvent("afterTodoListWidgetBox")
            </div>
        </div>
    </div>
</div>




<script type="module">

    @dispatchEvent('scripts.afterOpen');

    import "@mix('/js/Domain/Tickets/Js/ticketsController.js')"
    import "@mix('/js/Domain/Auth/Js/authController.js')"
    import "@mix('/js/Domain/Dashboard/Js/dashboardController.js')"

    jQuery('.todaysDate').text(DateTime.now().toFormat('LLLL'));

    jQuery(document).ready(function(){
        tippy('[data-tippy-content]');
        @if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor))
            dashboardController.prepareHiddenDueDate();
            ticketsController.initEffortDropdown();
            ticketsController.initMilestoneDropdown();
            ticketsController.initStatusDropdown();
            ticketsController.initDueDateTimePickers();
        @else
            authController.makeInputReadonly(".maincontentinner");
        @endif

    });

</script>



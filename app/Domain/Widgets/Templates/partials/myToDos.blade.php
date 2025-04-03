@props([
    'includeTitle' => true,
    'tickets' => [],
    'onTheClock' => false,
    'groupBy' => '',
    'allProjects' => [],
    'allAssignedprojects' => [],
    'projectFilter' => '',
])

@php
    // Helper function to count tickets recursively
    if (!function_exists('countTicketsRecursive')) {
        function countTicketsRecursive($tickets) {
            $count = count($tickets);

            foreach ($tickets as $ticket) {
                if (!empty($ticket['children'])) {
                    $count += countTicketsRecursive($ticket['children']);
                }
            }

            return $count;
        }
    }
@endphp

<div class="htmx-indicator full-width-loader">
    <div class="indeterminate"></div>
</div>

<div id="yourToDoContainer"
     hx-get="{{BASE_URL}}/widgets/myToDos/get"
     hx-trigger="ticket_update from:body"
     class="clear" hx-ext="json-enc">

    <div class="clear" style="position:absolute; top:10px; right:35px;">

        @dispatchEvent("beforeTodoWidgetGroupByDropdown")

        <div class="btn-group left">
            <button class="btn btn-link dropdown-toggle f-left" type="button"
                    data-toggle="dropdown">{!! __("links.group_by") !!}: {{ __('groupByLabel.'.$groupBy) }}</button>
            <ul class="dropdown-menu">
                <li>
                    <span class="radio">
                        <input type="radio" name="groupBy"
                               @if($groupBy == "time") checked='checked' @endif
                               value="time" id="groupByDate"
                               hx-get="{{BASE_URL}}/widgets/myToDos/get"
                               hx-trigger="click"
                               hx-target="#yourToDoContainer"
                               hx-swap="outerHTML"
                               hx-indicator="#todos .htmx-indicator"
                               style="margin-top:4px;"
                               hx-vals='{"projectFilter": "{{ $projectFilter }}", "groupBy": "time" }'
                        />
                        <label for="groupByDate">{!! __("label.dates") !!}</label>
                    </span>
                </li>
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
                               style="margin-top:4px;"
                               hx-vals='{"projectFilter": "{{ $projectFilter }}", "groupBy": "project" }'
                        />
                        <label for="groupByProject">{!! __("label.project") !!}</label>
                    </span>
                </li>
                <li>
                    <span class="radio">
                        <input type="radio"
                               name="groupBy"
                               @if($groupBy == "priority") checked='checked' @endif
                               value="priority" id="groupByPriority"
                               hx-get="{{BASE_URL}}/widgets/myToDos/get"
                               hx-trigger="click"
                               hx-target="#yourToDoContainer"
                               hx-swap="outerHTML"
                               hx-indicator="#todos .htmx-indicator"
                               style="margin-top:4px;"
                               hx-vals='{"projectFilter": "{{ $projectFilter }}", "groupBy": "priority" }'
                        />
                        <label for="groupByPriority">{!! __("label.priority") !!}</label>
                    </span>
                </li>
            </ul>
        </div>
        <div class="btn-group left ">
            <button class="btn btn-link dropdown-toggle f-right" type="button" data-toggle="dropdown">
                {!! __("links.filter") !!}
                @if($projectFilter != '')
                    <span class='badge badge-primary'>1</span>
                @endif
            </button>
            <ul class="dropdown-menu pull-right">
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
                    hx-vals='{"projectFilter": "all", "groupBy": "{{ $groupBy }}" }'

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

    </div>

    <div class="tw-flex tw-flex-col">

        <div class="">
            @if($tickets !== null && count($tickets) == 0)

                <div class='center'>
                    <div style='width:30%' class='svgContainer'>
                        {!! file_get_contents(ROOT . "/dist/images/svg/undraw_a_moment_to_relax_bbpa.svg") !!}
                    </div>
                    <br/>
                    <h4>{{ __("text.no_tasks_assigned") }}</h4>
                    <a href="javascript:void(0);" class="add-task-button btn btn-link" style="margin-left:0px;" data-group="emptyGroup"><i class="fa-solid fa-circle-plus"></i> {{ __('links.add_task') }}</a>

                    <div class="quickAddForm" id="quickAddForm-emptyGroup"
                         style="display:none; margin-bottom:15px; padding-bottom:5px; padding-left:5px;">
                        <form method="post"
                              hx-post="{{ BASE_URL }}/widgets/myToDos/addTodo"
                              hx-target="#yourToDoContainer"
                              hx-swap="outerHTML"
                              hx-indicator=".htmx-indicator">
                            <div class="tw-flex tw-flex-row tw-gap-2">
                                <div class="tw-flex-grow">
                                    <input type="text" name="headline" class="main-title-input"
                                           style="font-size:var(--base-font-size)"
                                           placeholder="{{ __('input.placeholders.what_are_you_working_on') }}"/>
                                    <input type="hidden" name="quickadd" value="true"/>
                                </div>
                                <div>
                                    <select name="projectId">
                                        @foreach($allAssignedprojects as $project)
                                            <option value="{{ $project['id']  }}"

                                                {{ (session('currentProject') == $project['id'] ) ? 'selected' : '' }}
                                            >{{ $project["name"]  }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <input type="hidden" name="milestone" value=""/>
                                    <input type="hidden" name="status" value="3"/>
                                    <input type="hidden" name="priority"
                                           value=""/>
                                    <input type="hidden" name="dateToFinish"
                                           value="{{ date('Y-m-d', strtotime('next friday'))}}"/>
                                    <textarea name="description" class="description-input" style="display:none;"
                                              placeholder="{{ __('input.placeholders.description') }}"></textarea>
                                </div>
                                <div>
                                    <input type="submit" value="{{ __('buttons.save') }}" name="create"
                                           class="btn btn-primary"/>
                                    <a href="javascript:void(0);" class="btn cancel-add-task"
                                       data-group="emptyGroup">{{ __('buttons.cancel') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>


                </div>

            @endif

            @foreach ($tickets as $groupKey => $ticketGroup)

                @php
                    //Get first duedate if exist
                    $firstDueDate = null;
                    foreach($ticketGroup['tickets'] as $ticket) {
                        if($ticket['dateToFinish'] != '0000-00-00' && $ticket['dateToFinish'] != '1969-12-31 00:00:00') {
                            if($firstDueDate == null || $ticket['dateToFinish'] < $firstDueDate) {
                                $firstDueDate = $ticket['dateToFinish'];
                            }
                        }
                    }
                @endphp

                <x-global::accordion id="ticketBox1-{{ $loop->index }}">
                    <x-slot name="title">
                        {!!  __($ticketGroup["labelName"]) !!} ({{ count($ticketGroup["tickets"]) }})
                    </x-slot>
                    <x-slot name="actionlink">
                        <a href="javascript:void(0);" class="add-task-button btn btn-link" style="padding:0px; padding-left:1px; width:31px; line-height:31px; height:31px; font-weight:bold; text-align: center; font-size:var(--font-size-l);" data-group="{{ $groupKey }}">
                            <i class="fa-solid fa-circle-plus"></i></a>
                    </x-slot>
                    <x-slot name="content">
                        <!-- Quick Add Form for this group -->
                        <div class="quickAddForm" id="quickAddForm-{{ $groupKey }}"
                             style="display:none; margin-bottom:15px; padding-bottom:5px; padding-left:5px;">
                            <form method="post"
                                  hx-post="{{ BASE_URL }}/widgets/myToDos/addTodo"
                                  hx-target="#yourToDoContainer"
                                  hx-swap="outerHTML"
                                  hx-indicator=".htmx-indicator">
                                <div class="tw-flex tw-flex-row tw-gap-2">
                                    <div class="tw-flex-grow">
                                        <input type="text" name="headline" class="main-title-input"
                                               style="font-size:var(--base-font-size)"
                                               placeholder="{{ __('input.placeholders.what_are_you_working_on') }}"/>
                                        <input type="hidden" name="quickadd" value="true"/>
                                    </div>
                                    <div>
                                        <select name="projectId">
                                            @foreach($allAssignedprojects as $project)
                                                <option value="{{ $project['id']  }}"

                                                    {{ (($groupBy === "project" && $project['id'] == $groupKey) || ($groupBy !== "project" && session('currentProject') == $groupKey)) ? 'selected' : '' }}
                                                >{{ $project["name"]  }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <input type="hidden" name="milestone" value=""/>
                                        <input type="hidden" name="status" value="3"/>
                                        <input type="hidden" name="priority"
                                               value="{{ $groupBy === "priority" ? $groupKey : '' }}"/>
                                        <input type="hidden" name="dateToFinish"
                                               value="{{ $groupKey === 'thisWeek' ? date('Y-m-d', strtotime('next friday')) : '' }}"/>
                                        <textarea name="description" class="description-input" style="display:none;"
                                                  placeholder="{{ __('input.placeholders.description') }}"></textarea>
                                    </div>
                                    <div>
                                        <input type="submit" value="{{ __('buttons.save') }}" name="create"
                                               class="btn btn-primary"/>
                                        <a href="javascript:void(0);" class="btn cancel-add-task"
                                           data-group="{{ $groupKey }}">{{ __('buttons.cancel') }}</a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="sortable-list" data-container-type="section" style="padding-left:5px;">
                            @foreach ($ticketGroup['tickets'] as $row)
                                @include('widgets::partials.todoItem', ['ticket' => $row, 'statusLabels' => $statusLabels, 'onTheClock' => $onTheClock, 'tpl' => $tpl, 'level' => 0])
                            @endforeach
                        </div>
                    </x-slot>

                </x-global::accordion>

            @endforeach

        </div>
    </div>

    @dispatchEvent('afterTodoListWidgetBox')


    <script type="text/javascript">

        @dispatchEvent('scripts.afterOpen')


        jQuery(document).ready(function () {

            console.debugging = true;
            console.debug = function () {
                if (!console.debugging) return;
                console.log.apply(this, arguments);
            };

            @if(session('userdata.id') != null)
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initStatusDropdown();
            leantime.ticketsController.initDueDateTimePickers();

            // Initialize the sortable lists for hierarchical tasks
            jQuery('.sortable-list').nestedSortable();
            @else
            leantime.authController.makeInputReadonly(".maincontentinner");
            @endif
        });

        htmx.onLoad(function () {
            jQuery('.sortable-list').nestedSortable();
        });


    </script>

    <script>

        // Quick Add Task functionality
        jQuery(document).ready(function () {
            initAddTaskBtns();
        });

        htmx.onLoad(function () {
            initAddTaskBtns();
        });

        function initAddTaskBtns() {
            // Show the quick add form when the + button is clicked
            jQuery('.add-task-button').on('click', function () {
                var groupKey = jQuery(this).data('group');
                jQuery('#quickAddForm-' + groupKey).slideDown();
                jQuery('#quickAddForm-' + groupKey + ' .main-title-input').focus();
            });

            // Hide the quick add form when cancel is clicked
            jQuery('.cancel-add-task').on('click', function () {
                var groupKey = jQuery(this).data('group');
                jQuery('#quickAddForm-' + groupKey).slideUp();
                jQuery('#quickAddForm-' + groupKey + ' .main-title-input').val('');
                jQuery('#quickAddForm-' + groupKey + ' .description-input').val('');
            });

            jQuery('.ticket-title').each(function(){

                let currentTitle = jQuery(this);
                jQuery(this).hover(function () {
                    jQuery(this).find(".edit-button").show();
                },
                    function(){
                        jQuery(this).find(".edit-button").hide();

                });

                jQuery(this).find(".edit-button").click(function() {
                    currentTitle.find(".edit-button").hide();
                    currentTitle.find('.title-text').hide();
                    currentTitle.find('.edit-form').show();
                });

                jQuery(this).find(".edit-form .cancel-edit-task").click(function() {
                    currentTitle.find('.title-text').show();
                    currentTitle.find('.edit-form').hide();
                });

            });

        }

    </script>


</div>

@extends($layout)

@section('content')

<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$sprints = $tpl->get('sprints');
$searchCriteria = $tpl->get('searchCriteria');
$currentSprint = $tpl->get('currentSprint');
$allTickets = $tpl->get('allTickets');
$flatTickets = $tpl->get('flatTickets');

$todoTypeIcons = $tpl->get('ticketTypeIcons');

$efforts = $tpl->get('efforts');
$priorities = $tpl->get('priorities');
$statusLabels = $tpl->get('allTicketStates');
$users = $tpl->get('users');
$milestones = $tpl->get('milestones');

$newField = $tpl->get('newField');
$onTheClock = $tpl->get('onTheClock');

$groupBy = $searchCriteria['groupBy'] ?? '';
?>

{!! $tpl->displayNotification() !!}

<?php $tpl->displaySubmodule('tickets-ticketHeader') ?>

<div class="maincontent">

    <?php $tpl->displaySubmodule('tickets-ticketBoardTabs') ?>

    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                <?php
                $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');
$tpl->displaySubmodule('tickets-ticketNewBtn');
$tpl->displaySubmodule('tickets-ticketFilter');
$tpl->dispatchTplEvent('filters.beforeLefthandSectionClose');
?>
            </div>

            <div class="col-md-4 center"></div>
            <div class="col-md-4">
                <div class="pull-right">
                    <?php $tpl->dispatchTplEvent('filters.afterRighthandSectionOpen'); ?>
                    <div id="tableButtons" style="display:inline-block"></div>
                    <?php $tpl->dispatchTplEvent('filters.beforeRighthandSectionClose'); ?>
                </div>
            </div>
        </div>

        <div class="clearfix" style="margin-bottom: 20px;"></div>

        <?php $tpl->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTickets]); ?>

        {{-- Single unified table for all tickets --}}
        {{-- Single unified table for all tickets --}}
        <table id="ticketGridTable" class="table table-bordered display ticketTable" style="width:100%">
            <thead>
                <tr>
                    <th class="no-sort drag-handle-col" style="width:30px;"></th>
                    <th class="id-col" style="width:50px;">{!! $tpl->__('label.id') !!}</th>
                    <th style="min-width: 200px;">{!! $tpl->__('label.title') !!}</th>
                    <th class="status-col" style="width:120px;">{!! $tpl->__('label.todo_status') !!}</th>
                    <th class="milestone-col" style="width:140px;">{!! $tpl->__('label.milestone') !!}</th>
                    <th class="effort-col" style="width:80px;">{!! $tpl->__('label.effort') !!}</th>
                    <th class="priority-col" style="width:100px;">{!! $tpl->__('label.priority') !!}</th>
                    <th class="user-col" style="width:140px;">{!! $tpl->__('label.editor') !!}</th>
                    <th class="sprint-col" style="width:120px;">{!! $tpl->__('label.sprint') !!}</th>
                    <th class="tags-col" style="width:100px;">{!! $tpl->__('label.tags') !!}</th>
                    <th class="duedate-col" style="width:110px;">{!! $tpl->__('label.due_date') !!}</th>
                    <th class="planned-hours-col" style="width:80px;">{!! $tpl->__('label.planned_hours') !!}</th>
                    <th class="remaining-hours-col" style="width:80px;">{!! $tpl->__('label.estimated_hours_remaining') !!}</th>
                    <th class="booked-hours-col" style="width:80px;">{!! $tpl->__('label.booked_hours') !!}</th>
                    <th class="no-sort" style="width:40px;"></th>
                    <th class="hidden-col">groupKey</th>
                    <th class="hidden-col">groupLabel</th>
                    <th class="hidden-col">sortIndex</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($flatTickets as $rowNum => $row)
                <tr class="ticketRow"
                    data-id="{{ $tpl->e($row['id']) }}"
                    data-type="{{ $tpl->e($row['type'] ?? 'task') }}"
                    data-milestone-id="{{ $tpl->e($row['milestoneid'] ?? '') }}"
                    data-sprint="{{ $tpl->e($row['sprint'] ?? '') }}"
                    data-parent-id="{{ $tpl->e($row['dependingTicketId'] ?? '') }}"
                    data-project-id="{{ $tpl->e($row['projectId'] ?? '') }}"
                    data-subtask-count="{{ $tpl->e($row['subtaskCount'] ?? 0) }}"
                    data-sort-index="{{ $tpl->e($row['sortindex'] ?? $rowNum) }}"
                >
                    {{-- Drag Handle --}}
                    <td class="drag-handle-cell no-sort">
                        <span class="drag-handle"><i class="fa fa-grip-vertical"></i></span>
                    </td>

                    {{-- ID --}}
                    <td data-order="{{ $tpl->e($row['id']) }}">
                        <a href="#/tickets/showTicket/{{ $tpl->e($row['id']) }}" class="ticketModal" preload="mouseover">#{{ $tpl->e($row['id']) }}</a>
                    </td>

                    {{-- Title (click-to-edit) --}}
                    <td data-order="{{ $tpl->e($row['headline']) }}" class="title-cell">
                        <div class="title-cell-inner">
                            @if ($row['subtaskCount'] > 0)
                                <span class="subtask-toggle" data-ticket-id="{{ $row['id'] }}" title="{{ $tpl->__('label.show_subtasks') }}">
                                    <i class="fa fa-angle-right"></i>
                                </span>
                            @endif
                            @if (!empty($row['dependingTicketId']) && $row['dependingTicketId'] > 0)
                                <small class="parent-link"><a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}" preload="mouseover">{{ $tpl->escape($row['parentHeadline'] ?? '') }}</a></small> //
                            @endif
                            <span class="title-text" data-ticket-id="{{ $row['id'] }}">{{ $tpl->e($row['headline']) }}</span>
                            <input type="text" class="title-edit-input" data-ticket-id="{{ $row['id'] }}" value="{{ $tpl->e($row['headline']) }}" style="display:none;" />
                        </div>
                    </td>

                    {{-- Status --}}
                    @php
                        $statusClass = $statusLabels[$row['status']]['class'] ?? 'label-important';
                        $statusName = $statusLabels[$row['status']]['name'] ?? 'new';
                    @endphp
                    <td data-order="{{ $statusName }}">
                        <div class="dropdown ticketDropdown statusDropdown colorized show">
                            <a class="dropdown-toggle status {{ $statusClass }} f-left"
                               href="javascript:void(0);" role="button"
                               id="statusDropdownMenuLink{{ $row['id'] }}"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text">{{ $statusName }}</span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                                <li class="nav-header border">{!! $tpl->__('dropdown.choose_status') !!}</li>
                                @foreach ($statusLabels as $key => $label)
                                    <li class="dropdown-item">
                                        <a href="javascript:void(0);"
                                           class="{{ $label['class'] }}"
                                           data-label="{{ $tpl->escape($label['name']) }}"
                                           data-value="{{ $row['id'] }}_{{ $key }}_{{ $label['class'] }}"
                                           id="ticketStatusChange{{ $row['id'] }}{{ $key }}">{{ $tpl->escape($label['name']) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </td>

                    {{-- Milestone --}}
                    @php
                        $milestoneHeadline = ($row['milestoneid'] != '' && $row['milestoneid'] != 0)
                            ? $tpl->escape($row['milestoneHeadline'])
                            : $tpl->__('label.no_milestone');
                    @endphp
                    <td data-order="{{ $milestoneHeadline }}">
                        <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                            <a style="background-color:{{ $tpl->escape($row['milestoneColor']) }}"
                               class="dropdown-toggle label-default milestone f-left"
                               href="javascript:void(0);" role="button"
                               id="milestoneDropdownMenuLink{{ $row['id'] }}"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text">{{ $milestoneHeadline }}</span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="milestoneDropdownMenuLink{{ $row['id'] }}">
                                <li class="nav-header border">{!! $tpl->__('dropdown.choose_milestone') !!}</li>
                                <li class="dropdown-item">
                                    <a style="background-color:#b0b0b0" href="javascript:void(0);"
                                       data-label="{{ $tpl->__('label.no_milestone') }}"
                                       data-value="{{ $row['id'] }}_0_#b0b0b0">{{ $tpl->__('label.no_milestone') }}</a>
                                </li>
                                @foreach ($milestones as $milestone)
                                    <li class="dropdown-item">
                                        <a href="javascript:void(0);"
                                           data-label="{{ $tpl->escape($milestone->headline) }}"
                                           data-value="{{ $row['id'] }}_{{ $milestone->id }}_{{ $tpl->escape($milestone->tags) }}"
                                           id="ticketMilestoneChange{{ $row['id'] }}{{ $milestone->id }}"
                                           style="background-color:{{ $tpl->escape($milestone->tags) }}">{{ $tpl->escape($milestone->headline) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </td>

                    {{-- Effort --}}
                    @php
                        $effortLabel = ($row['storypoints'] != '' && $row['storypoints'] > 0)
                            ? ($efforts['' . $row['storypoints']] ?? $row['storypoints'])
                            : $tpl->__('label.story_points_unkown');
                    @endphp
                    <td data-order="{{ $effortLabel }}">
                        <div class="dropdown ticketDropdown effortDropdown show">
                            <a class="dropdown-toggle label-default effort f-left"
                               href="javascript:void(0);" role="button"
                               id="effortDropdownMenuLink{{ $row['id'] }}"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text">{{ $effortLabel }}</span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink{{ $row['id'] }}">
                                <li class="nav-header border">{!! $tpl->__('dropdown.how_big_todo') !!}</li>
                                @foreach ($efforts as $effortKey => $effortValue)
                                    <li class="dropdown-item">
                                        <a href="javascript:void(0);"
                                           data-value="{{ $row['id'] }}_{{ $effortKey }}"
                                           id="ticketEffortChange{{ $row['id'] }}{{ $effortKey }}">{{ $effortValue }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </td>

                    {{-- Priority --}}
                    @php
                        $priorityLabel = ($row['priority'] != '' && $row['priority'] > 0)
                            ? ($priorities[$row['priority']] ?? $tpl->__('label.priority_unkown'))
                            : $tpl->__('label.priority_unkown');
                    @endphp
                    <td data-order="{{ $priorityLabel }}">
                        <div class="dropdown ticketDropdown priorityDropdown show">
                            <a class="dropdown-toggle label-default priority priority-bg-{{ $row['priority'] }} f-left"
                               href="javascript:void(0);" role="button"
                               id="priorityDropdownMenuLink{{ $row['id'] }}"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text">{{ $priorityLabel }}</span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="priorityDropdownMenuLink{{ $row['id'] }}">
                                <li class="nav-header border">{!! $tpl->__('dropdown.select_priority') !!}</li>
                                @foreach ($priorities as $priorityKey => $priorityValue)
                                    <li class="dropdown-item">
                                        <a href="javascript:void(0);"
                                           class="priority-bg-{{ $priorityKey }}"
                                           data-value="{{ $row['id'] }}_{{ $priorityKey }}"
                                           id="ticketPriorityChange{{ $row['id'] }}{{ $priorityKey }}">{{ $priorityValue }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </td>

                    {{-- Editor / Assigned User --}}
                    <td data-order="{{ $row['editorFirstname'] != '' ? $tpl->escape($row['editorFirstname']) : $tpl->__('dropdown.not_assigned') }}">
                        <div class="dropdown ticketDropdown userDropdown noBg show f-left">
                            <a class="dropdown-toggle" href="javascript:void(0);" role="button"
                               id="userDropdownMenuLink{{ $row['id'] }}"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text">
                                    @if ($row['editorFirstname'] != '')
                                        <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $row['editorId'] }}" width="25" style="vertical-align: middle; margin-right:5px;" /></span>
                                        <span id="user{{ $row['id'] }}">{{ $tpl->escape($row['editorFirstname']) }}</span>
                                    @else
                                        <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage=false" width="25" style="vertical-align: middle; margin-right:5px;" /></span>
                                        <span id="user{{ $row['id'] }}">{!! $tpl->__('dropdown.not_assigned') !!}</span>
                                    @endif
                                </span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                <li class="nav-header border">{!! $tpl->__('dropdown.choose_user') !!}</li>
                                <li class="dropdown-item">
                                    <a href="javascript:void(0);"
                                       data-label="{!! $tpl->__('label.not_assigned_to_user') !!}"
                                       data-value="{{ $row['id'] }}_0_0"
                                       id="userStatusChange{{ $row['id'] }}0">{!! $tpl->__('label.not_assigned_to_user') !!}</a>
                                </li>
                                @foreach ($users as $user)
                                    <li class="dropdown-item">
                                        <a href="javascript:void(0);"
                                           data-label="{{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}"
                                           data-value="{{ $row['id'] }}_{{ $user['id'] }}_{{ $user['profileId'] }}"
                                           id="userStatusChange{{ $row['id'] }}{{ $user['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}" width="25" style="vertical-align: middle; margin-right:5px;" />{{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </td>

                    {{-- Sprint --}}
                    @php
                        $sprintHeadline = ($row['sprint'] != '' && $row['sprint'] != 0 && $row['sprint'] != -1)
                            ? $tpl->escape($row['sprintName'])
                            : $tpl->__('label.not_assigned_to_sprint');
                    @endphp
                    <td data-order="{{ $sprintHeadline }}">
                        <div class="dropdown ticketDropdown sprintDropdown show">
                            <a class="dropdown-toggle label-default sprint f-left"
                               href="javascript:void(0);" role="button"
                               id="sprintDropdownMenuLink{{ $row['id'] }}"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text">{{ $sprintHeadline }}</span>
                                <i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="sprintDropdownMenuLink{{ $row['id'] }}">
                                <li class="nav-header border">{!! $tpl->__('dropdown.choose_sprint') !!}</li>
                                <li class="dropdown-item">
                                    <a href="javascript:void(0);"
                                       data-label="{{ $tpl->__('label.not_assigned_to_sprint') }}"
                                       data-value="{{ $row['id'] }}_0">{{ $tpl->__('label.not_assigned_to_sprint') }}</a>
                                </li>
                                @if ($sprints)
                                    @foreach ($sprints as $sprint)
                                        <li class="dropdown-item">
                                            <a href="javascript:void(0);"
                                               data-label="{{ $tpl->escape($sprint->name) }}"
                                               data-value="{{ $row['id'] }}_{{ $sprint->id }}"
                                               id="ticketSprintChange{{ $row['id'] }}{{ $sprint->id }}">{{ $tpl->escape($sprint->name) }}</a>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </td>

                    {{-- Tags --}}
                    <td data-order="{{ $row['tags'] }}">
                        @if ($row['tags'] != '')
                            @php $tagsArray = explode(',', $row['tags']); @endphp
                            <div class="tagsinput readonly">
                                @foreach ($tagsArray as $tag)
                                    <span class="tag"><span>{{ $tpl->escape($tag) }}</span></span>
                                @endforeach
                            </div>
                        @endif
                    </td>

                    {{-- Due Date --}}
                    @php
                        if ($row['dateToFinish'] == '0000-00-00 00:00:00' || $row['dateToFinish'] == '1969-12-31 00:00:00') {
                            $date = $tpl->__('text.anytime');
                        } else {
                            $dateObj = new DateTime($row['dateToFinish']);
                            $date = $dateObj->format($tpl->__('language.dateformat'));
                        }
                    @endphp
                    <td data-order="{{ $row['dateToFinish'] }}">
                        <input type="text" title="{{ $tpl->__('label.due') }}"
                               value="{{ $date }}"
                               class="quickDueDates secretInput"
                               data-id="{{ $row['id'] }}" name="date" />
                    </td>

                    {{-- Planned Hours --}}
                    <td data-order="{{ $tpl->e($row['planHours']) }}">
                        <input type="text" value="{{ $tpl->e($row['planHours']) }}"
                               name="planHours" class="small-input secretInput"
                               onchange="leantime.ticketsController.updatePlannedHours(this, '{{ $row['id'] }}'); jQuery(this).parent().attr('data-order',jQuery(this).val());" />
                    </td>

                    {{-- Remaining Hours --}}
                    <td data-order="{{ $tpl->e($row['hourRemaining']) }}">
                        <input type="text" value="{{ $tpl->e($row['hourRemaining']) }}"
                               name="remainingHours" class="small-input secretInput"
                               onchange="leantime.ticketsController.updateRemainingHours(this, '{{ $row['id'] }}');" />
                    </td>

                    {{-- Booked Hours --}}
                    <td data-order="{{ ($row['bookedHours'] === null || $row['bookedHours'] == '') ? '0' : $row['bookedHours'] }}">
                        {{ ($row['bookedHours'] === null || $row['bookedHours'] == '') ? '0' : $row['bookedHours'] }}
                    </td>

                    {{-- Submenu --}}
                    <td>
                        @include("tickets::partials.ticketsubmenu", [
                            "ticket" => $row,
                            "onTheClock" => $onTheClock,
                            "allowSubtaskCreation" => true
                        ])
                    </td>

                    {{-- Hidden columns for RowGroup and sorting (hidden via DataTables columnDefs) --}}
                    <td>{{ $row['groupKey'] ?? 'all' }}</td>
                    <td>{{ $row['groupLabel'] ?? 'all' }}</td>
                    <td>{{ $row['sortindex'] ?? $rowNum }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Persistent quick-add bar below table --}}
        @if ($login::userIsAtLeast($roles::$editor))
        <div class="table-grid-quick-add">
            <div class="quick-add-bar">
                <i class="fa fa-plus-circle quick-add-icon"></i>
                <input type="text"
                       id="persistentQuickAdd"
                       class="quick-add-persistent-input"
                       placeholder="{{ $tpl->__('input.placeholders.what_are_you_working_on') ?? 'Add a new task...' }} (Enter to save)"
                       autocomplete="off" />
            </div>
            <div class="table-grid-actions">
                <a href="javascript:void(0);" class="btn btn-link btn-sm" id="addMilestoneInline">
                    <i class="fa fa-flag"></i> {{ $tpl->__('links.add_milestone') ?? 'Add Milestone' }}
                </a>
                <a href="javascript:void(0);" class="btn btn-link btn-sm" id="addSprintInline">
                    <i class="fa fa-bolt"></i> {{ $tpl->__('links.add_sprint') ?? 'Add Sprint' }}
                </a>
            </div>
        </div>
        @endif

        {{-- Inline milestone creation template (hidden, shown by JS) --}}
        <div id="inlineMilestoneTemplate" style="display:none;">
            <div class="inline-create-form milestone-create-form">
                <i class="fa fa-flag"></i>
                <input type="text" class="inline-create-input" placeholder="{{ $tpl->__('input.placeholders.enter_milestone_name') ?? 'Enter milestone name...' }}" />
                <input type="text" class="inline-create-date-from secretInput" placeholder="{{ $tpl->__('label.start') }}" style="width:90px;" />
                <input type="text" class="inline-create-date-to secretInput" placeholder="{{ $tpl->__('label.end') }}" style="width:90px;" />
                <button class="btn btn-primary btn-sm inline-create-save"><i class="fa fa-check"></i></button>
                <button class="btn btn-sm inline-create-cancel"><i class="fa fa-times"></i></button>
            </div>
        </div>

        {{-- Inline sprint creation template (hidden, shown by JS) --}}
        <div id="inlineSprintTemplate" style="display:none;">
            <div class="inline-create-form sprint-create-form">
                <i class="fa fa-bolt"></i>
                <input type="text" class="inline-create-input" placeholder="{{ $tpl->__('input.placeholders.enter_sprint_name') ?? 'Enter sprint name...' }}" />
                <input type="text" class="inline-create-date-from secretInput" placeholder="{{ $tpl->__('label.start') }}" style="width:90px;" />
                <input type="text" class="inline-create-date-to secretInput" placeholder="{{ $tpl->__('label.end') }}" style="width:90px;" />
                <button class="btn btn-primary btn-sm inline-create-save"><i class="fa fa-check"></i></button>
                <button class="btn btn-sm inline-create-cancel"><i class="fa fa-times"></i></button>
            </div>
        </div>

        <?php $tpl->dispatchTplEvent('allTicketsTable.afterClose', ['tickets' => $allTickets]); ?>

    </div>
</div>

{{-- Pass data to JS --}}
@php
    $groupMetaJson = collect($allTickets)->map(function ($group, $key) {
        return [
            'key' => (string) $key,
            'label' => $group['label'] ?? (string) $key,
            'color' => $group['color'] ?? '',
            'moreInfo' => $group['more-info'] ?? '',
            'id' => $group['id'] ?? (string) $key,
            'count' => count($group['items']),
        ];
    })->values()->toArray();

    $milestonesJson = collect($milestones)->map(function ($m) {
        return ['id' => $m->id, 'headline' => $m->headline, 'color' => $m->tags];
    })->values()->toArray();

    $sprintsJson = $sprints
        ? collect($sprints)->map(function ($s) {
            return ['id' => $s->id, 'name' => $s->name];
        })->values()->toArray()
        : [];
@endphp
<script type="text/javascript">
    var leantimeTicketGridConfig = {
        groupBy: "{{ $tpl->e($groupBy) }}",
        baseUrl: "{{ BASE_URL }}",
        projectId: "{{ session('currentProject') }}",
        isEditor: {{ $login::userIsAtLeast($roles::$editor) ? 'true' : 'false' }},
        groupMeta: {!! json_encode($groupMetaJson) !!},
        milestones: {!! json_encode($milestonesJson) !!},
        sprints: {!! json_encode($sprintsJson) !!},
        i18n: {
            addTask: "{!! $tpl->__('links.add_task') !!}",
            noGroup: "{!! $tpl->__('label.no_milestone') !!}",
            saveSuccess: "{!! $tpl->__('notifications.ticket_saved') ?? 'Saved' !!}",
            saveError: "{!! $tpl->__('notifications.ticket_save_error') ?? 'Error saving' !!}",
            addMilestone: "{!! $tpl->__('links.add_milestone') ?? 'Add Milestone' !!}",
            addSprint: "{!! $tpl->__('links.add_sprint') ?? 'Add Sprint' !!}",
            noSubtasks: "{!! $tpl->__('text.no_subtasks') ?? 'No subtasks' !!}",
            addSubtask: "{!! $tpl->__('links.add_subtask') ?? 'Add Subtask' !!}",
        }
    };
</script>

<script type="text/javascript">
    jQuery(document).ready(function() {
        <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>

        @if ($login::userIsAtLeast($roles::$editor))
            leantime.ticketsController.initDueDateTimePickers();
            leantime.ticketsController.initUserDropdown();
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initPriorityDropdown();
            leantime.ticketsController.initSprintDropdown();
            leantime.ticketsController.initStatusDropdown();
        @else
            leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

        leantime.tableGridController.init(leantimeTicketGridConfig);

        <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>
    });
</script>

@endsection

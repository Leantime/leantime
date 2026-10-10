@extends($layout)

@section('content')

@php
    $allTicketGroups = $allTickets;
    $todoTypeIcons = $ticketTypeIcons;
    $statusLabels = $allTicketStates;
    $newField = $newField ?? [];
    $numberofColumns = count($allTicketStates) - 1;
    $size = floor(100 / $numberofColumns);
@endphp

{!! $tpl->displayNotification() !!}

@include('tickets::submodules.ticketHeader')

<div class="maincontent">

    @include('tickets::submodules.ticketBoardTabs')

    <div class="maincontentinner">

        {{-- Board actions (New / Filter / Group By) moved into the nav bar
             (ticketBoardTabs). Only the table-specific DataTables buttons remain
             here, right-aligned above the table. --}}
        <div class="row">
            <div class="col-md-12">
                <div class="pull-right">
                    @dispatchEvent('filters.afterRighthandSectionOpen')
                    <div id="tableButtons" style="display:inline-block"></div>
                    @dispatchEvent('filters.beforeRighthandSectionClose')
                </div>
            </div>

        </div>

        <div class="clearfix" style="margin-bottom: 20px;"></div>

        @if (isset($availableProjects))
            {{-- Program (cross-project) board: consistent inline quick-add with a required
                 project picker, matching the kanban/list add affordance. --}}
            <form action="" method="post" class="tw-mb-m" style="display:flex; gap:10px; align-items:flex-start; flex-wrap:wrap;">
                <input type="text" name="headline" placeholder="{{ __('input.placeholders.create_task') }}" style="flex:1 1 280px; min-width:240px;" />
                <x-global::forms.select name="quickaddProjectId" class="form-control" required style="width:auto;" aria-label="{{ __('label.project') }}">
                    <option value="">{{ __('label.project') }}…</option>
                    @foreach ($availableProjects as $quickAddProjectId => $quickAddProjectName)
                        <option value="{{ $quickAddProjectId }}">{{ $tpl->escape($quickAddProjectName) }}</option>
                    @endforeach
                </x-global::forms.select>
                <input type="hidden" name="sprint" value="{{ $currentSprint }}" />
                <input type="hidden" name="milestone" value="{{ htmlspecialchars((string) ($searchCriteria['milestone'] ?? ''), ENT_QUOTES, 'UTF-8') }}" />
                <input type="hidden" name="quickadd" value="1" />
                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="saveTicket" />
            </form>
        @endif

        @if (isset($allTicketGroups['all']))
            @php $allTickets = $allTicketGroups['all']['items']; @endphp
        @endif

        @foreach ($allTicketGroups as $group)
            @if ($group['label'] != 'all')
                <h5 class="accordionTitle {{ $group['class'] }}" @if (!empty($group['color'])) style="color:{{ htmlspecialchars($group['color']) }}" @endif id="accordion_link_{{ $group['id'] }}">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_{{ $group['id'] }}" onclick="leantime.snippets.accordionToggle('{{ $group['id'] }}');">
                        <i class="fa fa-angle-down"></i>{!! $group['label'] !!}({{ count($group['items']) }})
                    </a><br />
                    <small style="padding-left:20px; color:var(--primary-font-color); font-size:var(--font-size-s);">{{ $group['more-info'] }}</small>
                </h5>

                <div class="simpleAccordionContainer" id="accordion_content-{{ $group['id'] }}">
            @endif

                @php $allTickets = $group['items']; @endphp

                @dispatchEvent('allTicketsTable.before', ['tickets' => $allTicketGroups])
                <table class="table table-bordered display ticketTable " style="width:100%">
                <colgroup>
                    <col class="con1">
                    <col class="con0" style="max-width:200px;">
                    <col class="con1">
                    <col class="con0">
                    <col class="con1">
                    <col class="con0">
                    <col class="con1">
                    <col class="con0">
                    <col class="con1">
                    <col class="con0">
                    <col class="con1">
                    <col class="con0">
                    <col class="con1">
                    <col class="con0">
                    <col class="con1">
                </colgroup>
                @dispatchEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets])
                <thead>
                    @dispatchEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets])
                    <tr>
                        <th class="id-col">{!! __('label.id') !!}</th>
                        <th style="max-width: 350px;">{!! __('label.title') !!}</th>
                        <th class="status-col">{!! __('label.todo_status') !!}</th>
                        <th class="milestone-col">{!! __('label.milestone') !!}</th>
                        <th class="effort-col">{!! __('label.effort') !!}</th>
                        <th class="priority-col">{!! __('label.priority') !!}</th>
                        <th class="user-col">{!! __('label.editor') !!}.</th>
                        <th class="sprint-col">{!! __('label.sprint') !!}</th>
                        <th class="tags-col">{!! __('label.tags') !!}</th>
                        <th class="duedate-col">{!! __('label.due_date') !!}</th>
                        <th class="planned-hours-col">{!! __('label.planned_hours') !!}</th>
                        <th class="remaining-hours-col">{!! __('label.estimated_hours_remaining') !!}</th>
                        <th class="booked-hours-col">{!! __('label.booked_hours') !!}</th>
                        <th class="no-sort"></th>
                        {{-- Hidden in the table, included in the CSV export as plain text (#786). --}}
                        <th class="description-col noVis">{!! __('label.description') !!}</th>
                    </tr>
                    @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
                </thead>
                @dispatchEvent('allTicketsTable.afterHead', ['tickets' => $allTickets])
                <tbody>
                    @dispatchEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets])
                    @foreach ($allTickets as $rowNum => $row)
                        <tr style="height:1px;">
                            @dispatchEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets])
                            <td data-order="{{ $row['id'] }}">
                                #{{ $row['id'] }}
                            </td>

                        <td data-order="{{ $row['headline'] }}">
                            @if ($row['dependingTicketId'] > 0)
                                <small><a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}" preload="mouseover">{{ $row['parentHeadline'] }}</a></small> //<br />
                            @endif
                            <a class='ticketModal' href="#/tickets/showTicket/{{ $row['id'] }}" preload="mouseover">{{ $row['headline'] }}</a></td>

                            @php
                            // On a program (cross-project) board each row must render and edit
                            // with statuses from ITS OWN project, never a shared set, so a status
                            // change always writes a key valid in that project.
                            $rowStatusLabels = (isset($statusLabelsByProject) && isset($statusLabelsByProject[$row['projectId']]))
                                ? $statusLabelsByProject[$row['projectId']]
                                : $statusLabels;

                            if (isset($rowStatusLabels[$row['status']])) {
                                $class = $rowStatusLabels[$row['status']]['class'];
                                $name = $rowStatusLabels[$row['status']]['name'];
                                $sortKey = $rowStatusLabels[$row['status']]['sortKey'];
                            } else {
                                $class = 'label-important';
                                $name = 'new';
                                $sortKey = 0;
                            }
                            @endphp
                            <td data-order="{{ $name }}">
                                <x-tickets::chip-status :ticket-id="$row['id']" :status="$row['status']" :labels="$rowStatusLabels" />
                            </td>

                            @php
                            if ($row['milestoneid'] != '' && $row['milestoneid'] != 0) {
                                $milestoneHeadline = $tpl->escape($row['milestoneHeadline']);
                            } else {
                                $milestoneHeadline = __('label.no_milestone');
                            }
                            @endphp

                            <td data-order="{{ $milestoneHeadline }}">
                                <x-tickets::chip-milestone :ticket-id="$row['id']" :milestone-id="$row['milestoneid']" :headline="$row['milestoneHeadline'] ?? ''" :color="$row['milestoneColor'] ?? ''" :milestones="$milestones" />
                            </td>
                            {{-- Sort by effort SIZE, not its label (L/M/S/XL sorted alphabetically); unknown last. --}}
                            <td data-order="{{ $row['storypoints'] ? (float) $row['storypoints'] : 999 }}" data-export="{{ $row['storypoints'] ? $efforts[''.$row['storypoints'].''] ?? '?' : __('label.story_points_unkown') }}">
                                <x-tickets::chip-effort :ticket-id="$row['id']" :storypoints="$row['storypoints']" :efforts="$efforts" />
                            </td>

                            {{-- Sort by the priority NUMBER (1 = Critical), not its label, which sorted alphabetically (#1715); unknown last. The label is the CSV value. --}}
                            <td data-order="{{ ($row['priority'] != '' && $row['priority'] > 0) ? (int) $row['priority'] : 99 }}" data-export="@php if ($row['priority'] != '' && $row['priority'] > 0) { echo $priorities[$row['priority']] ?? __('label.priority_unkown'); } else { echo __('label.priority_unkown'); } @endphp">
                                <x-tickets::chip-priority :ticket-id="$row['id']" :priority="$row['priority']" :priorities="$priorities" />
                            </td>
                            <td data-order="{{ $row['editorFirstname'] != '' ? $tpl->escape($row['editorFirstname']) : __('dropdown.not_assigned') }}">
                                <x-tickets::chip-user class="f-left" :ticket-id="$row['id']" :editor-id="$row['editorId']" :editor-name="$row['editorFirstname'] ?? ''" :users="$users" :collaborators="$row['collaboratorPreview'] ?? []" :collaborator-overflow="$row['collaboratorOverflow'] ?? 0" />
                            </td>
                            @php
                            if ($row['sprint'] != '' && $row['sprint'] != 0 && $row['sprint'] != -1) {
                                $sprintHeadline = $tpl->escape($row['sprintName']);
                            } else {
                                $sprintHeadline = __('label.not_assigned_to_sprint');
                            }
                            @endphp

                            <td  data-order="{{ $sprintHeadline }}">

                                <x-tickets::chip-sprint :ticket-id="$row['id']" :sprint-id="$row['sprint']" :sprint-name="$row['sprintName'] ?? ''" :sprints="$sprints" />
                            </td>

                            <td data-order="{{ $row['tags'] }}">
                                @if ($row['tags'] != '')
                                    @php $tagsArray = explode(',', $row['tags']); @endphp
                                    <div class='tagsinput readonly'>
                                        @foreach ($tagsArray as $tag)
                                            <span class='tag'><span>{{ $tag }}</span></span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            @php
                            if ($row['dateToFinish'] == '0000-00-00 00:00:00' || $row['dateToFinish'] == '1969-12-31 00:00:00' || empty($row['dateToFinish'])) {
                                $date = __('text.anytime');
                            } else {
                                $date = format($row['dateToFinish'])->date(__('text.anytime'));
                            }
                            @endphp
                            {{-- Sort on the raw UTC value; export the date the user sees (not "0000-00-00 00:00:00"). --}}
                            <td data-order="{{ $row['dateToFinish'] }}" data-export="{{ $date }}">
                                <input type="text" title="{{ __('label.due') }}" value="{{ $date }}" class="quickDueDates secretInput" data-id="{{ $row['id'] }}" name="date" />
                            </td>
                            <td data-order="{{ $row['planHours'] }}">
                                <input type="text" value="{{ $row['planHours'] }}" name="planHours" class="small-input secretInput" onchange="leantime.ticketsController.updatePlannedHours(this, '{{ $row['id'] }}'); jQuery(this).parent().attr('data-order',jQuery(this).val());" />
                            </td>
                            <td data-order="{{ $row['hourRemaining'] }}">
                                <input type="text" value="{{ $row['hourRemaining'] }}" name="remainingHours" class="small-input secretInput" onchange="leantime.ticketsController.updateRemainingHours(this, '{{ $row['id'] }}');" />
                            </td>

                            <td data-order="{{ ($row['bookedHours'] === null || $row['bookedHours'] == '') ? '0' : $row['bookedHours'] }}">
                                {{ ($row['bookedHours'] === null || $row['bookedHours'] == '') ? '0' : $row['bookedHours'] }}
                            </td>
                            <td>
                                @include('tickets::partials.ticketsubmenu', ['ticket' => $row, 'onTheClock' => $onTheClock])
                            </td>
                            @php $descriptionText = \Leantime\Core\Support\Format::plainText($row['description'] ?? ''); @endphp
                            <td data-export="{{ \Leantime\Core\Support\Format::spreadsheetSafe($descriptionText) }}">{{ $descriptionText }}</td>
                            @dispatchEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum])
                        </tr>
                    @endforeach
                    @dispatchEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets])
                </tbody>
                @dispatchEvent('allTicketsTable.afterBody', ['tickets' => $allTickets])
                    <tfoot align="right">
                        <tr><td colspan="9"></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    </tfoot>

                </table>
                @dispatchEvent('allTicketsTable.afterClose', ['tickets' => $allTickets])

            @if ($group['label'] != 'all')
                </div>
            @endif
        @endforeach




    </div>
</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function() {
        @dispatchEvent('scripts.afterOpen')


        @if ($login::userIsAtLeast($roles::$editor))
            leantime.ticketsController.initDueDateTimePickers();

        @else
        leantime.authController.makeInputReadonly(".maincontentinner");
        @endif



        leantime.ticketsController.initTicketsTable("{{ $searchCriteria['groupBy'] }}");

        @dispatchEvent('scripts.beforeClose')

    });

</script>
@endpush @endonce

@endsection

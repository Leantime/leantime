@extends($layout)

@section('content')

@php
    $allTicketGroups = $allTickets;
    $todoTypeIcons = $ticketTypeIcons;
    $statusLabels = $allTicketStates;
    $numberofColumns = count($allTicketStates) - 1;
    $size = floor(100 / $numberofColumns);
@endphp

@include('tickets::submodules.timelineHeader')

<div class="maincontent">

    @include('tickets::submodules.timelineTabs')

    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        {{-- New / Filter moved into the nav bar (timelineTabs). Only the
             table's own export/columns buttons remain here. --}}
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

        @if (isset($allTicketGroups['all']))
            @php $allTickets = $allTicketGroups['all']['items']; @endphp
        @endif

        @foreach ($allTicketGroups as $group)
            @if ($group['label'] != 'all')
        <h5 class="accordionTitle {{ $group['class'] }}" @if (!empty($group['color'])) style="color:{{ htmlspecialchars($group['color']) }}" @endif id="accordion_link_{{ $group['id'] }}">
            <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_{{ $group['id'] }}" onclick="leantime.snippets.accordionToggle('{{ $group['id'] }}');">
                <i class="fa fa-angle-down"></i>{!! $group['label'] !!} ({{ count($group['items']) }})
            </a>
        </h5>
        <div class="simpleAccordionContainer" id="accordion_content-{{ $group['id'] }}">
            @endif

            @php $allTickets = $group['items']; @endphp

            @dispatchEvent('allTicketsTable.before', ['tickets' => $allTickets])
            <table class="table table-bordered display ticketTable " style="width:100%">
                <colgroup>
                    <col class="con1" >
                    <col class="con0">
                    <col class="con1">
                    <col class="con0" >
                    <col class="con1">
                    <col class="con0">
                    <col class="con1" >
                    <col class="con0" >
                    <col class="con1" >
                    <col class="con0" >
                    <col class="con1" >

                </colgroup>
                @dispatchEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets])
                <thead>
                @dispatchEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets])
                <tr>
                    <th>{!! __('label.title') !!}</th>
                    <th>{!! __('label.todo_type') !!}</th>
                    <th>{!! __('label.progress') !!}</th>
                    <th class="milestone-col">{!! __('label.dependent_on') !!}</th>
                    <th>{!! __('label.todo_status') !!}</th>
                    <th class="user-col">{!! __('label.owner') !!}</th>
                    <th>{!! __('label.planned_start_date') !!}</th>
                    <th>{!! __('label.planned_end_date') !!}</th>
                    <th>{!! __('label.planned_hours') !!}</th>
                    <th>{!! __('label.estimated_hours_remaining') !!}</th>
                    <th>{!! __('label.booked_hours') !!}</th>
                    <th class="no-sort"></th>
                </tr>
                @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
                </thead>
                @dispatchEvent('allTicketsTable.afterHead', ['tickets' => $allTickets])
                <tbody>
                    @dispatchEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets])
                    @foreach ($allTickets as $rowNum => $row)
                        <tr>
                            @dispatchEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets])
                            <td data-order="{{ $row['headline'] }}">
                                @if ($row['type'] == 'milestone')
                                    <a href="#/tickets/editMilestone/{{ $row['id'] }}">{{ $row['headline'] }}</a>
                                @else
                                    <a href="#/tickets/showTicket/{{ $row['id'] }}">{{ $row['headline'] }}</a>
                                @endif
                            </td>
                            <td>{{ __('label.'.strtolower($row['type'])) }}</td>

                            <td>
                                @if ($row['type'] == 'milestone')
                                    <div hx-trigger="load"
                                         hx-get="{{ BASE_URL }}/hx/tickets/milestones/progress?milestoneId={{ $row['id'] }}&view=Progress">
                                        <div class="htmx-indicator">
                                            {!! __('label.calculating_progress') !!}
                                        </div>
                                    </div>
                                @endif
                            </td>

                                @php
                            if ($row['milestoneid'] != '' && $row['milestoneid'] != 0) {
                                $milestoneHeadline = $tpl->escape($row['milestoneHeadline']);
                            } else {
                                $milestoneHeadline = __('label.no_milestone');
                            }
                                @endphp

                            <td data-order="{{ $milestoneHeadline }}">
                                <x-tickets::chip-milestone :float="false" :ticket-id="$row['id']" :milestone-id="$row['milestoneid']" :headline="$row['milestoneHeadline'] ?? ''" :color="$row['milestoneColor'] ?? ''" :milestones="$milestones" />
                            </td>
                            @php
                            if (isset($statusLabels[$row['status']])) {
                                $class = $statusLabels[$row['status']]['class'];
                                $name = $statusLabels[$row['status']]['name'];
                                $sortKey = $statusLabels[$row['status']]['sortKey'];
                            } else {
                                $class = 'label-important';
                                $name = 'new';
                                $sortKey = 0;
                            }
                            @endphp
                            <td data-order="{{ $sortKey }}">
                                <x-tickets::chip-status :float="false" :ticket-id="$row['id']" :status="$row['status']" :labels="$statusLabels" />
                            </td>

                            <td data-order="{{ $row['editorFirstname'] != '' ? $tpl->escape($row['editorFirstname']) : __('dropdown.not_assigned') }}">
                                <x-tickets::chip-user :ticket-id="$row['id']" :editor-id="$row['editorId']" :editor-name="$row['editorFirstname'] ?? ''" :users="$users" />
                            </td>

                            <td data-order="{{ $row['editFrom'] }}" >
                                {!! __('label.due_icon') !!}<input type="text" title="{{ __('label.planned_start_date') }}" value="{{ format($row['editFrom'])->date() }}" class="editFromDate secretInput milestoneEditFromAsync fromDateTicket-{{ $row['id'] }}" data-id="{{ $row['id'] }}" name="editFrom" class=""/>
                            </td>

                            <td data-order="{{ $row['editTo'] }}" >
                                {!! __('label.due_icon') !!}<input type="text" title="{{ __('label.planned_end_date') }}" value="{{ format($row['editTo'])->date() }}" class="editToDate secretInput milestoneEditToAsync toDateTicket-{{ $row['id'] }}" data-id="{{ $row['id'] }}" name="editTo" class="" />
                            </td>

                            <td data-order="{{ $row['planHours'] }}" >
                                {{ $row['planHours'] }}
                            </td>
                            <td data-order="{{ $row['hourRemaining'] }}" >
                                {{ $row['hourRemaining'] }}
                            </td>
                            <td data-order="{{ $row['bookedHours'] }}" >
                                {{ $row['bookedHours'] }}
                            </td>

                            <td>
                                @if ($login::userIsAtLeast($roles::$editor))
                                    <div class="inlineDropDownContainer">
                                        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li class="nav-header">{!! __('subtitles.todo') !!}</li>
                                            <li><a href="{{ BASE_URL }}/tickets/editMilestone/{{ $row['id'] }}" class='ticketModal'><i class="fa fa-edit"></i> {!! __('links.edit_milestone') !!}</a></li>
                                            <li><a href="{{ BASE_URL }}/tickets/moveTicket/{{ $row['id'] }}" class="moveTicketModal sprintModal"><i class="fa-solid fa-arrow-right-arrow-left"></i> {!! __('links.move_milestone') !!}</a></li>
                                            <li><a href="{{ BASE_URL }}/tickets/delMilestone/{{ $row['id'] }}" class="delete"><i class="fa fa-trash"></i> {!! __('links.delete') !!}</a></li>
                                            <li class="nav-header border"></li>
                                            <li><a href="{{ BASE_URL }}/tickets/showAll?search=true&milestone={{ $row['id'] }}">{!! __('links.view_todos') !!}</a></li>
                                        </ul>
                                    </div>
                                @endif
                            </td>
                            @dispatchEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum])
                        </tr>
                    @endforeach
                    @dispatchEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets])
                </tbody>
                @dispatchEvent('allTicketsTable.afterBody', ['tickets' => $allTickets])
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

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function(){

        @if ($login::userIsAtLeast($roles::$editor))
        leantime.ticketsController.initMilestoneDatesAsyncUpdate();

        @else
            leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

        leantime.ticketsController.initMilestoneTable("{{ $searchCriteria['groupBy'] }}");

        @dispatchEvent('scripts.beforeClose')
    });
</script>
@endpush @endonce

@endsection

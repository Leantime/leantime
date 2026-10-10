@extends($layout)

@section('content')

@php
    $todoTypeIcons = $ticketTypeIcons;
    $statusLabels = $allTicketStates;
    $numberofColumns = count($allTicketStates) - 1;
    $size = floor(100 / $numberofColumns);
@endphp

    @include('tickets::submodules.portfolioHeader')

    <div class="maincontent">
        {{-- New / Filter sit on the right of the view bar like on every board (ticketFilter brings its
             own #ticketSearch form, so it must not sit inside the one below). --}}
        <x-tickets::portfolio-tabs>
            <x-slot:actions>
                @dispatchEvent('filters.afterLefthandSectionOpen')
                @include('tickets::submodules.ticketNewBtn')
                @include('tickets::submodules.ticketFilter')
                @dispatchEvent('filters.beforeLefthandSectionClose')
            </x-slot:actions>
        </x-tickets::portfolio-tabs>

        <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <form action="" method="get" id="ticketTableHooks">

            @dispatchEvent('filters.afterFormOpen')

            <input type="hidden" value="1" name="search"/>
            <div class="row">
                <div class="col-md-7 center">
                    @dispatchEvent('filters.afterCenterSectionOpen')
                    @dispatchEvent('filters.beforeCenterSectionClose')
                </div>
                <div class="col-md-5">
                    <div class="pull-right">
                        @dispatchEvent('filters.afterRighthandSectionOpen')
                        <div id="tableButtons" style="display:inline-block"></div>
                        @dispatchEvent('filters.beforeRighthandSectionClose')
                    </div>
                </div>

            </div>

            @dispatchEvent('filters.beforeFormClose')

            <div class="clearfix"></div>

        </form>

        @dispatchEvent('allTicketsTable.before', ['tickets' => $allTickets])

            <table id="allTicketsTable" class="table table-bordered display ticketTable" style="width:100%">
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
                    <col class="con0" >
                </colgroup>
                @dispatchEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets])
                <thead>
                @dispatchEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets])
                <tr>
                    <th>{!! __('label.project_name') !!}</th>
                    <th>{!! __('label.title') !!}</th>
                    <th class="milestone-col">{!! __('label.dependent_on') !!}</th>
                    <th>{!! __('label.todo_status') !!}</th>
                    <th class="user-col">{!! __('label.owner') !!}</th>
                    <th>{!! __('label.planned_start_date') !!}</th>
                    <th>{!! __('label.planned_end_date') !!}</th>
                    <th>{!! __('label.planned_hours') !!}</th>
                    <th>{!! __('label.estimated_hours_remaining') !!}</th>
                    <th>{!! __('label.booked_hours') !!}</th>
                    <th>{!! __('label.progress') !!}</th>
                    <th class="no-sort"></th>
                </tr>
                @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
                </thead>
                @dispatchEvent('allTicketsTable.afterHead', ['tickets' => $allTickets])
                <tbody>
                    @dispatchEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets])
                    @foreach ($allTickets as $rowNum => $row)
                        <tr>
                            <td><h4>{{ $row->projectName }} </h4></td>
                            @dispatchEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets])
                            <td data-order="{{ $row->headline }}"><a href="#/tickets/editMilestone/{{ $row->id }}">{{ $row->headline }}</a></td>
                            @php
                            if ($row->milestoneid != '' && $row->milestoneid != 0) {
                                $milestoneHeadline = $tpl->escape($row->milestoneHeadline);
                            } else {
                                $milestoneHeadline = __('label.no_milestone');
                            }
                            @endphp

                            <td class="dropdown-cell" data-order="{{ $milestoneHeadline }}">
                                <x-tickets::chip-milestone :float="false" :ticket-id="$row->id" :milestone-id="$row->milestoneid" :headline="$row->milestoneHeadline ?? ''" :color="$row->milestoneColor ?? ''" :milestones="$milestones" />
                            </td>

                            @php
                            if (isset($statusLabels[$row->status])) {
                                $class = $statusLabels[$row->status]['class'];
                                $name = $statusLabels[$row->status]['name'];
                            } else {
                                $class = 'label-important';
                                $name = 'new';
                            }
                            @endphp
                            <td class="dropdown-cell" data-order="{{ $name }}">
                                <x-tickets::chip-status :float="false" :ticket-id="$row->id" :status="$row->status" :labels="$statusLabels" />
                            </td>

                            <td class="dropdown-cell" data-order="{{ $row->editorFirstname != '' ? $tpl->escape($row->editorFirstname) : __('dropdown.not_assigned') }}">
                                <x-tickets::chip-user :ticket-id="$row->id" :editor-id="$row->editorId" :editor-name="$row->editorFirstname ?? ''" :users="$users" />
                            </td>

                            <td data-order="{{ $row->editFrom }}" >
                                {!! __('label.due_icon') !!}<input type="text" title="{{ __('label.planned_start_date') }}" value="{{ format($row->editFrom)->date() }}" class="editFromDate secretInput milestoneEditFromAsync fromDateTicket-{{ $row->id }}" data-id="{{ $row->id }}" name="editFrom" class=""/>
                            </td>

                            <td data-order="{{ $row->editTo }}" >
                                {!! __('label.due_icon') !!}<input type="text" title="{{ __('label.planned_end_date') }}" value="{{ format($row->editTo)->date() }}" class="editToDate secretInput milestoneEditToAsync toDateTicket-{{ $row->id }}" data-id="{{ $row->id }}" name="editTo" class="" />
                            </td>

                            <td data-order="{{ $row->planHours }}" >
                                {{ $row->planHours }}
                            </td>
                            <td data-order="{{ $row->hourRemaining }}" >
                                {{ $row->hourRemaining }}
                            </td>
                            <td data-order="{{ $row->bookedHours }}" >
                                {{ $row->bookedHours }}
                            </td>

                            <td data-order="{{ $row->percentDone }}">
                                <div class="progress " style="width: 100%;">
                                    <div class="progress-bar progress-bar-success " role="progressbar" aria-valuenow="{{ $row->percentDone }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $row->percentDone }}%">
                                        <span class="sr-only">{!! sprintf(__('text.percent_complete'), $row->percentDone) !!}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($login::userIsAtLeast($roles::$editor))
                                    <x-global::actions.dropdown>
                                        <li class="nav-header">{!! __('subtitles.todo') !!}</li>
                                        <li><a href="#/tickets/editMilestone/{{ $row->id }}" class='ticketModal'><i class="fa fa-edit"></i> {!! __('links.edit_milestone') !!}</a></li>
                                        <li><a href="#/tickets/moveTicket/{{ $row->id }}" class="moveTicketModal sprintModal"><i class="fa-solid fa-arrow-right-arrow-left"></i> {!! __('links.move_milestone') !!}</a></li>
                                        <li><a href="#/tickets/delMilestone/{{ $row->id }}" class="delete"><i class="fa fa-trash"></i> {!! __('links.delete') !!}</a></li>
                                        <li class="nav-header border"></li>
                                        <li><a href="{{ BASE_URL }}/tickets/showAll?search=true&milestone={{ $row->id }}">{!! __('links.view_todos') !!}</a></li>

                                    </x-global::actions.dropdown>
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

    </div>
</div>

@once @push('scripts')
<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function(){
    });

    @if ($login::userIsAtLeast($roles::$editor))
    leantime.ticketsController.initMilestoneDatesAsyncUpdate();

    @else
        leantime.authController.makeInputReadonly(".maincontentinner");
    @endif

    leantime.ticketsController.initMilestoneTable("{{ $searchCriteria['groupBy'] }}");

    @dispatchEvent('scripts.beforeClose')

</script>
@endpush @endonce

@endsection

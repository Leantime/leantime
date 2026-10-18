@extends($layout)

@section('content')

@php
    $allTicketGroups = $allTickets;
    $statusLabels = $allTicketStates;
    $groupBy = $groupBy ?? [];
    $newField = $newField ?? [];
    $numberofColumns = count($allTicketStates) - 1;
    $size = floor(100 / $numberofColumns);
@endphp

{!! $tpl->displayNotification() !!}

@include('tickets::submodules.ticketHeader')

<div class="maincontent">

    @include('tickets::submodules.ticketBoardTabs')

    <x-global::layout.content-box id="tickets-list" :leftWidth="340" stack="lg" mainClass="hidden-sm">
        <x-slot:left>
            @dispatchEvent('allTicketsTable.before', ['tickets' => $allTickets])
            {{-- The list sits on the sidebar itself (no card): quick-add form, then the groups. --}}
            <div class="ticketListSidebar">
                <form action="" method="post">
                    <x-global::forms.text-input width="full" name="headline" autofocus placeholder="{{ __('input.placeholders.create_task') }}" />
                    @if (isset($availableProjects))
                        {{-- Program (cross-project) board: a task must belong to one child project. --}}
                        <x-global::forms.select name="quickaddProjectId" class="form-control tw-mb-s" required aria-label="{{ __('label.project') }}">
                            <option value="">{{ __('label.project') }}…</option>
                            @foreach ($availableProjects as $quickAddProjectId => $quickAddProjectName)
                                <option value="{{ $quickAddProjectId }}">{{ $tpl->escape($quickAddProjectName) }}</option>
                            @endforeach
                        </x-global::forms.select>
                    @endif
                    <input type="hidden" name="sprint" value="{{ $currentSprint }}" />
                    <input type="hidden" name="milestone" value="{{ htmlspecialchars((string) ($searchCriteria['milestone'] ?? ''), ENT_QUOTES, 'UTF-8') }}" />
                    <input type="hidden" name="groupBy" value="{{ htmlspecialchars((string) ($searchCriteria['groupBy'] ?? ''), ENT_QUOTES, 'UTF-8') }}" />
                    <input type="hidden" name="quickadd" value="1"/>
                    <x-global::forms.button tag="input" inputType="submit" class="tw-mb-m" contentRole="primary" :labelText="__('buttons.save')" name="saveTicket" style="vertical-align: top; " />
                </form>


                @foreach ($allTicketGroups as $group)
                    <x-tickets::group-section :group="$group" :groupBy="$searchCriteria['groupBy'] ?? ''">

                    @php $allTickets = $group['items']; @endphp


                    <table class="table display listStyleTable" style="width:100%">

                        @dispatchEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets])
                        <thead>
                        @dispatchEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets])
                        <tr style="display:none;">

                            <th style="width:20px" class="status-col">{!! __('label.todo_status') !!}</th>
                            <th>{!! __('label.title') !!}</th>
                        </tr>

                        @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
                        </thead>

                        @dispatchEvent('allTicketsTable.afterHead', ['tickets' => $allTickets])
                        <tbody>
                        @dispatchEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets])
                        @foreach ($allTickets as $rowNum => $row)
                            <tr onclick="leantime.ticketsController.loadTicketToContainer('{{ $row['id'] }}', '#ticketContent')" id="row-{{ $row['id'] }}" class="ticketRows">
                                @dispatchEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets])
                                @php
                                // Program (cross-project) board: render/edit each row with its own
                                // project's statuses so a status change never orphans the task.
                                $rowStatusLabels = (isset($statusLabelsByProject) && isset($statusLabelsByProject[$row['projectId']]))
                                    ? $statusLabelsByProject[$row['projectId']]
                                    : $statusLabels;
                                @endphp
                                <td data-order="{{ isset($rowStatusLabels[$row['status']]) ? $rowStatusLabels[$row['status']]['sortKey'] : '' }}" data-search="{{ isset($rowStatusLabels[$row['status']]) ? $rowStatusLabels[$row['status']]['name'] : '' }}" class="roundStatusBtn" style="width:20px">
                                    <x-tickets::chip-status :float="false" :icon-only="true" :ticket-id="$row['id']" :status="$row['status']" :labels="$rowStatusLabels" />
                                </td>

                                <td data-search="{{ isset($rowStatusLabels[$row['status']]) ? $rowStatusLabels[$row['status']]['name'] : '' }}" data-order="{{ $row['headline'] }}" >
                                    <a href="javascript:void(0);"><strong>{{ $row['headline'] }}</strong></a></td>

                                @dispatchEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum])
                            </tr>
                        @endforeach
                        @dispatchEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets])
                        </tbody>
                        @dispatchEvent('allTicketsTable.afterBody', ['tickets' => $allTickets])
                    </table>

                    </x-tickets::group-section>
                @endforeach

            </div>
            @dispatchEvent('allTicketsTable.afterClose', ['tickets' => $allTickets])
        </x-slot:left>
        {{-- The main pane is hidden on small screens (mainClass), as before: there the list opens
             to-dos in the dialog, and the list fills the box (stack="lg"). --}}
        <div id="ticketContent">
            <div class="center">
                <div class='svgContainer'>
                    {!! file_get_contents(ROOT.'/dist/images/svg/undraw_design_data_khdb.svg') !!}
                </div>

                <h3>{!! __('headlines.pick_a_task') !!}</h3>
                {!! __('text.edit_tasks_in_here') !!}
            </div>
        </div>
    </x-global::layout.content-box>
</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function() {
        @dispatchEvent('scripts.afterOpen')


        @if ($login::userIsAtLeast($roles::$editor))
        @else
        leantime.authController.makeInputReadonly(".maincontentinner");
        @endif



        leantime.ticketsController.initTicketsList("{{ $searchCriteria['groupBy'] }}");

        @dispatchEvent('scripts.beforeClose')

    });

</script>
@endpush @endonce

@endsection

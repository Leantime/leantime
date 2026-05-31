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

    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                @dispatchEvent('filters.afterLefthandSectionOpen')
                @include('tickets::submodules.ticketNewBtn')
                @include('tickets::submodules.ticketFilter')
                @dispatchEvent('filters.beforeLefthandSectionClose')
            </div>

            <div class="col-md-4 center">
            </div>
            <div class="col-md-4">
            </div>
        </div>

        <div class="clearfix"></div>

        @dispatchEvent('allTicketsTable.before', ['tickets' => $allTickets])

        <div class="row">
            <div class="col-md-3">
                <div class="quickAddForm" style="margin-top:15px;">
                    <form action="" method="post">
                        <input type="text" name="headline" autofocus placeholder="{{ __('input.placeholders.create_task') }}" style="width: 100%;"/>
                        <input type="hidden" name="sprint" value="{{ $currentSprint }}" />
                        <input type="hidden" name="milestone" value="{{ htmlspecialchars((string) ($searchCriteria['milestone'] ?? ''), ENT_QUOTES, 'UTF-8') }}" />
                        <input type="hidden" name="groupBy" value="{{ htmlspecialchars((string) ($searchCriteria['groupBy'] ?? ''), ENT_QUOTES, 'UTF-8') }}" />
                        <input type="hidden" name="quickadd" value="1"/>
                        <input type="submit" class="btn btn-primary tw-mb-m" value="{{ __('buttons.save') }}" name="saveTicket" style="vertical-align: top; "/>
                    </form>


                    @foreach ($allTicketGroups as $group)
                        @if ($group['label'] != 'all')
                            <h5 class="accordionTitle {{ $group['class'] }}" @if (!empty($group['color'])) style="color:{{ htmlspecialchars($group['color']) }}" @endif id="accordion_link_{{ $group['id'] }}">
                                <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_{{ $group['id'] }}" onclick="leantime.snippets.accordionToggle('{{ $group['id'] }}');">
                                    <i class="fa fa-angle-down"></i>{{ $group['label'] }} ({{ count($group['items']) }})
                                </a>
                            </h5>
                            <div class="simpleAccordionContainer" id="accordion_content-{{ $group['id'] }}">
                        @endif

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
                                    <td data-order="{{ isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['sortKey'] : '' }}" data-search="{{ isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['name'] : '' }}" class="roundStatusBtn" style="width:20px">
                                        <div class="dropdown ticketDropdown statusDropdown colorized show">
                                            <a class="dropdown-toggle status {{ isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['class'] : '' }}" href="javascript:void(0);" role="button" id="statusDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-caret-down" aria-hidden="true"></i>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                                                <li class="nav-header border">{!! __('dropdown.choose_status') !!}</li>
                                                @php
                                                foreach ($statusLabels as $key => $label) {
                                                    echo "<li class='dropdown-item'>
                                            <a href='javascript:void(0);' class='".$label['class']."' data-label='".$tpl->escape($label['name'])."' data-value='".$row['id'].'_'.$key.'_'.$label['class']."' id='ticketStatusChange".$row['id'].$key."' >".$tpl->escape($label['name']).'</a>';
                                                    echo '</li>';
                                                }
                                                @endphp
                                            </ul>
                                        </div>
                                    </td>

                                    <td data-search="{{ isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['name'] : '' }}" data-order="{{ $row['headline'] }}" >
                                        <a href="javascript:void(0);"><strong>{{ $row['headline'] }}</strong></a></td>

                                    @dispatchEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum])
                                </tr>
                            @endforeach
                            @dispatchEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets])
                            </tbody>
                            @dispatchEvent('allTicketsTable.afterBody', ['tickets' => $allTickets])
                        </table>

                        @if ($group['label'] != 'all')
                            </div>
                        @endif
                    @endforeach

                </div>
            </div>
            <div class="col-md-9 hidden-sm"  >
                <div id="ticketContent">
                    <div class="center">
                        <div class='svgContainer'>
                            {!! file_get_contents(ROOT.'/dist/images/svg/undraw_design_data_khdb.svg') !!}
                        </div>

                        <h3>{!! __('headlines.pick_a_task') !!}</h3>
                        {!! __('text.edit_tasks_in_here') !!}
                    </div>
                </div>
            </div>
        </div>

        @dispatchEvent('allTicketsTable.afterClose', ['tickets' => $allTickets])
    </div>
</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function() {
        @dispatchEvent('scripts.afterOpen')


        @if ($login::userIsAtLeast($roles::$editor))
        leantime.ticketsController.initStatusDropdown();
        @else
        leantime.authController.makeInputReadonly(".maincontentinner");
        @endif



        leantime.ticketsController.initTicketsList("{{ $searchCriteria['groupBy'] }}");

        @dispatchEvent('scripts.beforeClose')

    });

</script>
@endpush @endonce

@endsection

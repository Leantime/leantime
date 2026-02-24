@php
    $sprints = $tpl->get('sprints');
    $searchCriteria = $tpl->get('searchCriteria');
    $currentSprint = $tpl->get('currentSprint');
    $allTicketGroups = $tpl->get('allTickets');
    $efforts = $tpl->get('efforts');
    $priorities = $tpl->get('priorities');
    $statusLabels = $tpl->get('allTicketStates');
    $groupBy = $tpl->get('groupBy');
    $newField = $tpl->get('newField');
    $numberofColumns = count($tpl->get('allTicketStates')) - 1;
    $size = floor(100 / $numberofColumns);
@endphp

@php $tpl->displaySubmodule('tickets-ticketHeader') @endphp

<div class="maincontent">

    @php $tpl->displaySubmodule('tickets-ticketBoardTabs') @endphp

    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                @dispatchEvent('filters.afterLefthandSectionOpen')
                @php
                    $tpl->displaySubmodule('tickets-ticketNewBtn');
                    $tpl->displaySubmodule('tickets-ticketFilter');
                @endphp
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
                        <x-globals::forms.input name="headline" autofocus placeholder="{{ __('input.placeholders.create_task') }}" style="width: 100%;" />
                        <input type="hidden" name="sprint" value="{{ $currentSprint }}" />
                        <input type="hidden" name="quickadd" value="1"/>
                        <x-globals::forms.button submit type="primary" name="saveTicket" style="vertical-align: top;">{{ __('buttons.save') }}</x-globals::forms.button>
                    </form>

                    @foreach($allTicketGroups as $group)
                        @if($group['label'] != 'all')
                            <h5 class="accordionTitle {{ $group['class'] }}" @if(!empty($group['color'])) style="color:{{ htmlspecialchars($group['color']) }}" @endif id="accordion_link_{{ $group['id'] }}">
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
                                <th style="width:20px" class="status-col">{{ __('label.todo_status') }}</th>
                                <th>{{ __('label.title') }}</th>
                            </tr>
                            @dispatchEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets])
                            </thead>

                            @dispatchEvent('allTicketsTable.afterHead', ['tickets' => $allTickets])
                            <tbody>
                            @dispatchEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets])
                            @foreach($allTickets as $rowNum => $row)
                                <tr onclick="leantime.ticketsController.loadTicketToContainer('{{ $row['id'] }}', '#ticketContent')" id="row-{{ $row['id'] }}" class="ticketRows">
                                    @dispatchEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets])
                                    <td data-order="{{ isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['sortKey'] : '' }}" data-search="{{ isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['name'] : '' }}" class="roundStatusBtn" style="width:20px">
                                        <x-globals::dropdownPill
                                            type="status"
                                            :parentId="$row['id']"
                                            selectedClass="{{ isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['class'] : '' }}"
                                            :selectedKey="$row['status']"
                                            :options="$statusLabels"
                                            :colorized="true"
                                            headerLabel="{{ __('dropdown.choose_status') }}"
                                        />
                                    </td>

                                    <td data-search="{{ isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['name'] : '' }}" data-order="{{ e($row['headline']) }}">
                                        <a href="javascript:void(0);"><strong>{{ e($row['headline']) }}</strong></a>
                                    </td>

                                    @dispatchEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum])
                                </tr>
                            @endforeach
                            @dispatchEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets])
                            </tbody>
                            @dispatchEvent('allTicketsTable.afterBody', ['tickets' => $allTickets])
                        </table>

                        @if($group['label'] != 'all')
                            </div>
                        @endif
                    @endforeach

                </div>
            </div>
            <div class="col-md-9 hidden-sm">
                <div id="ticketContent">
                    <div class="center">
                        <div class='svgContainer'>
                            {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
                        </div>

                        <h3>{{ __('headlines.pick_a_task') }}</h3>
                        {{ __('text.edit_tasks_in_here') }}
                    </div>
                </div>
            </div>
        </div>

        @dispatchEvent('allTicketsTable.afterClose', ['tickets' => $allTickets])
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {
        @dispatchEvent('scripts.afterOpen')

        @if($login::userIsAtLeast($roles::$editor))
        leantime.ticketsController.initStatusDropdown();
        @else
        leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

        leantime.ticketsController.initTicketsList("{{ $searchCriteria['groupBy'] }}");

        @dispatchEvent('scripts.beforeClose')

    });

</script>

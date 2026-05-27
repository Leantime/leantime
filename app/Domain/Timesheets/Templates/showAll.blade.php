@extends($layout)
@section('content')

@once
@push('scripts')
<script type="text/javascript">
    function filterProjectsByClient() {
        var selectedClientId = jQuery('select[name="clientId"]').val();
        var projectSelect = jQuery('select[name="project"]');

        if (selectedClientId === '-1') {
            projectSelect.find('option[data-client-id]').show();
        } else {
            projectSelect.find('option[data-client-id]').hide();
            projectSelect.find('option[data-client-id="' + selectedClientId + '"]').show();
        }

        if (projectSelect.find('option:selected').is(':hidden')) {
            projectSelect.val('-1');
        }

        filterTicketsByProject();
    }

    function filterTicketsByProject() {
        var selectedProjectId = jQuery('select[name="project"]').val();
        var ticketSelect = jQuery('select[name="ticket"]');

        if (ticketSelect.length === 0) {
            return;
        }

        if (selectedProjectId === '-1') {
            ticketSelect.find('option[data-project-id]').show();
        } else {
            ticketSelect.find('option[data-project-id]').hide();
            ticketSelect.find('option[data-project-id="' + selectedProjectId + '"]').show();
        }

        if (ticketSelect.find('option:selected').is(':hidden')) {
            ticketSelect.val('-1');
        }
    }

    jQuery(document).ready(function(){
        jQuery("#checkAllEmpl").change(function(){
            jQuery(".invoicedEmpl").prop('checked', jQuery(this).prop("checked"));
            if (jQuery(this).prop("checked") == true) {
                jQuery(".invoicedEmpl").attr("checked", "checked");
                jQuery(".invoicedEmpl").parent().addClass("checked");
            } else {
                jQuery(".invoicedEmpl").removeAttr("checked");
                jQuery(".invoicedEmpl").parent().removeClass("checked");
            }
        });

        jQuery("#checkAllComp").change(function(){
            jQuery(".invoicedComp").prop('checked', jQuery(this).prop("checked"));
            if (jQuery(this).prop("checked") == true) {
                jQuery(".invoicedComp").attr("checked", "checked");
                jQuery(".invoicedComp").parent().addClass("checked");
            } else {
                jQuery(".invoicedComp").removeAttr("checked");
                jQuery(".invoicedComp").parent().removeClass("checked");
            }
        });

        jQuery("#checkAllPaid").change(function(){
            jQuery(".paid").prop('checked', jQuery(this).prop("checked"));
            if (jQuery(this).prop("checked") == true) {
                jQuery(".paid").attr("checked", "checked");
                jQuery(".paid").parent().addClass("checked");
            } else {
                jQuery(".paid").removeAttr("checked");
                jQuery(".paid").parent().removeClass("checked");
            }
        });

        jQuery('select[name="clientId"]').change(filterProjectsByClient);
        jQuery('select[name="project"]').change(filterTicketsByProject);

        leantime.timesheetsController.initTimesheetsTable();

        @if ($login::userIsAtLeast($roles::$manager))
            leantime.timesheetsController.initEditTimeModal();
        @endif

        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 1)
    });
</script>
@endpush
@endonce

<!-- page header -->
<div class="pageheader">
    <div class="pageicon"><span class="fa-solid fa-business-time"></span></div>
        <div class="pagetitle">
        <h1>{!! __('headlines.all_timesheets') !!}</h1>
    </div>
</div>
<!-- page header -->


<div class="maincontent">
    <div class="maincontentinner">
        <form action="{{ BASE_URL }}/timesheets/showAll" method="post" id="form" name="form">

            <div class="pull-right">
                <div id="tableButtons" style="display:inline-block"></div>
            </div>
            <div class="clearfix"></div>
            <div class="headtitle" style="">

            <table cellpadding="10" cellspacing="0" width="90%" class="table dataTable filterTable">
                <tr>
                    <td>
                        <label for="clients">{!! __('label.client') !!}</label>
                        <select name="clientId">
                            <option value="-1">{{ strip_tags(__('menu.all_clients')) }}</option>
                            @foreach ($allClients as $client)
                                <option value="{{ $client['id'] }}"
                                    @if ($clientFilter == $client['id'])
                                        selected="selected"
                                    @endif
                                >{{ $client['name'] }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <label for="projects">{!! __('label.project') !!}</label>
                        <select name="project" style="max-width:120px;">
                            <option value="-1">{{ strip_tags(__('menu.all_projects')) }}</option>
                            @foreach ($allProjects as $project)
                                <option value="{{ $project['id'] }}" data-client-id="{{ $project['clientId'] }}"
                                    @if ($projectFilter == $project['id'])
                                        selected="selected"
                                    @endif
                                >{{ $project['name'] }}</option>
                            @endforeach
                        </select>
                    </td>
                    @if (! empty($allTickets))
                    <td>
                        <label for="ticket">{!! __('label.ticket') !!}</label>
                            <select name="ticket" style="max-width:120px;">
                                <option value="-1">{{ strip_tags(__('menu.all_tickets')) }}</option>
                                @foreach ($allTickets as $ticket)
                                    <option value="{{ $ticket['id'] }}" data-project-id="{{ $ticket['projectId'] }}"
                                        @if ($ticketFilter == $ticket['id'])
                                            selected="selected"
                                        @endif
                                    >{{ $ticket['headline'] }}</option>
                                @endforeach
                            </select>
                    </td>
                    @endif

                    <td>
                        <label for="dateFrom">{!! __('label.date_from') !!}</label>
                        <input type="text" id="dateFrom" class="dateFrom"  name="dateFrom" autocomplete="off"
                        value="{{ format($dateFrom)->date() }}" size="5" style="max-width:100px; margin-bottom:10px"/></td>
                    <td>
                        <label for="dateTo">{!! __('label.date_to') !!}</label>
                        <input type="text" id="dateTo" class="dateTo" name="dateTo" autocomplete="off"
                        value="{{ format($dateTo)->date() }}" size="5" style="max-width:100px; margin-bottom:10px" /></td>
                    <td>
                    <label for="userId">{!! __('label.employee') !!}</label>
                        <select name="userId" id="userId" onchange="submit();" style="max-width:120px;">
                            <option value="all">{!! __('label.all_employees') !!}</option>

                            @foreach ($employees as $row)
                                <option value="{{ $row['id'] }}"
                                    @if ($row['id'] == $employeeFilter)
                                        selected="selected"
                                    @endif
                                >{{ sprintf(__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <label for="kind">{!! __('label.type') !!}</label>
                        <select id="kind" name="kind" onchange="submit();" style="max-width:120px;">
                            <option value="all">{!! __('label.all_types') !!}</option>
                            @foreach ($kind as $key => $row)
                                <option value="{{ $key }}"
                                    @if ($key == $actKind)
                                        selected="selected"
                                    @endif
                                >{!! __($row) !!}</option>
                            @endforeach

                        </select>
                    </td>
                    <td>
                        <label for="invEmpl">{!! __('label.invoiced') !!}</label>
                        <select name="invEmpl" id="invEmpl" style="max-width:120px;">
                            <option value="all" @if ($invEmpl == 'all' || ! $invEmpl) selected="selected" @endif>{!! __('label.invoiced_all') !!}</option>
                            <option value="1" @if ($invEmpl == '1') selected="selected" @endif>{!! __('label.invoiced') !!}</option>
                            <option value="0" @if ($invEmpl == '0') selected="selected" @endif>{!! __('label.invoiced_not') !!}</option>
                        </select>
                    </td>
                    <td>
                        <input type="checkbox" value="on" name="invComp" id="invComp" onclick="submit();"
                            @if ($invComp == '1')
                                checked="checked"
                            @endif
                        />
                        <label for="invEmpl">{!! __('label.invoiced_comp') !!}</label>
                    </td>

                    <td>
                        <input type="checkbox" value="on" name="paid" id="paid" onclick="submit();"
                            @if ($paid == '1')
                                checked="checked"
                            @endif
                        />
                        <label for="paid">{!! __('label.paid') !!}</label>
                    </td>
                    <td>
                        <input type="hidden" name='filterSubmit' value="1"/>
                        <input type="submit" value="{{ __('buttons.search') }}" class="reload" />
                    </td>
                </tr>
            </table>
            </div>

            <table cellpadding="0" cellspacing="0" border="0" class="table table-bordered display" id="allTimesheetsTable">
                <colgroup>
                      <col class="con0" width="100px"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1" />
                      <col class="con0"/>
                      <col class="con1"/>
                      <col class="con0"/>
                      <col class="con1"/>
                      <col class="con0"/>
                      <col class="con1"/>
                </colgroup>
                <thead>
                    <tr>
                        <th>{!! __('label.id') !!}</th>
                        <th>{!! __('label.date') !!}</th>
                        <th>{!! __('label.hours') !!}</th>
                        <th>{!! __('label.plan_hours') !!}</th>
                        <th>{!! __('label.difference') !!}</th>
                        <th>{!! __('label.ticket') !!}</th>
                        <th>{!! __('label.project') !!}</th>
                        <th>{!! __('label.client') !!}</th>
                        <th>{!! __('label.employee') !!}</th>
                        <th>{!! __('label.type') !!}</th>
                        <th>{!! __('label.milestone') !!}</th>
                        <th>{!! __('label.tags') !!}</th>
                        <th>{!! __('label.description') !!}</th>
                        <th>{!! __('label.invoiced') !!}</th>
                        <th>{!! __('label.invoiced_comp') !!}</th>
                        <th>{!! __('label.paid') !!}</th>
                    </tr>

                </thead>
                <tbody>

                @php
                    $sum = 0;
                    $billableSum = 0;
                @endphp

                @foreach ($allTimesheets as $row)
                    @php $sum = $sum + $row['hours']; @endphp
                    <tr>
                        <td data-order="{{ $row['id'] }}">
                                @if ($login::userIsAtLeast($roles::$manager))
                                <a href="{{ BASE_URL }}/timesheets/editTime/{{ $row['id'] }}" class="editTimeModal">#{{ $row['id'] . ' - ' . __('label.edit') }} </a>
                                @else
                                #{{ $row['id'] }}
                                @endif
                        </td>
                        <td data-order="{{ $row['workDate'] }}">
                                {{ format($row['workDate'])->date() }}
                        </td>
                        <td data-order="{{ $row['hours'] }}">{{ $row['hours'] }}</td>
                        <td data-order="{{ $row['planHours'] }}">{{ $row['planHours'] }}</td>
                            @php $diff = $row['planHours'] - $row['hours']; @endphp
                        <td data-order="{{ $diff }}">{{ $diff }}</td>
                        <td data-order="{{ $row['headline'] }}"><a href="#/tickets/showTicket/{{ $row['ticketId'] }}">{{ $row['headline'] }}</a></td>

                        <td data-order="{{ $row['name'] }}"><a href="{{ BASE_URL }}/projects/showProject/{{ $row['projectId'] }}">{{ $row['name'] }}</a></td>
                        <td data-order="{{ $row['clientName'] }}"><a href="{{ BASE_URL }}/clients/showClient/{{ $row['clientId'] }}">{{ $row['clientName'] }}</a></td>

                        <td>{!! sprintf(__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])) !!}</td>
                        <td>{!! __($kind[$row['kind'] ?? 'GENERAL_BILLABLE'] ?? $kind['GENERAL_BILLABLE']) !!}</td>

                        <td>{{ $row['milestone'] }}</td>
                        <td>{{ $row['tags'] }}</td>

                        <td>{{ $row['description'] }}</td>
                        <td data-order="@if ($row['invoicedEmpl'] == '1'){{ format($row['invoicedEmplDate'])->date() }}@endif">
                            @if ($row['invoicedEmpl'] == '1')
                                {{ format($row['invoicedEmplDate'])->date() }}
                            @else
                                @if ($login::userIsAtLeast($roles::$manager))
                                    <input type="checkbox" name="invoicedEmpl[]" class="invoicedEmpl"
                                value="{{ $row['id'] }}" />
                                @endif
                            @endif
                        </td>
                        <td data-order="@if ($row['invoicedComp'] == '1'){{ format($row['invoicedCompDate'])->date() }}@endif">

                            @if ($row['invoicedComp'] == '1')
                                {{ format($row['invoicedCompDate'])->date() }}
                            @else
                                @if ($login::userIsAtLeast($roles::$manager))
                                <input type="checkbox" name="invoicedComp[]" class="invoicedComp" value="{{ $row['id'] }}" />
                                @endif
                            @endif
                        </td>
                        <td data-order="@if ($row['paid'] == '1'){{ format($row['paidDate'])->date() }}@endif">

                            @if ($row['paid'] == '1')
                                {{ format($row['paidDate'])->date() }}
                            @else
                                @if ($login::userIsAtLeast($roles::$manager))
                                    <input type="checkbox" name="paid[]" class="paid" value="{{ $row['id'] }}" />
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>{!! __('label.total_hours') !!}</strong></td>
                        <td colspan="10"><strong>{{ $sum }}</strong></td>

                        <td>
                            @if ($login::userIsAtLeast($roles::$manager))
                            <input type="submit" class="button" value="{{ __('buttons.save') }}" name="saveInvoice" />
                            @endif
                        </td>
                        <td>
                            @if ($login::userIsAtLeast($roles::$manager))
                            <input type="checkbox" id="checkAllEmpl" style="vertical-align: baseline;"/> {!! __('label.select_all') !!}</td>
                            @endif
                        <td>
                            @if ($login::userIsAtLeast($roles::$manager))
                            <input type="checkbox"  id="checkAllComp" style="vertical-align: baseline;"/> {!! __('label.select_all') !!}
                            @endif
                        </td>
                        <td>
                            @if ($login::userIsAtLeast($roles::$manager))
                                <input type="checkbox"  id="checkAllPaid" style="vertical-align: baseline;"/> {!! __('label.select_all') !!}
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>

@endsection

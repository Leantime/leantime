<script type="text/javascript">
    function filterProjectsByClient() {
        var selectedClientId = jQuery('select[name="clientId"]').val();
        var projectSelect = jQuery('select[name="project"]');

        if (selectedClientId === '-1') {
            // Show all projects when "All Clients" is selected
            projectSelect.find('option[data-client-id]').show();
        } else {
            // Hide all projects first
            projectSelect.find('option[data-client-id]').hide();
            // Show only projects for selected client
            projectSelect.find('option[data-client-id="' + selectedClientId + '"]').show();
        }

        // Reset to "All Projects" if current selection is now hidden
        if (projectSelect.find('option:selected').is(':hidden')) {
            projectSelect.val('-1');
        }

        // Also filter tickets when project selection changes
        filterTicketsByProject();
    }

    function filterTicketsByProject() {
        var selectedProjectId = jQuery('select[name="project"]').val();
        var ticketSelect = jQuery('select[name="ticket"]');

        if (ticketSelect.length === 0) {
            return; // No ticket dropdown exists
        }

        if (selectedProjectId === '-1') {
            // Show all tickets when "All Projects" is selected
            ticketSelect.find('option[data-project-id]').show();
        } else {
            // Hide all tickets first
            ticketSelect.find('option[data-project-id]').hide();
            // Show only tickets for selected project
            ticketSelect.find('option[data-project-id="' + selectedProjectId + '"]').show();
        }

        // Reset to "All Tickets" if current selection is now hidden
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

        // Filter projects when client dropdown changes
        jQuery('select[name="clientId"]').change(filterProjectsByClient);

        // Filter tickets when project dropdown changes
        jQuery('select[name="project"]').change(filterTicketsByProject);

        leantime.timesheetsController.initTimesheetsTable();

        @if ($login::userIsAtLeast($roles::$manager))
            leantime.timesheetsController.initEditTimeModal();
        @endif

        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 1)
    });
</script>

<!-- page header -->
<div class="pageheader">
    <div class="pageicon"><span class="fa-solid fa-business-time"></span></div>
        <div class="pagetitle">
        <h1>{{ __('headlines.all_timesheets') }}</h1>
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
                        <label for="clients">{{ __('label.client') }}</label>
                        <x-globals::forms.select :bare="true" name="clientId">
                            <option value="-1">{{ strip_tags(__('menu.all_clients')) }}</option>
                            @foreach ($tpl->get('allClients') as $client)
                                <option value="{{ $client['id'] }}"
                                    @if ($tpl->get('clientFilter') == $client['id'])
                                        selected='selected'
                                    @endif
                                >{{ e($client['name']) }}</option>
                            @endforeach
                        </x-globals::forms.select>
                    </td>
                    <td>
                        <label for="projects">{{ __('label.project') }}</label>
                        <x-globals::forms.select :bare="true" name="project" style="max-width:120px;">
                            <option value="-1">{{ strip_tags(__('menu.all_projects')) }}</option>
                            @foreach ($tpl->get('allProjects') as $project)
                                <option value="{{ $project['id'] }}" data-client-id="{{ $project['clientId'] }}"
                                    @if ($tpl->get('projectFilter') == $project['id'])
                                        selected='selected'
                                    @endif
                                >{{ e($project['name']) }}</option>
                            @endforeach
                        </x-globals::forms.select>
                    </td>
                    @if (! empty($tpl->get('allTickets')))
                    <td>
                        <label for="ticket">{{ __('label.ticket') }}</label>
                            <x-globals::forms.select :bare="true" name="ticket" style="max-width:120px;">
                                <option value="-1">{{ strip_tags(__('menu.all_tickets')) }}</option>
                                @foreach ($tpl->get('allTickets') as $ticket)
                                    <option value="{{ $ticket['id'] }}" data-project-id="{{ $ticket['projectId'] }}"
                                        @if ($tpl->get('ticketFilter') == $ticket['id'])
                                            selected='selected'
                                        @endif
                                    >{{ e($ticket['headline']) }}</option>
                                @endforeach
                            </x-globals::forms.select>
                    </td>
                    @endif

                    <td>
                        <label for="dateFrom">{{ __('label.date_from') }}</label>
                        <input type="text" id="dateFrom" class="dateFrom"  name="dateFrom" autocomplete="off"
                        value="{{ format($tpl->get('dateFrom'))->date() }}" size="5" style="max-width:100px; margin-bottom:10px"/></td>
                    <td>
                        <label for="dateTo">{{ __('label.date_to') }}</label>
                        <input type="text" id="dateTo" class="dateTo" name="dateTo" autocomplete="off"
                        value="{{ format($tpl->get('dateTo'))->date() }}" size="5" style="max-width:100px; margin-bottom:10px" /></td>
                    <td>
                    <label for="userId">{{ __('label.employee') }}</label>
                        <x-globals::forms.select :bare="true" name="userId" id="userId" onchange="submit();" style="max-width:120px;">
                            <option value="all">{{ __('label.all_employees') }}</option>

                            @foreach ($tpl->get('employees') as $row)
                                <option value="{{ $row['id'] }}"
                                    @if ($row['id'] == $tpl->get('employeeFilter'))
                                        selected="selected"
                                    @endif
                                >{{ sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) }}</option>
                            @endforeach
                        </x-globals::forms.select>
                    </td>
                    <td>
                        <label for="kind">{{ __('label.type') }}</label>
                        <x-globals::forms.select :bare="true" id="kind" name="kind" onchange="submit();" style="max-width:120px;">
                            <option value="all">{{ __('label.all_types') }}</option>
                            @foreach ($tpl->get('kind') as $key => $row)
                                <option value="{{ $key }}"
                                    @if ($key == $tpl->get('actKind'))
                                        selected="selected"
                                    @endif
                                >{{ __($row) }}</option>
                            @endforeach

                        </x-globals::forms.select>
                    </td>
                    <td>
                        <label for="invEmpl">{{ __('label.invoiced') }}</label>
                        <x-globals::forms.select :bare="true" name="invEmpl" id="invEmpl" style="max-width:120px;">
                            <option value="all"
                                @if ($tpl->get('invEmpl') == 'all' || ! $tpl->get('invEmpl'))
                                    selected="selected"
                                @endif
                            >{{ __('label.invoiced_all') }}</option>
                            <option value="1"
                                @if ($tpl->get('invEmpl') == '1')
                                    selected="selected"
                                @endif
                            >{{ __('label.invoiced') }}</option>
                            <option value="0"
                                @if ($tpl->get('invEmpl') == '0')
                                    selected="selected"
                                @endif
                            >{{ __('label.invoiced_not') }}</option>
                        </x-globals::forms.select>
                    </td>
                    <td>
                        <input type="checkbox" value="on" name="invComp" id="invComp" onclick="submit();"
                            @if ($tpl->get('invComp') == '1')
                                checked="checked"
                            @endif
                        />
                        <label for="invEmpl">{{ __('label.invoiced_comp') }}</label>
                    </td>

                    <td>
                        <input type="checkbox" value="on" name="paid" id="paid" onclick="submit();"
                            @if ($tpl->get('paid') == '1')
                                checked="checked"
                            @endif
                        />
                        <label for="paid">{{ __('label.paid') }}</label>
                    </td>
                    <td>
                        <input type="hidden" name='filterSubmit' value="1"/>
                        <x-globals::forms.button submit type="primary" class="reload">{{ __('buttons.search') }}</x-globals::forms.button>
                    </td>
                </tr>
            </table>
            </div>

            <div style="overflow-x: auto;">
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
                        <th>{{ __('label.id') }}</th>
                        <th>{{ __('label.date') }}</th>
                        <th>{{ __('label.hours') }}</th>
                        <th>{{ __('label.plan_hours') }}</th>
                        <th>{{ __('label.difference') }}</th>
                        <th>{{ __('label.ticket') }}</th>
                        <th>{{ __('label.project') }}</th>
                        <th>{{ __('label.client') }}</th>
                        <th>{{ __('label.employee') }}</th>
                        <th>{{ __('label.type') }}</th>
                        <th>{{ __('label.milestone') }}</th>
                        <th>{{ __('label.tags') }}</th>
                        <th>{{ __('label.description') }}</th>
                        <th>{{ __('label.invoiced') }}</th>
                        <th>{{ __('label.invoiced_comp') }}</th>
                        <th>{{ __('label.paid') }}</th>
                    </tr>

                </thead>
                <tbody>

                @php
                    $sum = 0;
                    $billableSum = 0;
                @endphp

                @foreach ($tpl->get('allTimesheets') as $row)
                    @php $sum = $sum + $row['hours']; @endphp
                    <tr>
                        <td data-order="{{ e($row['id']) }}">
                                @if ($login::userIsAtLeast($roles::$manager))
                                <a href="{{ BASE_URL }}/timesheets/editTime/{{ $row['id'] }}" class="editTimeModal">#{{ $row['id'] }} - {{ __('label.edit') }} </a>
                                @else
                                #{{ $row['id'] }}
                                @endif
                        </td>
                        <td data-order="{{ e($row['workDate']) }}">
                                {{ format($row['workDate'])->date() }}
                        </td>
                        <td data-order="{{ e($row['hours']) }}">{{ e($row['hours']) }}</td>
                        <td data-order="{{ e($row['planHours']) }}">{{ e($row['planHours']) }}</td>
                            @php $diff = $row['planHours'] - $row['hours']; @endphp
                        <td data-order="{{ $diff }}">{{ $diff }}</td>
                        <td data-order="{{ e($row['headline']) }}"><a href="#/tickets/showTicket/{{ $row['ticketId'] }}">{{ e($row['headline']) }}</a></td>

                        <td data-order="{{ e($row['name']) }}"><a href="{{ BASE_URL }}/projects/showProject/{{ $row['projectId'] }}">{{ e($row['name']) }}</a></td>
                        <td data-order="{{ e($row['clientName']) }}"><a href="{{ BASE_URL }}/clients/showClient/{{ $row['clientId'] }}">{{ e($row['clientName']) }}</a></td>

                        <td>{{ sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) }}</td>
                        <td>{{ __($tpl->get('kind')[$row['kind'] ?? 'GENERAL_BILLABLE'] ?? $tpl->get('kind')['GENERAL_BILLABLE']) }}</td>

                        <td>{{ e($row['milestone']) }}</td>
                        <td>{{ e($row['tags']) }}</td>

                        <td>{{ e($row['description']) }}</td>
                        <td data-order="@if ($row['invoicedEmpl'] == '1'){{ format($row['invoicedEmplDate'])->date() }}@endif">
                            @if ($row['invoicedEmpl'] == '1')
                                {{ format($row['invoicedEmplDate'])->date() }}
                            @else
                                @if ($login::userIsAtLeast($roles::$manager))
                                    <x-globals::forms.checkbox name="invoicedEmpl[]" class="invoicedEmpl"
                                        value="{{ $row['id'] }}" />
                                @endif
                            @endif
                        </td>
                        <td data-order="@if ($row['invoicedComp'] == '1'){{ format($row['invoicedCompDate'])->date() }}@endif">

                            @if ($row['invoicedComp'] == '1')
                                {{ format($row['invoicedCompDate'])->date() }}
                            @else
                                @if ($login::userIsAtLeast($roles::$manager))
                                <x-globals::forms.checkbox name="invoicedComp[]" class="invoicedComp" value="{{ $row['id'] }}" />
                                @endif
                            @endif
                        </td>
                        <td data-order="@if ($row['paid'] == '1'){{ format($row['paidDate'])->date() }}@endif">

                            @if ($row['paid'] == '1')
                                {{ format($row['paidDate'])->date() }}
                            @else
                                @if ($login::userIsAtLeast($roles::$manager))
                                    <x-globals::forms.checkbox name="paid[]" class="paid" value="{{ $row['id'] }}" />
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>{{ __('label.total_hours') }}</strong></td>
                        <td colspan="10"><strong>{{ $sum }}</strong></td>

                        <td>
                            @if ($login::userIsAtLeast($roles::$manager))
                            <x-globals::forms.button submit type="primary" name="saveInvoice">{{ __('buttons.save') }}</x-globals::forms.button>
                            @endif
                        </td>
                        <td>
                            @if ($login::userIsAtLeast($roles::$manager))
                            <input type="checkbox" id="checkAllEmpl" aria-label="{{ __('label.select_all') }}" style="vertical-align: baseline;"/> {{ __('label.select_all') }}</td>
                            @endif
                        <td>
                            @if ($login::userIsAtLeast($roles::$manager))
                            <input type="checkbox"  id="checkAllComp" aria-label="{{ __('label.select_all') }}" style="vertical-align: baseline;"/> {{ __('label.select_all') }}
                            @endif
                        </td>
                        <td>
                            @if ($login::userIsAtLeast($roles::$manager))
                                <input type="checkbox"  id="checkAllPaid" aria-label="{{ __('label.select_all') }}" style="vertical-align: baseline;"/> {{ __('label.select_all') }}
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        leantime.timesheetsController.initMyTimesheetsTable();
        leantime.timesheetsController.initEditTimeModal();
        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 1);
    });
</script>

<x-globals::layout.page-header icon="schedule" headline="{{ __('headline.my_timesheets') }}" />

<div class="maincontent">
    <div class="maincontentinner">
        {!! $tpl->displayNotification() !!}

        <form action="{{ BASE_URL }}/timesheets/showMyList" method="post" id="form" name="form">
            <div class="tw:flex tw:items-end tw:flex-wrap tw:gap-x-4 tw:gap-y-3 tw:mb-4 tw:p-5">
                <div>
                    <label for="dateFrom" class="tw:block tw:text-xs tw:text-[var(--secondary-font-color)] tw:mb-1">{{ __('label.date_from') }}</label>
                    <input type="text" id="dateFrom" class="dateFrom" name="dateFrom" autocomplete="off"
                        value="{{ $tpl->get('dateFrom')->formatDateForUser() }}" class="tw:w-[110px]" />
                </div>
                <div>
                    <label for="dateTo" class="tw:block tw:text-xs tw:text-[var(--secondary-font-color)] tw:mb-1">{{ __('label.date_to') }}</label>
                    <input type="text" id="dateTo" class="dateTo" name="dateTo" autocomplete="off"
                        value="{{ $tpl->get('dateTo')->formatDateForUser() }}" class="tw:w-[110px]" />
                </div>
                <div>
                    <label for="kind" class="tw:block tw:text-xs tw:text-[var(--secondary-font-color)] tw:mb-1">{{ __('label.type') }}</label>
                    <x-globals::forms.select :bare="true" id="kind" name="kind" onchange="submit();" class="tw:max-w-[130px]">
                        <option value="all">{{ __('label.all_types') }}</option>
                        @foreach ($tpl->get('kind') as $key => $row)
                            <option value="{{ $key }}"
                                @if ($key == $tpl->get('actKind'))
                                    selected="selected"
                                @endif
                            >{{ __($row) }}</option>
                        @endforeach
                    </x-globals::forms.select>
                </div>
                <div class="tw:pb-[2px]">
                    <input type="hidden" name="filterSubmit" value="1"/>
                    <x-globals::forms.button submit type="primary" class="reload">{{ __('buttons.search') }}</x-globals::forms.button>
                </div>

                <div class="tw:flex-1"></div>

                <div class="tw:pb-[2px]">
                    <x-globals::actions.dropdown-menu variant="link" trailing-visual="arrow_drop_down" :label="__('links.list_view')">
                        <li><a href="{{ BASE_URL }}/timesheets/showMy">{!! __('links.week_view') !!}</a></li>
                        <li><a href="{{ BASE_URL }}/timesheets/showMyList" class="active">{!! __('links.list_view') !!}</a></li>
                    </x-globals::actions.dropdown-menu>
                </div>
                <div class="tw:pb-[2px]">
                    <div id="tableButtons" class="tw:inline-block"></div>
                </div>
            </div>

            <x-globals::elements.table :hover="true" id="myTimesheetsTable" class="display">
                <x-slot:head>
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
                    </colgroup>
                    <tr>
                        <th>{{ __('label.id') }}</th>
                        <th>{{ __('label.date') }}</th>
                        <th>{{ __('label.hours') }}</th>
                        <th>{{ __('label.plan_hours') }}</th>
                        <th>{{ __('label.difference') }}</th>
                        <th>{{ __('label.ticket') }}</th>
                        <th>{{ __('label.project') }}</th>
                        <th>{{ __('label.employee') }}</th>
                        <th>{{ __('label.type') }}</th>
                        <th>{{ __('label.description') }}</th>
                        <th>{{ __('label.invoiced') }}</th>
                        <th>{{ __('label.invoiced_comp') }}</th>
                        <th>{{ __('label.paid') }}</th>
                    </tr>
                </x-slot:head>

                @php $sum = 0; @endphp

                @if (is_array($tpl->get('allTimesheets')))
                    @foreach ($tpl->get('allTimesheets') as $row)
                        @php $sum += $row['hours']; @endphp
                        <tr>
                            <td data-order="{{ e($row['id']) }}">
                                <a href="{{ BASE_URL }}/timesheets/editTime/{{ $row['id'] }}" class="editTimeModal">#{{ $row['id'] }} - {{ __('label.edit') }}</a>
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
                            <td>{{ sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) }}</td>
                            <td>{{ __($tpl->get('kind')[$row['kind'] ?? 'GENERAL_BILLABLE'] ?? $tpl->get('kind')['GENERAL_BILLABLE']) }}</td>
                            <td>{{ e($row['description']) }}</td>
                            <td data-order="@if ($row['invoicedEmpl'] == '1'){{ format($row['invoicedEmplDate'])->date() }}@endif">
                                @if ($row['invoicedEmpl'] == '1')
                                    {{ format($row['invoicedEmplDate'])->date() }}
                                @endif
                            </td>
                            <td data-order="@if ($row['invoicedComp'] == '1'){{ format($row['invoicedCompDate'])->date() }}@endif">
                                @if ($row['invoicedComp'] == '1')
                                    {{ format($row['invoicedCompDate'])->date() }}
                                @endif
                            </td>
                            <td data-order="@if ($row['paid'] == '1'){{ format($row['paidDate'])->date() }}@endif">
                                @if ($row['paid'] == '1')
                                    {{ format($row['paidDate'])->date() }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif

                <x-slot:foot>
                    <tr>
                        <td colspan="2"><strong>{{ __('label.total_hours') }}</strong></td>
                        <td colspan="11"><strong>{{ $sum }}</strong></td>
                    </tr>
                </x-slot:foot>
            </x-globals::elements.table>
        </form>
    </div>
</div>

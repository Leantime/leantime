@php
    use Leantime\Core\Support\FromFormat;
@endphp

<!-- page header -->
<div class="pageheader">
    <div class="pageicon"><span class="fa-regular fa-clock"></span></div>
    <div class="pagetitle">
        <h5>{{ __('headline.overview') }}</h5>
        <h1>{{ __('headline.my_timesheets') }}</h1>
    </div>
</div>
<!-- page header -->


<div class="maincontent">
    <div class="maincontentinner">
        {!! $tpl->displayNotification() !!}

        <form action="{{ BASE_URL }}/timesheets/showMyList" method="post" id="form" name="form">
            <div class="filterWrapper tw:relative">
                <a onclick="jQuery('.filterBar').toggle();" class="btn btn-default pull-left">{!! __('links.filter') !!} (1)</a>
                <div class="filterBar" style="display:none; top:30px;">

                    <div class="filterBoxLeft">
                        <label for="dateFrom">{{ __('label.date_from') }}</label>
                        <input type="text"
                               id="dateFrom"
                               class="dateFrom"
                               name="dateFrom"
                               value="{{ $tpl->get('dateFrom')->formatDateForUser() }}"
                               style="width:110px"/>
                    </div>
                    <div class="filterBoxLeft">
                        <label for="dateTo">{{ __('label.date_to') }}</label>
                        <input type="text"
                               id="dateTo"
                               class="dateTo"
                               name="dateTo"
                               value="{{ $tpl->get('dateTo')->formatDateForUser() }}"
                               style="width:110px" />
                    </div>

                    <div class="filterBoxLeft">
                        <label for="kind">{{ __('label.type') }}</label>
                        <select id="kind" name="kind" onchange="submit();">
                            <option value="all">{{ __('label.all_types') }}</option>
                            @foreach ($tpl->get('kind') as $key => $row)
                                <option value="{{ $key }}"
                                    @if ($key == $tpl->get('actKind'))
                                        selected="selected"
                                    @endif
                                >{{ __($row) }}</option>
                            @endforeach

                        </select>
                    </div>
                    <div class="filterBoxLeft">
                        <input type="submit" value="{{ __('buttons.search') }}" class="reload" />
                    </div>
                </div>
            </div>
            <div class="pull-right">
                <div class="btn-group viewDropDown">
                    <button class="btn dropdown-toggle" data-toggle="dropdown">{!! __('links.list_view') !!} {!! __('links.view') !!}</button>
                    <ul class="dropdown-menu">
                        <li><a href="{{ BASE_URL }}/timesheets/showMy" >{!! __('links.week_view') !!}</a></li>
                        <li><a href="{{ BASE_URL }}/timesheets/showMyList" class="active">{!! __('links.list_view') !!}</a></li>
                    </ul>
                </div>
            </div>

            <div class="pull-right" style="margin-right:3px;">
                <div id="tableButtons" style="display:inline-block"></div>
            </div>

            <div class="clearfix"></div>

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
                        <th>{{ __('label.employee') }}</th>
                        <th>{{ __('label.type') }}</th>
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
                            <a href="{{ BASE_URL }}/timesheets/editTime/{{ $row['id'] }}" class="editTimeModal" id="editTimesheet-{{ $row['id'] }}">#{{ $row['id'] }} - {{ __('label.edit') }} </a></td>
                        <td data-order="{{ format($row['workDate'])->isoDateTime() }}">
                            {{ format($row['workDate'])->date() }}
                            {{ format($row['workDate'])->time() }}
                        </td>
                        <td data-order="{{ e($row['hours']) }}">
                            {{ e($row['hours'] ?: 0) }}
                        </td>
                        <td data-order="{{ e($row['planHours']) }}">
                            {{ e($row['planHours'] ?: 0) }}
                        </td>
                        @php $diff = ($row['planHours'] ?: 0) - ($row['hours'] ?: 0); @endphp
                        <td data-order="{{ $diff }}">
                            {{ $diff }}
                        </td>
                        <td data-order="{{ e($row['headline']) }}">
                            <a href="#/tickets/showTicket/{{ $row['ticketId'] }}">{{ e($row['headline']) }}</a>
                        </td>

                        <td data-order="{{ e($row['name']) }}">
                            <a href="{{ BASE_URL }}/projects/showProject/{{ $row['projectId'] }}">{{ e($row['name']) }}</a>
                        </td>
                        <td>
                            {{ sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) }}
                        </td>
                        <td>
                            {{ __($tpl->get('kind')[$row['kind']]) }}
                        </td>
                        <td>
                            {{ e($row['description']) }}
                        </td>
                        <td data-order="@if ($row['invoicedEmpl'] == '1'){{ format(value: $row['invoicedEmplDate'], fromFormat: FromFormat::DbDate)->date() }}@endif">
                            @if ($row['invoicedEmpl'] == '1')
                                {{ format(value: $row['invoicedEmplDate'], fromFormat: FromFormat::DbDate)->date() }}
                            @else
                                {{ __('label.pending') }}
                            @endif
                        </td>
                        <td data-order="@if ($row['invoicedComp'] == '1'){{ format(value: $row['invoicedCompDate'], fromFormat: FromFormat::DbDate)->date() }}@endif">
                            @if ($row['invoicedComp'] == '1')
                                {{ format(value: $row['invoicedCompDate'], fromFormat: FromFormat::DbDate)->date() }}
                            @else
                                {{ __('label.pending') }}
                            @endif
                        </td>
                        <td data-order="@if ($row['paid'] == '1'){{ format(value: $row['paidDate'], fromFormat: FromFormat::DbDate)->date() }}@endif">
                            @if ($row['paid'] == '1')
                                {{ format(value: $row['paidDate'], fromFormat: FromFormat::DbDate)->date() }}
                            @else
                                {{ __('label.pending') }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td colspan="1"><strong>{{ __('label.total_hours') }}</strong></td>
                        <td colspan="11"><strong>{{ $sum }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        leantime.timesheetsController.initTimesheetsTable();
        leantime.timesheetsController.initEditTimeModal();
        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 1);
    });
</script>

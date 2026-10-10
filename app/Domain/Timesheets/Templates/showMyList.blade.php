@extends($layout)

@section('content')

@php
    use Leantime\Core\Support\FromFormat;
@endphp

<!-- page header -->
<div class="pageheader">
    <div class="pageicon"><span class="fa-regular fa-clock"></span></div>
    <div class="pagetitle">
        <h5>{!! __('headline.overview') !!}</h5>
        <h1>{!! __('headline.my_timesheets') !!}</h1>
    </div>
</div>
<!-- page header -->

<div class="maincontent">
    <x-global::navigation.view-tabs :tabs="[
        ['url' => BASE_URL.'/timesheets/showMy', 'label' => __('links.week_view'), 'active' => false],
        ['url' => BASE_URL.'/timesheets/showMyList', 'label' => __('links.list_view'), 'active' => true],
    ]">
        <x-slot:actions>
            {{-- The filter fields belong to the #form form below (form="…"). --}}
            <x-global::actions.dropdown variant="filter" menu-as="div" menu-class="filterBar" menu-style="width:250px;" keep-open class="filterWrapper">
                <x-slot:trigger class="btn-link">{!! __('links.filter') !!} <span class="badge badge-primary">1</span></x-slot:trigger>
                <label for="dateFrom">{!! __('label.date_from') !!}</label>
                <input type="text" id="dateFrom" class="dateFrom" name="dateFrom" form="form" value="{{ $dateFrom->formatDateForUser() }}" />
                <label for="dateTo">{!! __('label.until') !!}</label>
                <input type="text" id="dateTo" class="dateTo" name="dateTo" form="form" value="{{ $dateTo->formatDateForUser() }}" />
                <label for="kind">{!! __('label.type') !!}</label>
                <x-global::forms.select id="kind" name="kind" form="form" onchange="this.form.submit();">
                    <option value="all">{!! __('label.all_types') !!}</option>
                    @foreach($kind as $key => $row)
                        <option value="{{ $key }}" @selected($key == $actKind)>{!! __($row) !!}</option>
                    @endforeach
                </x-global::forms.select>
                <div class="tw-mt-2">
                    <x-global::forms.button tag="input" inputType="submit" form="form" contentRole="primary" :labelText="__('buttons.search')" class="reload" />
                </div>
            </x-global::actions.dropdown>
        </x-slot:actions>
    </x-global::navigation.view-tabs>

    <div class="maincontentinner">
        {!! $tpl->displayNotification() !!}

        <form action="{{ BASE_URL }}/timesheets/showMyList" method="post" id="form" name="form">
            {{-- Export / column visibility act on the table, so they stay with it (like the To-Do table). --}}
            <div class="pull-right">
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
                        <th>{!! __('label.id') !!}</th>
                        <th>{!! __('label.date') !!}</th>
                        <th>{!! __('label.hours') !!}</th>
                        <th>{!! __('label.plan_hours') !!}</th>
                        <th>{!! __('label.difference') !!}</th>
                        <th>{!! __('label.ticket') !!}</th>
                        <th>{!! __('label.project') !!}</th>
                        <th>{!! __('label.employee') !!}</th>
                        <th>{!! __('label.type') !!}</th>
                        <th>{!! __('label.description') !!}</th>
                        <th>{!! __('label.invoiced') !!}</th>
                        <th>{!! __('label.invoiced_comp') !!}</th>
                        <th>{!! __('label.paid') !!}</th>
                    </tr>
                </thead>
                <tbody>

                @php $sum = 0; @endphp
                @foreach($allTimesheets as $row)
                    @php $sum = $sum + $row['hours']; @endphp
                    <tr>
                        <td data-order="{{ $row['id'] }}">
                            <a href="{{ BASE_URL }}/timesheets/editTime/{{ $row['id'] }}" class="editTimeModal" id="editTimesheet-{{ $row['id'] }}">#{{ $row['id'] }} - {!! __('label.edit') !!} </a></td>
                        <td data-order="{{ format($row['workDate'])->isoDateTime() }}">
                            {{ format($row['workDate'])->date() }}
                            {{ format($row['workDate'])->time() }}
                        </td>
                        <td data-order="{{ $row['hours'] }}">
                            {{ $row['hours'] ?: 0 }}
                            <small class="tw-opacity-60 tw-whitespace-nowrap">({{ \Leantime\Core\Support\Format::hoursMinutes($row['hours'] ?: 0, __('text.hours_minutes_short')) }})</small>
                        </td>
                        <td data-order="{{ $row['planHours'] }}">
                            {{ $row['planHours'] ?: 0 }}
                        </td>
                        @php $diff = ($row['planHours'] ?: 0) - ($row['hours'] ?: 0); @endphp
                        <td data-order="{{ $diff }}">
                            {{ $diff }}
                        </td>
                        <td data-order="{{ $row['headline'] }}">
                            <a href="#/tickets/showTicket/{{ $row['ticketId'] }}">{{ $row['headline'] }}</a>
                        </td>
                        <td data-order="{{ $row['name'] }}">
                            <a href="{{ BASE_URL }}/projects/showProject/{{ $row['projectId'] }}">{{ $row['name'] }}</a>
                        </td>
                        <td>
                            {{ sprintf(__('text.full_name'), e($row['firstname']), e($row['lastname'])) }}
                        </td>
                        <td>
                            {!! __($kind[$row['kind']]) !!}
                        </td>
                        <td>
                            {{ $row['description'] }}
                        </td>
                        <td data-order="@if($row['invoicedEmpl'] == '1'){{ format(value: $row['invoicedEmplDate'], fromFormat: FromFormat::DbDate)->date() }}@endif">
                            @if($row['invoicedEmpl'] == '1')
                                {{ format(value: $row['invoicedEmplDate'], fromFormat: FromFormat::DbDate)->date() }}
                            @else
                                {!! __('label.pending') !!}
                            @endif
                        </td>
                        <td data-order="@if($row['invoicedComp'] == '1'){{ format(value: $row['invoicedCompDate'], fromFormat: FromFormat::DbDate)->date() }}@endif">
                            @if($row['invoicedComp'] == '1')
                                {{ format(value: $row['invoicedCompDate'], fromFormat: FromFormat::DbDate)->date() }}
                            @else
                                {!! __('label.pending') !!}
                            @endif
                        </td>
                        <td data-order="@if($row['paid'] == '1'){{ format(value: $row['paidDate'], fromFormat: FromFormat::DbDate)->date() }}@endif">
                            @if($row['paid'] == '1')
                                {{ format(value: $row['paidDate'], fromFormat: FromFormat::DbDate)->date() }}
                            @else
                                {!! __('label.pending') !!}
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td colspan="1"><strong>{!! __('label.total_hours') !!}</strong></td>
                        <td colspan="11"><strong>{{ $sum }}</strong> <small class="tw-opacity-60 tw-whitespace-nowrap">({{ \Leantime\Core\Support\Format::hoursMinutes($sum, __('text.hours_minutes_short')) }})</small></td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>

@once @push('scripts')
<script type="text/javascript">
    jQuery(document).ready(function(){
        leantime.timesheetsController.initTimesheetsTable();
        leantime.timesheetsController.initEditTimeModal();
        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 1);
    });
</script>
@endpush @endonce

@endsection

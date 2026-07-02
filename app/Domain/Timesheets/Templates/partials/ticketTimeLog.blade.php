@php
    $displayRemainingHours = $remainingHours < 0 ? 0 : $remainingHours;
@endphp

<p>
    @if ($ticket)
        {!! __('label.planned_hours') !!}: {{ $ticket->planHours }}<br />
    @endif
    {!! __('label.booked_hours') !!}: {{ $timesheetsAllHours }}<br />
    {!! __('label.actual_hours_remaining') !!}: {{ $displayRemainingHours }}<br />
</p>

@if ($isManager)
    <h4 class="widgettitle title-light"><span class="fa fa-list"></span>{!! __('headline.time_entries', false) !!}</h4>

    <table class="table">
        <thead>
            <tr>
                <th>{!! __('label.date') !!}</th>
                <th>{!! __('label.employee') !!}</th>
                <th>{!! __('label.hours') !!}</th>
                <th>{!! __('label.description') !!}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach ($timeEntries as $entry)
            <tr>
                <td>{{ format($entry['workDate'])->date() }}</td>
                <td>{!! sprintf(__('text.full_name'), $tpl->escape($entry['firstname']), $tpl->escape($entry['lastname'])) !!}</td>
                <td>
                    <span id="timeEntryHours-{{ $entry['id'] }}">{{ $entry['hours'] }}</span>
                </td>
                <td>{{ $entry['description'] }}</td>
                <td>
                    <a href="javascript:void(0);" onclick="jQuery('#editTimeEntry-{{ $entry['id'] }}').toggle();">
                        {!! __('label.edit') !!}
                    </a>
                    &nbsp;
                    <a href="javascript:void(0);"
                       hx-get="{{ BASE_URL }}/timesheets/timeEntryHistory/get?id={{ $entry['id'] }}"
                       hx-target="#timeEntryHistoryWrapper-{{ $entry['id'] }}"
                       hx-swap="innerHTML"
                       onclick="jQuery('#timeEntryHistory-{{ $entry['id'] }}').toggle();">
                        {!! __('label.timelog_history') !!}
                    </a>

                    <form id="editTimeEntry-{{ $entry['id'] }}" style="display:none;"
                          hx-post="{{ BASE_URL }}/timesheets/ticketTimeLog/save?ticketId={{ $ticket->id }}"
                          hx-target="#ticketTimeLog"
                          hx-swap="innerHTML">
                        <input type="hidden" name="id" value="{{ $entry['id'] }}" />
                        <input type="number" name="hours" value="{{ (int) round($entry['hours']) }}" step="1" min="1" class="input-small" required />
                        <input type="submit" value="{{ __('buttons.save') }}" class="button" />
                        <a href="javascript:void(0);" onclick="jQuery('#editTimeEntry-{{ $entry['id'] }}').toggle();">{!! __('buttons.cancel') !!}</a>
                    </form>

                    <div id="timeEntryHistory-{{ $entry['id'] }}" style="display:none;">
                        <div id="timeEntryHistoryWrapper-{{ $entry['id'] }}"></div>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

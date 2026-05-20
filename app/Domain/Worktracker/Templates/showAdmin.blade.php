@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-users'">
    <h1>Work Session Monitor</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        {{-- ── Summary stats ── --}}
        <div class="row tw-mb-m">
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3 class="{{ $activeNow > 0 ? 'tw-text-green-600' : '' }}">{{ $activeNow }}</h3>
                    <p>Currently Working</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3>{{ $todayGrandTotal }}</h3>
                    <p>Total Hours Today</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3>{{ $totalCount }}</h3>
                    <p>All Sessions</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3>{{ $totalPages }}</h3>
                    <p>Pages</p>
                </div>
            </div>
        </div>

        {{-- ── Sessions table ── --}}
        <div class="row">
            <div class="col-md-12">
                <div class="maincontentinner">
                    <div class="tw-flex tw-items-center tw-justify-between tw-mb-s">
                        <h4 class="widgettitle title-light tw-mb-0">All Sessions</h4>
                        <span class="tw-text-sm tw-text-muted">
                            Page {{ $page }} of {{ $totalPages }} &nbsp;|&nbsp; {{ $totalCount }} records
                        </span>
                    </div>

                    @if (count($sessions) === 0)
                        <div class="tw-p-l tw-text-center">
                            <p>No sessions recorded yet.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="tablesorter table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Date</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Start SS</th>
                                        <th>End SS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sessions as $session)
                                    <tr @if($session['status'] === 'running') class="tw-bg-green-50" @endif>
                                        <td>{{ $session['id'] }}</td>
                                        <td>
                                            <strong>{{ $session['employee_name'] ?: 'Unknown' }}</strong>
                                            @if ($session['username'] ?? '')
                                                <br><small class="tw-text-muted">{{ $session['username'] }}</small>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($session['start_time'])->format('Y-m-d') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($session['start_time'])->format('H:i:s') }}</td>
                                        <td>
                                            @if ($session['end_time'])
                                                {{ \Carbon\Carbon::parse($session['end_time'])->format('H:i:s') }}
                                            @else
                                                <span class="tw-text-green-600 tw-font-semibold">Running…</span>
                                            @endif
                                        </td>
                                        <td class="tw-font-mono">{{ $session['duration_formatted'] }}</td>
                                        <td>
                                            @if ($session['status'] === 'running')
                                                <span class="tag tw-bg-green-100 tw-text-green-800">
                                                    <i class="fa fa-circle tw-mr-xs" style="font-size:8px;"></i>Running
                                                </span>
                                            @else
                                                <span class="tag">Completed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($session['start_screenshot_url'])
                                                <a href="{{ $session['start_screenshot_url'] }}" target="_blank" class="btn btn-xs btn-default">
                                                    <i class="fa fa-image"></i> View
                                                </a>
                                            @else
                                                <span class="tw-text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($session['end_screenshot_url'])
                                                <a href="{{ $session['end_screenshot_url'] }}" target="_blank" class="btn btn-xs btn-default">
                                                    <i class="fa fa-image"></i> View
                                                </a>
                                            @else
                                                <span class="tw-text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if ($totalPages > 1)
                        <div class="tw-flex tw-justify-center tw-mt-m tw-gap-xs">
                            @if ($page > 1)
                                <a href="?page={{ $page - 1 }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-chevron-left"></i> Previous
                                </a>
                            @endif
                            <span class="btn btn-default btn-sm disabled">{{ $page }} / {{ $totalPages }}</span>
                            @if ($page < $totalPages)
                                <a href="?page={{ $page + 1 }}" class="btn btn-default btn-sm">
                                    Next <i class="fa fa-chevron-right"></i>
                                </a>
                            @endif
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

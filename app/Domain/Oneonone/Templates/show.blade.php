@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-handshake'">
    <h1>{{ __('headlines.oneonone.sessions') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <x-global::tabs id="oneononeTabs">
            <x-slot:headings>
                <x-global::tabs.heading name="my">
                    <i class="fa fa-user"></i> {{ __('headlines.oneonone.my_sessions') }}
                    @if($myStats['upcoming'] > 0)
                        <span class="label label-info tw-ml-xs">{{ $myStats['upcoming'] }}</span>
                    @endif
                </x-global::tabs.heading>

                <x-global::tabs.heading name="team">
                    <i class="fa fa-users-gear"></i> {{ __('headlines.oneonone.team_sessions') }}
                    @if(count($teamDashboard) > 0)
                        <span class="label label-default tw-ml-xs">{{ count($teamDashboard) }}</span>
                    @endif
                </x-global::tabs.heading>
            </x-slot:headings>

            <x-slot:contents>

                {{-- ─────────── MY 1:1 SESSIONS ─────────── --}}
                <x-global::tabs.content name="my">

                    {{-- Stats row --}}
                    <div class="row tw-mb-m">
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="bigNumberBox">
                                <h3>{{ $myStats['total'] }}</h3>
                                <p>{{ __('text.oneonone.total_sessions') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="bigNumberBox">
                                <h3>{{ $myStats['upcoming'] }}</h3>
                                <p>{{ __('text.oneonone.upcoming') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="bigNumberBox">
                                <h3>{{ $myStats['completed'] }}</h3>
                                <p>{{ __('text.oneonone.completed') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 col-xs-6">
                            <div class="bigNumberBox">
                                <h3>{{ $myStats['openActions'] }}</h3>
                                <p>{{ __('text.oneonone.open_actions') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 col-sm-12">
                            <h4 class="widgettitle title-light">{{ __('headlines.oneonone.session_history') }}</h4>

                            @if (count($mySessions) === 0)
                                <div class="tw-p-l tw-text-center">
                                    <p>{{ __('text.oneonone.no_sessions_yet') }}</p>
                                </div>
                            @else
                                <ul class="tw-list-none tw-p-0 tw-m-0">
                                    @foreach ($mySessions as $session)
                                        @include('oneonone::partials.sessionCard', [
                                            'session' => $session,
                                            'view' => 'employee',
                                        ])
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="col-md-4 col-sm-12">
                            @include('oneonone::partials.myOpenActions', ['openActionItems' => $myOpenActions])
                        </div>
                    </div>
                </x-global::tabs.content>

                {{-- ─────────── TEAM 1:1 SESSIONS ─────────── --}}
                <x-global::tabs.content name="team">

                    {{-- Team grid: one card per direct report --}}
                    <h4 class="widgettitle title-light">{{ __('headlines.oneonone.your_team') }}</h4>

                    @if (count($teamDashboard) === 0)
                        <div class="tw-p-l tw-text-center">
                            <p>{{ __('text.oneonone.no_team_yet') }}</p>
                            <a href="{{ BASE_URL }}/oneonone/newSession" class="btn btn-primary">
                                <span class="fa fa-plus"></span>
                                {{ __('buttons.oneonone.schedule_first') }}
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach ($teamDashboard as $member)
                                <div class="col-md-4 col-sm-6 col-xs-12 tw-mb-m">
                                    <div class="tw-p-m tw-rounded tw-h-full"
                                         style="background:var(--secondary-background); border:1px solid var(--main-border-color);">

                                        <div class="tw-flex tw-items-center tw-gap-s tw-mb-s">
                                            <div class="tw-w-12 tw-h-12 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-lg tw-font-bold"
                                                 style="background:var(--accent1); color:#fff;">
                                                {{ strtoupper(substr((string) ($member['firstname'] ?? ''), 0, 1) . substr((string) ($member['lastname'] ?? ''), 0, 1)) }}
                                            </div>
                                            <div class="tw-flex-1 tw-min-w-0">
                                                <strong class="tw-truncate tw-block">{{ $member['firstname'] }} {{ $member['lastname'] }}</strong>
                                                @if (!empty($member['jobTitle']))
                                                    <small class="tw-text-xs" style="color:var(--grey);">{{ $member['jobTitle'] }}</small>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="tw-grid tw-grid-cols-2 tw-gap-s tw-mb-s tw-text-sm">
                                            <div>
                                                <strong>{{ $member['sessionCount'] }}</strong>
                                                <small style="color:var(--grey);">{{ __('text.oneonone.sessions') }}</small>
                                            </div>
                                            <div>
                                                <strong>{{ $member['completedCount'] }}</strong>
                                                <small style="color:var(--grey);">{{ __('text.oneonone.completed') }}</small>
                                            </div>
                                        </div>

                                        @if ($member['lastSession'])
                                            <div class="tw-mb-s tw-text-sm">
                                                <small style="color:var(--grey);">{{ __('text.oneonone.last_session') }}:</small><br>
                                                <a href="{{ BASE_URL }}/oneonone/showSession/{{ $member['lastSession']['id'] }}">
                                                    @if (!empty($member['lastSession']['meetingDate']))
                                                        {{ dtHelper()->parseDbDateTime($member['lastSession']['meetingDate'])->setToUserTimezone()->format(__('language.dateformat')) }}
                                                    @else
                                                        {{ __('text.oneonone.no_date') }}
                                                    @endif
                                                </a>
                                            </div>
                                        @endif

                                        @if ($member['nextSession'])
                                            <div class="tw-mb-s tw-text-sm" style="color:var(--accent2);">
                                                <span class="fa fa-calendar-check"></span>
                                                {{ __('text.oneonone.next_session') }}:
                                                <a href="{{ BASE_URL }}/oneonone/showSession/{{ $member['nextSession']['id'] }}">
                                                    @if (!empty($member['nextSession']['meetingDate']))
                                                        {{ dtHelper()->parseDbDateTime($member['nextSession']['meetingDate'])->setToUserTimezone()->format(__('language.dateformat')) }}
                                                    @else
                                                        {{ __('text.oneonone.no_date') }}
                                                    @endif
                                                </a>
                                            </div>
                                        @endif

                                        <div class="tw-flex tw-gap-s tw-mt-m">
                                            <a href="{{ BASE_URL }}/oneonone/newSession?employeeId={{ $member['employeeId'] }}"
                                               class="btn btn-primary btn-xs tw-flex-1">
                                                <span class="fa fa-plus"></span> {{ __('buttons.oneonone.schedule') }}
                                            </a>
                                            @if ($member['lastSession'])
                                                <a href="{{ BASE_URL }}/oneonone/showSession/{{ $member['lastSession']['id'] }}"
                                                   class="btn btn-secondary btn-xs tw-flex-1">
                                                    <span class="fa fa-eye"></span> {{ __('buttons.oneonone.view_last') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Recent team sessions table --}}
                    <h4 class="widgettitle title-light tw-mt-l">{{ __('headlines.oneonone.recent_sessions') }}</h4>

                    @if (count($teamSessions) === 0)
                        <p class="tw-text-sm">{{ __('text.oneonone.no_sessions_yet') }}</p>
                    @else
                        <div class="tw-overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('label.oneonone.employee') }}</th>
                                        <th>{{ __('label.oneonone.meeting_date') }}</th>
                                        <th>{{ __('label.oneonone.title') }}</th>
                                        <th>{{ __('label.status') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($teamSessions as $session)
                                        <tr>
                                            <td>{{ $session['employeeFirstname'] ?? '' }} {{ $session['employeeLastname'] ?? '' }}</td>
                                            <td>
                                                @if (!empty($session['meetingDate']))
                                                    {{ dtHelper()->parseDbDateTime($session['meetingDate'])->setToUserTimezone()->format(__('language.dateformat')) }}
                                                @else
                                                    <span style="color:var(--grey);">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $session['title'] ?? '—' }}</td>
                                            <td>
                                                @php $status = $session['status'] ?? 'scheduled'; @endphp
                                                <span class="label label-{{ $status === 'completed' ? 'success' : ($status === 'cancelled' ? 'default' : 'info') }}">
                                                    {{ __('oneonone.status.' . $status) }}
                                                </span>
                                            </td>
                                            <td class="tw-text-right">
                                                <a href="{{ BASE_URL }}/oneonone/showSession/{{ $session['id'] }}"
                                                   class="btn btn-secondary btn-xs">
                                                    {{ __('buttons.open') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </x-global::tabs.content>

            </x-slot:contents>
        </x-global::tabs>

    </div>
</div>

<script>
// Activate the default tab (jQuery UI tabs activates the first by default;
// override based on URL hash, ?tab= query, or the controller's choice).
jQuery(function($){
    var $tabs = $('#oneononeTabs');
    if (!$tabs.length) return;

    var indexByName = { 'my': 0, 'team': 1 };
    var pick = '{{ $defaultTab }}';

    // URL hash (#my / #team) wins
    if (window.location.hash) {
        var hash = window.location.hash.replace('#', '');
        if (indexByName[hash] !== undefined) pick = hash;
    } else {
        // Fall back to ?tab= query
        var m = window.location.search.match(/[?&]tab=([^&]+)/);
        if (m && indexByName[m[1]] !== undefined) pick = m[1];
    }

    var idx = indexByName[pick] || 0;
    if (idx > 0) {
        $tabs.tabs('option', 'active', idx);
    }
});
</script>

@endsection

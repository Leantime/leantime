@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-handshake'">
    <h1>{{ __('weeklyplanning.headlines.commitments') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <div class="tw-flex tw-justify-between tw-items-center tw-mb-m">
            <a href="{{ BASE_URL }}/weeklyplanning/showTeam" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> {{ __('weeklyplanning.buttons.back_to_team') }}
            </a>
            <div class="btn-group">
                <a href="{{ BASE_URL }}/weeklyplanning/showCommitments" class="btn btn-sm {{ $openOnly ? 'btn-primary' : 'btn-default' }}">
                    {{ __('weeklyplanning.buttons.open_only') }}
                </a>
                <a href="{{ BASE_URL }}/weeklyplanning/showCommitments?showAll=1" class="btn btn-sm {{ ! $openOnly ? 'btn-primary' : 'btn-default' }}">
                    {{ __('weeklyplanning.buttons.show_all') }}
                </a>
            </div>
        </div>

        @if(count($commitments) === 0)
            <x-global::emptyState
                icon="fa-handshake"
                headline="{{ __('weeklyplanning.text.no_commitments_yet') }}"
                description="{{ __('weeklyplanning.text.no_commitments_hint') }}"
            />
        @else
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('weeklyplanning.labels.task') }}</th>
                            <th>{{ __('weeklyplanning.labels.employee') }}</th>
                            <th>{{ __('weeklyplanning.labels.owner') }}</th>
                            <th>{{ __('weeklyplanning.labels.deadline') }}</th>
                            <th>{{ __('weeklyplanning.labels.status') }}</th>
                            <th>{{ __('weeklyplanning.labels.week') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commitments as $c)
                            <tr>
                                <td>{{ $c['task'] }}</td>
                                <td class="tw-text-sm">{{ $c['employeeFirstname'] }} {{ $c['employeeLastname'] }}</td>
                                <td class="tw-text-sm">{{ $c['ownerFirstname'] ?? '' }} {{ $c['ownerLastname'] ?? '' }}</td>
                                <td class="tw-text-sm">
                                    @if(!empty($c['deadline']))
                                        {{ \Carbon\Carbon::parse($c['deadline'])->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="label label-{{ $c['status'] === 'done' ? 'success' : 'default' }}">
                                        {{ __('weeklyplanning.commitment_status.'.$c['status']) }}
                                    </span>
                                </td>
                                <td class="tw-text-sm">{{ $c['weekLabel'] }} / {{ $c['month'] }}</td>
                                <td>
                                    <a href="{{ BASE_URL }}/weeklyplanning/showPlan/{{ $c['weeklyPlanId'] }}" class="btn btn-default btn-xs">
                                        {{ __('weeklyplanning.buttons.view_plan') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>

@endsection

@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-building'">
    <h1>{{ __('clientportal.headlines.dashboard') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        @if(empty($projects))
            <x-global::emptyState
                icon="fa-folder-open"
                headline="{{ __('clientportal.text.no_projects') }}"
                description="{{ __('clientportal.text.no_projects_hint') }}" />
        @else
            <div class="row tw-mb-m">
                <div class="col-md-12">
                    <h4 class="widgettitle title-light">
                        <i class="fa fa-folder-open"></i>
                        {{ __('clientportal.sections.your_projects') }}
                    </h4>
                </div>
            </div>

            <div class="row">
                @foreach($projects as $project)
                <div class="col-md-6 tw-mb-m">
                    <div class="tw-rounded tw-p-m"
                         style="border:1px solid var(--main-border-color); background:var(--secondary-background);">

                        {{-- Project name --}}
                        <div class="tw-flex tw-justify-between tw-items-start tw-mb-s">
                            <h4 class="widgettitle title-light tw-mb-0">
                                <i class="fa fa-folder tw-mr-xs" style="color:var(--accent1);"></i>
                                {{ $project['name'] }}
                            </h4>
                            <a href="{{ BASE_URL }}/clientportal/showProject/{{ $project['id'] }}"
                               class="btn btn-primary btn-sm">
                                {{ __('clientportal.buttons.view_project') }} <i class="fa fa-arrow-right tw-ml-xs"></i>
                            </a>
                        </div>

                        {{-- Progress bar --}}
                        <div class="tw-mb-s">
                            <div class="tw-flex tw-justify-between tw-items-center tw-mb-xs">
                                <small style="color:var(--grey);">{{ __('clientportal.labels.overall_progress') }}</small>
                                <strong class="tw-text-sm">{{ $project['percent'] }}%</strong>
                            </div>
                            <div style="height:10px; background:var(--primary-background); border-radius:5px; overflow:hidden;">
                                <div style="height:100%; width:{{ $project['percent'] }}%; background:var(--accent1); border-radius:5px; transition:width 0.4s ease;"></div>
                            </div>
                            <small style="color:var(--grey);" class="tw-mt-xs tw-block">
                                {{ $project['progress']['done'] }} / {{ $project['progress']['total'] }}
                                {{ __('clientportal.labels.tasks_done') }}
                            </small>
                        </div>

                        {{-- Milestone count --}}
                        <div class="tw-flex tw-gap-m tw-text-sm" style="color:var(--grey);">
                            <span>
                                <i class="fa fa-flag tw-mr-xs" style="color:var(--accent2);"></i>
                                {{ $project['milestoneDone'] }} / {{ $project['milestoneTotal'] }}
                                {{ __('clientportal.labels.milestones_done') }}
                            </span>
                            @if($project['nextMilestone'])
                                <span>
                                    <i class="fa fa-circle-dot tw-mr-xs" style="color:var(--accent1);"></i>
                                    {{ __('clientportal.labels.next') }}: {{ $project['nextMilestone']['headline'] }}
                                </span>
                            @endif
                        </div>

                    </div>
                </div>
                @endforeach
            </div>
        @endif

    </div>
</div>

@endsection

@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-folder-open'">
    <h1>{{ $project['name'] }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        {{-- Back link --}}
        <a href="{{ BASE_URL }}/clientportal/showDashboard" class="btn btn-default btn-sm tw-mb-m">
            <i class="fa fa-arrow-left"></i> {{ __('clientportal.buttons.back_to_projects') }}
        </a>

        <div class="row">

            {{-- LEFT COLUMN: Progress + Milestones --}}
            <div class="col-md-8">

                {{-- Progress --}}
                <div class="tw-mb-l tw-p-m tw-rounded"
                     style="border:1px solid var(--main-border-color); background:var(--secondary-background);">
                    <h4 class="widgettitle title-light">
                        <i class="fa fa-chart-bar"></i> {{ __('clientportal.sections.overall_progress') }}
                    </h4>

                    <div class="tw-flex tw-justify-between tw-items-center tw-mb-xs">
                        <span class="tw-text-sm" style="color:var(--grey);">
                            {{ $progress['done'] }} {{ __('clientportal.labels.of') }} {{ $progress['total'] }}
                            {{ __('clientportal.labels.tasks_completed') }}
                        </span>
                        <strong style="font-size:var(--font-size-xl); color:var(--accent1);">{{ $percent }}%</strong>
                    </div>

                    <div style="height:14px; background:var(--primary-background); border-radius:7px; overflow:hidden;">
                        <div style="height:100%; width:{{ $percent }}%; background:var(--accent1); border-radius:7px; transition:width 0.5s ease;"></div>
                    </div>
                </div>

                {{-- Milestones --}}
                <div class="tw-mb-l tw-p-m tw-rounded"
                     style="border:1px solid var(--main-border-color); background:var(--secondary-background);">
                    <h4 class="widgettitle title-light">
                        <i class="fa fa-flag"></i> {{ __('clientportal.sections.milestones') }}
                    </h4>

                    @if(empty($milestones))
                        <p class="tw-text-sm" style="color:var(--grey);">{{ __('clientportal.text.no_milestones') }}</p>
                    @else
                        <div class="tw-flex tw-flex-col tw-gap-s">
                            @foreach($milestones as $m)
                                @php $mStatus = (int)($m['status'] ?? 1); $done = $mStatus === 0; @endphp
                                <div class="tw-flex tw-items-center tw-gap-s tw-p-s tw-rounded"
                                     style="background:var(--primary-background);">
                                    <span style="font-size:var(--font-size-l); width:24px; text-align:center;">
                                        @if($done)
                                            <i class="fa fa-circle-check" style="color:var(--status-green);"></i>
                                        @elseif($mStatus > 0)
                                            <i class="fa fa-circle-half-stroke" style="color:var(--accent1);"></i>
                                        @else
                                            <i class="fa fa-circle" style="color:var(--grey);"></i>
                                        @endif
                                    </span>
                                    <div class="tw-flex-1">
                                        <span class="tw-text-sm tw-font-semibold
                                            {{ $done ? 'tw-line-through' : '' }}"
                                            style="{{ $done ? 'color:var(--grey);' : '' }}">
                                            {{ $m['headline'] }}
                                        </span>
                                        @if(!empty($m['editTo']) && $m['editTo'] !== '0000-00-00 00:00:00')
                                            <small class="tw-block" style="color:var(--grey);">
                                                {{ \Carbon\Carbon::parse($m['editTo'])->format('d M Y') }}
                                            </small>
                                        @endif
                                    </div>
                                    <span class="label label-{{ $done ? 'success' : ($mStatus > 0 ? 'primary' : 'default') }}">
                                        {{ $done ? __('clientportal.status.done') : ($mStatus > 0 ? __('clientportal.status.in_progress') : __('clientportal.status.pending')) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- RIGHT COLUMN: Team Contacts + Requests --}}
            <div class="col-md-4">

                {{-- Team Contacts --}}
                <div class="tw-mb-l tw-p-m tw-rounded"
                     style="border:1px solid var(--main-border-color); background:var(--secondary-background);">
                    <h4 class="widgettitle title-light">
                        <i class="fa fa-users"></i> {{ __('clientportal.sections.team_contacts') }}
                    </h4>

                    @if(empty($contacts))
                        <p class="tw-text-sm" style="color:var(--grey);">{{ __('clientportal.text.no_contacts') }}</p>
                    @else
                        @foreach($contacts as $contact)
                            <div class="tw-flex tw-items-center tw-gap-s tw-mb-s">
                                <div style="width:36px; height:36px; border-radius:50%; background:var(--accent1); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:bold; flex-shrink:0;">
                                    {{ strtoupper(substr($contact['firstname'], 0, 1)) }}
                                </div>
                                <div>
                                    <strong class="tw-block tw-text-sm">
                                        {{ $contact['firstname'] }} {{ $contact['lastname'] }}
                                    </strong>
                                    <small style="color:var(--grey);">
                                        {{ $contact['role'] === 'teamlead' ? __('clientportal.labels.team_lead') : __('clientportal.labels.company_manager') }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Requests --}}
                <div class="tw-mb-l tw-p-m tw-rounded"
                     style="border:1px solid var(--main-border-color); background:var(--secondary-background);">
                    <div class="tw-flex tw-justify-between tw-items-center tw-mb-s">
                        <h4 class="widgettitle title-light tw-mb-0">
                            <i class="fa fa-paper-plane"></i> {{ __('clientportal.sections.your_requests') }}
                        </h4>
                        @if(session('userdata.role') === 'commenter')
                            <button class="btn btn-primary btn-xs"
                                    onclick="htmx.ajax('GET', '{{ BASE_URL }}/hx/clientportal/requests/form?projectId={{ $projectId }}', {target:'#request-form-container', swap:'innerHTML'})">
                                <i class="fa fa-plus"></i> {{ __('clientportal.buttons.new_request') }}
                            </button>
                        @endif
                    </div>

                    {{-- Form area --}}
                    <div id="request-form-container"></div>

                    {{-- Request list (HTMX-refreshable) --}}
                    <div id="request-list-wrapper"
                         hx-get="{{ BASE_URL }}/hx/clientportal/requests/list?projectId={{ $projectId }}"
                         hx-trigger="load, clientportal_request_updated from:body"
                         hx-swap="innerHTML">
                        <div class="htmx-indicator">
                            <div class="indeterminate"></div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection

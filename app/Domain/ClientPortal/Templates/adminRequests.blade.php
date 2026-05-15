@extends($layout)

@section('content')

@php
    $statusBadgeMap = [
        'open'              => ['warning', __('clientportal.status.open')],
        'reviewed'          => ['info',    __('clientportal.status.reviewed')],
        'accepted'          => ['success', __('clientportal.status.accepted')],
        'rejected'          => ['important', __('clientportal.status.rejected')],
        'changes_requested' => ['warning', __('clientportal.status.changes_requested')],
    ];
@endphp

<x-global::pageheader :icon="'fa fa-inbox'">
    <h1>{{ __('clientportal.headlines.client_requests') }}
        @if($totalOpen > 0)
            <span class="label label-warning tw-ml-s">{{ $totalOpen }} {{ __('clientportal.labels.open') }}</span>
        @endif
    </h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        @if(empty($grouped))
            <x-global::emptyState
                icon="fa-inbox"
                headline="{{ __('clientportal.text.no_requests_admin') }}"
                description="{{ __('clientportal.text.no_requests_admin_hint') }}" />
        @else
            @foreach($grouped as $projectName => $requests)
                <div class="tw-mb-l">
                    <h4 class="widgettitle title-light tw-mb-s">
                        <i class="fa fa-folder-open tw-mr-xs" style="color:var(--accent1);"></i>
                        {{ $projectName }}
                        <span class="tw-text-sm tw-font-normal" style="color:var(--grey);">
                            ({{ count($requests) }} {{ __('clientportal.labels.requests') }})
                        </span>
                    </h4>

                    <div class="tw-flex tw-flex-col tw-gap-s">
                        @foreach($requests as $req)
                            @php
                                $reviewAction = $req['clientReviewAction'] ?? null;
                                $effectiveStatus = ($req['status'] === 'open' && $reviewAction)
                                    ? $reviewAction
                                    : $req['status'];
                                [$badgeClass, $badgeLabel] = $statusBadgeMap[$effectiveStatus] ?? $statusBadgeMap['open'];
                                $responses = $req['responses'] ?? [];
                            @endphp

                            <div class="tw-p-m tw-rounded"
                                 style="border:1px solid var(--main-border-color); background:var(--secondary-background);"
                                 id="req-card-{{ $req['id'] }}">

                                {{-- Header row --}}
                                <div class="tw-flex tw-justify-between tw-items-start tw-mb-s">
                                    <div>
                                        <span class="tw-font-semibold">{{ $req['title'] }}</span>
                                        <small class="tw-block tw-mt-xs" style="color:var(--grey);">
                                            <i class="fa fa-user tw-mr-xs"></i>
                                            {{ $req['firstname'] }} {{ $req['lastname'] }}
                                            &nbsp;&bull;&nbsp;
                                            <i class="fa fa-clock tw-mr-xs"></i>
                                            {{ \Carbon\Carbon::parse($req['createdAt'])->format('d M Y, H:i') }}
                                        </small>
                                    </div>
                                    <span class="label label-{{ $badgeClass }}">{{ $badgeLabel }}</span>
                                </div>

                                {{-- Description --}}
                                @if(!empty($req['description']))
                                    <p class="tw-text-sm tw-mb-s" style="color:var(--primary-font-color); white-space:pre-wrap;">{{ $req['description'] }}</p>
                                @endif

                                {{-- Client attachment --}}
                                @if(!empty($req['filePath']))
                                    <div class="tw-mb-s">
                                        <a href="{{ BASE_URL }}/{{ $req['filePath'] }}" target="_blank" class="btn btn-xs btn-default">
                                            <i class="fa fa-paperclip tw-mr-xs"></i>{{ __('clientportal.labels.attached_file') }}
                                        </a>
                                    </div>
                                @endif

                                {{-- Response thread (all responses chronologically) --}}
                                @foreach($responses as $resp)
                                    <div class="tw-p-s tw-rounded tw-mb-s"
                                         style="border-left:3px solid var(--accent1); background:var(--layered-background);">
                                        <small class="tw-font-semibold" style="color:var(--accent1);">
                                            <i class="fa fa-reply tw-mr-xs"></i>
                                            {{ __('clientportal.labels.response_from') }}
                                            {{ $resp['firstname'] }} {{ $resp['lastname'] }}
                                            &nbsp;&bull;&nbsp;
                                            {{ \Carbon\Carbon::parse($resp['createdAt'])->format('d M Y, H:i') }}
                                        </small>
                                        @if(!empty($resp['notes']))
                                            <p class="tw-text-sm tw-mt-xs tw-mb-xs" style="white-space:pre-wrap;">{{ $resp['notes'] }}</p>
                                        @endif
                                        <div class="tw-flex tw-gap-s">
                                            @if(!empty($resp['driveLink']))
                                                <a href="{{ $resp['driveLink'] }}" target="_blank" class="btn btn-xs btn-default">
                                                    <i class="fa fa-link tw-mr-xs"></i>{{ __('clientportal.labels.view_document') }}
                                                </a>
                                            @endif
                                            @if(!empty($resp['documentPath']))
                                                <a href="{{ BASE_URL }}/{{ $resp['documentPath'] }}" target="_blank" class="btn btn-xs btn-default">
                                                    <i class="fa fa-file tw-mr-xs"></i>{{ __('clientportal.labels.download_file') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Client decision (if made) — visible to TL/CM so they can act on it --}}
                                @if($reviewAction)
                                    <div class="tw-p-s tw-rounded tw-mb-s"
                                         style="border-left:3px solid var(--accent2); background:var(--layered-background);">
                                        <small class="tw-font-semibold" style="color:var(--accent2);">
                                            <i class="fa fa-stamp tw-mr-xs"></i>
                                            {{ __('clientportal.labels.client_decision') }}: {{ $badgeLabel }}
                                            @if(!empty($req['clientReviewedAt']))
                                                &nbsp;&bull;&nbsp;
                                                {{ \Carbon\Carbon::parse($req['clientReviewedAt'])->format('d M Y, H:i') }}
                                            @endif
                                        </small>
                                        @if(!empty($req['clientReviewReason']))
                                            <p class="tw-text-sm tw-mt-xs tw-mb-0" style="white-space:pre-wrap;">{{ $req['clientReviewReason'] }}</p>
                                        @endif
                                    </div>
                                @endif

                                {{-- Respond button: show whenever the request is in 'open' state --}}
                                @if($req['status'] === 'open')
                                    <div>
                                        <button class="btn btn-primary btn-sm"
                                                hx-get="{{ BASE_URL }}/hx/clientportal/requests/responseForm?id={{ $req['id'] }}&fromAdmin=1"
                                                hx-target="#response-area-{{ $req['id'] }}"
                                                hx-swap="innerHTML">
                                            <i class="fa fa-reply tw-mr-xs"></i>{{ __('clientportal.buttons.respond') }}
                                        </button>
                                        <div id="response-area-{{ $req['id'] }}"></div>
                                    </div>
                                @elseif(in_array($req['status'], ['accepted', 'rejected'], true))
                                    <p class="tw-text-xs tw-mb-0"
                                       style="color:{{ $req['status'] === 'accepted' ? 'var(--status-green)' : 'var(--accent2)' }};">
                                        <i class="fa fa-lock tw-mr-xs"></i>{{ __($req['status'] === 'accepted' ? 'clientportal.text.request_closed' : 'clientportal.text.request_rejected_closed') }}
                                    </p>
                                @endif

                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

    </div>
</div>

@endsection

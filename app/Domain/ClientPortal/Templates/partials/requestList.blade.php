@php
    // Resolve the visible status: a re-opened request still has status='open'
    // but a clientReviewAction set ('rejected' or 'changes_requested').
    $statusBadgeMap = [
        'open'              => ['default', __('clientportal.status.open')],
        'reviewed'          => ['info',    __('clientportal.status.reviewed')],
        'accepted'          => ['success', __('clientportal.status.accepted')],
        'rejected'          => ['important', __('clientportal.status.rejected')],
        'changes_requested' => ['warning', __('clientportal.status.changes_requested')],
    ];
    $role = session('userdata.role');
@endphp

@if(empty($requests))
    <p class="tw-text-sm tw-mt-s" style="color:var(--grey);">{{ __('clientportal.text.no_requests') }}</p>
@else
    <div class="tw-flex tw-flex-col tw-gap-s tw-mt-s">
        @foreach($requests as $req)
            @php
                $reviewAction = $req['clientReviewAction'] ?? null;
                $effectiveStatus = ($req['status'] === 'open' && $reviewAction)
                    ? $reviewAction
                    : $req['status'];
                [$badgeClass, $badgeLabel] = $statusBadgeMap[$effectiveStatus] ?? $statusBadgeMap['open'];
            @endphp

            <div class="tw-p-s tw-rounded"
                 style="border:1px solid var(--main-border-color); background:var(--primary-background);">

                {{-- Request header --}}
                <div class="tw-flex tw-justify-between tw-items-start tw-mb-xs">
                    <span class="tw-text-sm tw-font-semibold">{{ $req['title'] }}</span>
                    <span class="label label-{{ $badgeClass }}">{{ $badgeLabel }}</span>
                </div>

                {{-- Description --}}
                @if(!empty($req['description']))
                    <p class="tw-text-xs tw-mb-xs" style="color:var(--grey); white-space:pre-wrap;">{{ $req['description'] }}</p>
                @endif

                {{-- Attached file (from client) --}}
                @if(!empty($req['filePath']))
                    <div class="tw-mb-xs">
                        <a href="{{ BASE_URL }}/{{ $req['filePath'] }}" target="_blank" class="tw-text-xs">
                            <i class="fa fa-paperclip tw-mr-xs"></i>{{ __('clientportal.labels.attached_file') }}
                        </a>
                    </div>
                @endif

                <small style="color:var(--grey);">
                    {{ \Carbon\Carbon::parse($req['createdAt'])->format('d M Y') }}
                </small>

                {{-- Latest TL/CM response (if any) --}}
                @if(!empty($req['response']))
                    @php $resp = $req['response']; @endphp
                    <div class="tw-mt-s tw-p-xs tw-rounded"
                         style="border-left:3px solid var(--accent1); background:var(--layered-background);">
                        <small class="tw-font-semibold" style="color:var(--accent1);">
                            <i class="fa fa-reply tw-mr-xs"></i>
                            {{ __('clientportal.labels.response_from') }}
                            {{ $resp['firstname'] }} {{ $resp['lastname'] }}
                        </small>

                        @if(!empty($resp['notes']))
                            <p class="tw-text-xs tw-mt-xs tw-mb-xs" style="white-space:pre-wrap;">{{ $resp['notes'] }}</p>
                        @endif

                        @if(!empty($resp['driveLink']))
                            <a href="{{ $resp['driveLink'] }}" target="_blank" class="tw-text-xs btn btn-link" style="padding:0;">
                                <i class="fa fa-link tw-mr-xs"></i>{{ __('clientportal.labels.view_document') }}
                            </a>
                        @endif

                        @if(!empty($resp['documentPath']))
                            <a href="{{ BASE_URL }}/{{ $resp['documentPath'] }}" target="_blank" class="tw-text-xs btn btn-link" style="padding:0; margin-left:8px;">
                                <i class="fa fa-file tw-mr-xs"></i>{{ __('clientportal.labels.download_file') }}
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Client review decision (if already made) --}}
                @if($reviewAction)
                    <div class="tw-mt-s tw-p-xs tw-rounded"
                         style="border-left:3px solid var(--accent2); background:var(--layered-background);">
                        <small class="tw-font-semibold" style="color:var(--accent2);">
                            <i class="fa fa-stamp tw-mr-xs"></i>
                            {{ __('clientportal.labels.client_decision') }}: {{ $badgeLabel }}
                        </small>
                        @if(!empty($req['clientReviewReason']))
                            <p class="tw-text-xs tw-mt-xs tw-mb-0" style="white-space:pre-wrap;">{{ $req['clientReviewReason'] }}</p>
                        @endif
                    </div>
                @endif

                {{-- Client review actions: shown ONLY to the original submitter
                     (other clients in the same org can see the request but cannot decide on it). --}}
                @if($req['status'] === 'reviewed' && $role === 'commenter' && (int) ($req['clientUserId'] ?? 0) === (int) session('userdata.id'))
                    <div class="tw-mt-s">
                        <p class="tw-text-xs tw-mb-xs" style="color:var(--grey);">
                            {{ __('clientportal.text.awaiting_review') }}
                        </p>

                        <div class="tw-flex tw-gap-s tw-flex-wrap">
                            {{-- Accept (no reason needed) --}}
                            <form hx-post="{{ BASE_URL }}/hx/clientportal/requests/submitReview"
                                  hx-target="#request-list-wrapper" hx-swap="innerHTML"
                                  style="display:inline;">
                                <input type="hidden" name="requestId" value="{{ $req['id'] }}">
                                <input type="hidden" name="projectId" value="{{ $projectId }}">
                                <input type="hidden" name="action" value="accepted">
                                <button type="submit" class="btn btn-xs btn-success">
                                    <i class="fa fa-check tw-mr-xs"></i>{{ __('clientportal.buttons.accept') }}
                                </button>
                            </form>

                            {{-- Request Changes --}}
                            <button type="button" class="btn btn-xs btn-warning"
                                    onclick="
                                        document.getElementById('reviewform-{{ $req['id'] }}-changes_requested').style.display='block';
                                        document.getElementById('reviewform-{{ $req['id'] }}-rejected').style.display='none';
                                    ">
                                <i class="fa fa-pen-to-square tw-mr-xs"></i>{{ __('clientportal.buttons.request_changes') }}
                            </button>

                            {{-- Reject --}}
                            <button type="button" class="btn btn-xs btn-danger"
                                    onclick="
                                        document.getElementById('reviewform-{{ $req['id'] }}-rejected').style.display='block';
                                        document.getElementById('reviewform-{{ $req['id'] }}-changes_requested').style.display='none';
                                    ">
                                <i class="fa fa-xmark tw-mr-xs"></i>{{ __('clientportal.buttons.reject') }}
                            </button>
                        </div>

                        {{-- Reason form for changes_requested + rejected --}}
                        @foreach(['changes_requested' => 'changes_reason', 'rejected' => 'rejection_reason'] as $actionKey => $placeholderKey)
                            <div id="reviewform-{{ $req['id'] }}-{{ $actionKey }}"
                                 class="tw-mt-s tw-p-xs tw-rounded"
                                 style="display:none; border:1px solid var(--main-border-color); background:var(--layered-background);">
                                <form hx-post="{{ BASE_URL }}/hx/clientportal/requests/submitReview"
                                      hx-target="#request-list-wrapper" hx-swap="innerHTML">
                                    <input type="hidden" name="requestId" value="{{ $req['id'] }}">
                                    <input type="hidden" name="projectId" value="{{ $projectId }}">
                                    <input type="hidden" name="action" value="{{ $actionKey }}">

                                    <label class="tw-text-xs tw-font-semibold">
                                        {{ __('clientportal.labels.review_reason') }}
                                        <span style="color:var(--accent2);">*</span>
                                    </label>
                                    <textarea name="reason" class="form-control" rows="2" required
                                              placeholder="{{ __('clientportal.placeholders.' . $placeholderKey) }}"></textarea>

                                    <div class="tw-flex tw-gap-s tw-mt-xs">
                                        <button type="submit" class="btn btn-xs btn-primary">
                                            {{ __('clientportal.buttons.submit_review') }}
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default"
                                                onclick="this.closest('[id^=reviewform-]').style.display='none';">
                                            {{ __('buttons.cancel') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- TL/CM Respond button: show when request is open (initial or re-opened) --}}
                @if($req['status'] === 'open' && in_array($role, ['teamlead', 'manager', 'admin', 'owner']))
                    <div class="tw-mt-s">
                        <button class="btn btn-xs btn-default"
                                onclick="htmx.ajax('GET', '{{ BASE_URL }}/hx/clientportal/requests/responseForm?id={{ $req['id'] }}', {target:'#response-form-{{ $req['id'] }}', swap:'innerHTML'})">
                            <i class="fa fa-reply"></i> {{ __('clientportal.buttons.respond') }}
                        </button>
                        <div id="response-form-{{ $req['id'] }}"></div>
                    </div>
                @endif

                {{-- Closed (accepted or rejected) --}}
                @if(in_array($req['status'], ['accepted', 'rejected'], true))
                    <p class="tw-text-xs tw-mt-s tw-mb-0"
                       style="color:{{ $req['status'] === 'accepted' ? 'var(--status-green)' : 'var(--accent2)' }};">
                        <i class="fa fa-lock tw-mr-xs"></i>{{ __($req['status'] === 'accepted' ? 'clientportal.text.request_closed' : 'clientportal.text.request_rejected_closed') }}
                    </p>
                @endif

            </div>
        @endforeach
    </div>
@endif

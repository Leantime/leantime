@extends($layout)

@section('content')

@php
    $employeeName = trim(($session['employeeFirstname'] ?? '') . ' ' . ($session['employeeLastname'] ?? ''));
    $managerName = trim(($session['managerFirstname'] ?? '') . ' ' . ($session['managerLastname'] ?? ''));
    $isManager = (int)($session['managerId'] ?? 0) === (int)session('userdata.id');
@endphp

<x-global::pageheader :icon="'fa fa-handshake'">
    <h5>
        <a href="{{ BASE_URL }}/oneonone/{{ $isManager ? 'showTeam' : 'showMy' }}">
            <span class="fa fa-arrow-left"></span> {{ __('buttons.back') }}
        </a>
    </h5>
    <h1>
        {{ $session['title'] ?: sprintf(__('headlines.oneonone.session_with'), $employeeName) }}
        <small style="color:var(--grey); font-size:14px;">
            @if (!empty($session['meetingDate']))
                &mdash; {{ dtHelper()->parseDbDateTime($session['meetingDate'])->setToUserTimezone()->format(__('language.dateformat')) }}
            @endif
        </small>
    </h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        {{-- Session header / meta --}}
        <form method="post" action="{{ BASE_URL }}/oneonone/showSession/{{ $session['id'] }}" id="sessionMetaForm">
            @if(isset($csrf_token))
                <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
            @endif

            <div class="row tw-mb-m">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <label class="control-label">{{ __('label.oneonone.employee') }}</label>
                    <div class="tw-py-s">
                        <strong>{{ $employeeName ?: '—' }}</strong>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <label class="control-label">{{ __('label.oneonone.manager') }}</label>
                    <div class="tw-py-s">
                        <strong>{{ $managerName ?: '—' }}</strong>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <label class="control-label" for="meetingDate">{{ __('label.oneonone.meeting_date') }}</label>
                    {{-- datetime-local has no timezone — show DB UTC converted to user TZ so it round-trips with scheduleSession()'s parseUserDateTime which assumes user TZ. --}}
                    <input type="datetime-local" id="meetingDate" name="meetingDate" class="form-control"
                        value="{{ !empty($session['meetingDate']) ? dtHelper()->parseDbDateTime($session['meetingDate'])->setToUserTimezone()->format('Y-m-d\\TH:i') : '' }}"
                        @if(!$canEdit) disabled @endif>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <label class="control-label" for="status">{{ __('label.status') }}</label>
                    <select id="status" name="status" class="form-control" @if(!$canEdit) disabled @endif>
                        @foreach ($sessionStatuses as $value => $key)
                            <option value="{{ $value }}" @if($session['status'] === $value) selected @endif>
                                {{ __($key) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row tw-mb-m">
                <div class="col-md-6 col-sm-12 col-xs-12">
                    <label class="control-label" for="title">{{ __('label.oneonone.title') }}</label>
                    <input type="text" id="title" name="title" class="form-control"
                           value="{{ $session['title'] ?? '' }}"
                           placeholder="{{ __('placeholder.oneonone.title') }}"
                           @if(!$canEdit) disabled @endif>
                </div>
                <div class="col-md-6 col-sm-12 col-xs-12">
                    <label class="control-label" for="mood">{{ __('label.oneonone.mood') }}</label>
                    <select id="mood" name="mood" class="form-control" @if(!$canEdit) disabled @endif>
                        <option value="">—</option>
                        @foreach ($moodValues as $value => $key)
                            <option value="{{ $value }}" @if(($session['mood'] ?? '') === $value) selected @endif>
                                {{ __($key) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row tw-mb-m">
                <div class="col-md-12">
                    <label class="control-label" for="summary">{{ __('label.oneonone.summary') }}</label>
                    <textarea id="summary" name="summary" class="form-control" rows="3"
                              placeholder="{{ __('placeholder.oneonone.summary') }}"
                              @if(!$canEdit) disabled @endif>{{ $session['summary'] ?? '' }}</textarea>
                    <small style="color:var(--grey);">{{ __('text.oneonone.summary_help') }}</small>
                </div>
            </div>

            @if ($isManager)
                <div class="row tw-mb-m">
                    <div class="col-md-12">
                        <label class="control-label" for="notes">
                            <span class="fa fa-lock"></span> {{ __('label.oneonone.private_notes') }}
                        </label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"
                                  placeholder="{{ __('placeholder.oneonone.private_notes') }}">{{ $session['notes'] ?? '' }}</textarea>
                        <small style="color:var(--grey);">{{ __('text.oneonone.private_notes_help') }}</small>
                    </div>
                </div>
            @endif

            @if ($canEdit)
                <div class="tw-flex tw-gap-s tw-mb-l">
                    <button type="submit" class="btn btn-primary">
                        <span class="fa fa-save"></span> {{ __('buttons.save') }}
                    </button>
                    @if ($isManager)
                        <a href="{{ BASE_URL }}/oneonone/delSession/{{ $session['id'] }}" class="btn btn-secondary">
                            <span class="fa fa-trash"></span> {{ __('buttons.delete') }}
                        </a>
                    @endif
                </div>
            @endif
        </form>

        {{-- Items: lazily reloaded via HTMX after every mutation --}}
        <div id="oneononeItemList"
             hx-get="{{ BASE_URL }}/hx/oneonone/sessionItems/list?sessionId={{ $session['id'] }}"
             hx-trigger="load, oneonone_item_changed from:body"
             hx-swap="innerHTML">
            @include('oneonone::partials.itemList', [
                'session' => $session,
                'itemsByType' => $itemsByType,
                'itemTypes' => $itemTypes,
                'canEdit' => $canEdit,
                'focusType' => null,
            ])
        </div>

    </div>
</div>

@endsection

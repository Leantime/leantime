@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-handshake'">
    <h1>{{ __('headlines.oneonone.schedule_session') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-8 col-md-offset-2 col-sm-12">

                <form method="post" action="{{ BASE_URL }}/oneonone/newSession">
                    @if(isset($csrf_token))
                        <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
                    @endif

                    <div class="form-group">
                        <label class="control-label" for="employeeId">
                            {{ __('label.oneonone.employee') }} <span style="color:var(--red);">*</span>
                        </label>
                        <select id="employeeId" name="employeeId" class="form-control" required>
                            <option value="">{{ __('placeholder.oneonone.select_employee') }}</option>
                            @foreach ($allUsers as $user)
                                <option value="{{ $user['id'] }}"
                                    @if((int)($values['employeeId'] ?? 0) === (int)$user['id']) selected @endif>
                                    {{ $user['firstname'] }} {{ $user['lastname'] }}
                                    @if (!empty($user['jobTitle'])) ({{ $user['jobTitle'] }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="meetingDate">
                            {{ __('label.oneonone.meeting_date') }} <span style="color:var(--red);">*</span>
                        </label>
                        <input type="datetime-local" id="meetingDate" name="meetingDate" class="form-control"
                               value="{{ $values['meetingDate'] ?? '' }}" required>
                        <small style="color:var(--grey);">{{ __('text.oneonone.meeting_date_help') }}</small>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="title">{{ __('label.oneonone.title') }}</label>
                        <input type="text" id="title" name="title" class="form-control"
                               value="{{ $values['title'] ?? '' }}"
                               placeholder="{{ __('placeholder.oneonone.title') }}">
                    </div>

                    <div class="tw-flex tw-gap-s tw-mt-m">
                        <button type="submit" class="btn btn-primary">
                            <span class="fa fa-calendar-plus"></span>
                            {{ __('buttons.oneonone.schedule_session') }}
                        </button>
                        <a href="{{ BASE_URL }}/oneonone/showTeam" class="btn btn-secondary">
                            {{ __('buttons.cancel') }}
                        </a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection

@php( $percentDone = format($project['progress']['percent'])->decimal())

    <div class="row">
        <div class="col-md-7">
            {{ __("subtitles.project_progress") }}
        </div>
        <div class="col-md-5" style="text-align:right">
            {{ sprintf(__("text.percent_complete"), $percentDone) }}
        </div>
    </div>
    <div class="progress">
        <div class="progress-bar progress-bar-success"
             role="progressbar"
             aria-valuenow="{{ $percentDone }}"
             aria-valuemin="0"
             aria-valuemax="100"
             style="width: {{ $percentDone }}%">
            <span class="sr-only">{{ sprintf(__("text.percent_complete"), $percentDone) }}</span>
        </div>
    </div>
    <div>
        @if ($project['status'] !== null && $project['status'] != '')
            <span class="label label-{{ $project['status'] }}">
                            {{ __("label.project_status_" . $project['status']) }}
                        </span><br />
        @else
            <span class="label label-grey">{{ __("label.no_status") }}</span><br />
        @endif
    </div>
    <br />
    <div>
        <div class="team">
            @foreach ($project['team'] as $member)
                <div class="commentImage" style="margin-right:-10px;" data-tippy-content="{{ $member['firstname'] }} {{ $member['lastname'] }}">
                    <img
                        style=""
                        src="{{  BASE_URL }}/api/users?profileImage={{ $member['id'] }}&v={{ format($member['modified'])->timestamp() }}" data-tippy-content="{{ $member['firstname'] . ' ' . $member['lastname'] }}" />
                </div>
            @endforeach
        </div>
        <div class="clearall"></div>
    </div>

<script>
    tippy('[data-tippy-content]');
</script>

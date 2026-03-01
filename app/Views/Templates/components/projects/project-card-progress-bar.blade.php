@props([
    'project' => [],
])

@php( $percentDone = format($project['progress']['percent'])->decimal())

    <x-global::progress :value="$percentDone" label="{{ __('subtitles.project_progress') }}" />

    <div class="projectBox-statusRow">
        @if ($project['status'] !== null && $project['status'] != '')
            <span class="label label-{{ $project['status'] }}">
                {{ __("label.project_status_" . $project['status']) }}
            </span>
        @else
            <span class="label label-grey">{{ __("label.no_status") }}</span>
        @endif
    </div>
    <div>
        <div class="team">
            @foreach ($project['team'] as $member)
                <div class="commentImage" data-tippy-content="{{ $member['firstname'] }} {{ $member['lastname'] }}">
                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $member['id'] }}&v={{ format($member['modified'])->timestamp() }}" data-tippy-content="{{ $member['firstname'] . ' ' . $member['lastname'] }}" />
                </div>
            @endforeach
        </div>
        <div class="clearall"></div>
    </div>

<script>
    tippy('[data-tippy-content]');
</script>

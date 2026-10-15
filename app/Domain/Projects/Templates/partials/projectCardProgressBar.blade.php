@php( $percentDone = format($project['progress']['percent'])->decimal())

    <div class="row">
        <div class="col-md-7">
            {{ __("subtitles.project_progress") }}
        </div>
        <div class="col-md-5" style="text-align:right">
            {{ sprintf(__("text.percent_complete"), $percentDone) }}
        </div>
    </div>
    <x-global::feedback.progress :value="$percentDone" />
    <div class="row">
        <div class="col-md-12">
            @php($statusTone = ['green' => 'success', 'yellow' => 'warning', 'red' => 'danger'][$project['status'] ?? ''] ?? null)
            @if ($statusTone !== null)
                <x-global::elements.badge :tone="$statusTone" dot>{{ __("label.project_status_" . $project['status']) }}</x-global::elements.badge><br />
            @else
                <x-global::elements.badge tone="muted">{{ __("label.no_status") }}</x-global::elements.badge><br />
            @endif
        </div>
    </div>
    <br />
    <div class="row">
        <div class="col-md-12">
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
    </div>

<script>
    // Idempotent init (see milestoneCard) — avoids re-instancing all tooltips
    // every time a project progress bar renders.
    window.leantime?.initTooltips?.();
</script>

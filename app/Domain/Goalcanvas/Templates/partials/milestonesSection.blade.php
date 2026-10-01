{{--
    Goal editor — linked-milestones MANAGEMENT list (summary + one row per
    milestone). Progress bars deliberately live on the Progress tab, not here
    (review 2026-08-03: chips carrying percent duplicated the Progress view) —
    this list is for linking/unlinking, RA line-item style.

    Lives in its own partial so the row-remove hx-post can re-render the WHOLE
    section (hx-target="#goalMsSection" outerHTML): the summary count stays
    correct with the removal.

    Expects: $id, $goalMilestones, $milestoneSummary, $milestones (project
    milestone options — drives the "link existing" button visibility).
--}}
<div id="goalMsSection">
    <div class="gv-ms-head">
        @if (($milestoneSummary['total'] ?? 0) > 0)
            <span class="gv-ms-summary"><b>{{ $milestoneSummary['total'] }}</b> {{ $milestoneSummary['total'] == 1 ? __("goalcanvas.summary_milestone_one") : __("goalcanvas.summary_milestones") }}
                @if ($milestoneSummary['inProgress'] > 0)&middot; {{ $milestoneSummary['inProgress'] }} {{ __("goalcanvas.summary_in_progress") }} @endif
                @if ($milestoneSummary['notStarted'] > 0)&middot; {{ $milestoneSummary['notStarted'] }} {{ __("goalcanvas.summary_not_started") }} @endif
                @if ($milestoneSummary['done'] > 0)&middot; {{ $milestoneSummary['done'] }} {{ __("goalcanvas.summary_done") }} @endif
            </span>
        @endif
        <span class="gv-ms-actions">
            @if ($login::userIsAtLeast($roles::$editor))
                <button type="button" class="gv-ms-act helperTooltip" onclick="leantime.goalCanvasController.toggleMilestoneSelectors('new');" data-tippy-content="{{ __('goalcanvas.ms_new') }}" title="{{ __('goalcanvas.ms_new') }}" aria-label="{{ __('goalcanvas.ms_new') }}"><i class="fa fa-plus" aria-hidden="true"></i></button>
                @if (count($milestones) > 0)
                    <button type="button" class="gv-ms-act helperTooltip" onclick="leantime.goalCanvasController.toggleMilestoneSelectors('existing');" data-tippy-content="{{ __('goalcanvas.ms_link') }}" title="{{ __('goalcanvas.ms_link') }}" aria-label="{{ __('goalcanvas.ms_link') }}"><i class="fa fa-link" aria-hidden="true"></i></button>
                @endif
            @endif
            <i class="fa fa-question-circle-o helperTooltip" aria-hidden="true" data-tippy-content="{{ __("tooltip.link_milestones_tooltip") }}"></i>
        </span>
    </div>

    @if (count($goalMilestones) > 0)
        <div class="gv-ms-list">
            @foreach ($goalMilestones as $ms)
                @php
                    $msDue = trim((string) ($ms['editTo'] ?? ''));
                    $msDue = ($msDue === '' || str_starts_with($msDue, '0000-00-00')) ? null : $msDue;
                @endphp
                {{-- Management row only — status signals (dots/bars) live on
                     the Progress tab (review 2026-08-04). --}}
                <div class="gv-ms-item">
                    <a class="gv-ms-name" href="#/tickets/editMilestone/{{ (int) $ms['id'] }}" title="{{ __('links.edit_milestone') }}: {{ $ms['headline'] }}">{{ $ms['headline'] }}</a>
                    @if ($msDue !== null)
                        <span class="gv-ms-due">{{ __('label.due') }} {{ format($msDue)->date() }}</span>
                    @endif
                    @if ($login::userIsAtLeast($roles::$editor))
                        <button type="button"
                                hx-post="{{ BASE_URL }}/goalcanvas/editCanvasItem/{{ $id }}"
                                hx-vals='{"removeMilestone": {{ (int) $ms['id'] }}}'
                                hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                hx-target="#goalMsSection"
                                hx-swap="outerHTML"
                                class="delete gv-ms-remove"
                                aria-label="{{ __("links.remove") }}: {{ $ms['headline'] }}" title="{{ __("links.remove") }}"><i class="fa fa-close" aria-hidden="true"></i></button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

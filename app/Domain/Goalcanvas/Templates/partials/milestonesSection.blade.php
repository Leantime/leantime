{{--
    Goal editor — the linked-milestones list. ONE list: summary, add/link
    actions, and a row per milestone carrying both the progress signal (status
    dot, bar, percent) and the management affordances (due date, unlink).

    This used to be two lists. A read-only bar list lived on the Progress tab and
    a management list lived on a separate Milestones tab, each holding half the
    information about the same milestones — so the same names appeared twice and
    neither view was complete. Merged (2026-09-07).

    Lives in its own partial so the row-remove hx-post can re-render the WHOLE
    section (hx-target="#goalMsSection" outerHTML): the summary count stays
    correct with the removal.

    Expects: $id, $goalMilestones, $milestoneSummary, $milestones (project
    milestone options — drives the "link existing" button visibility).
--}}
<div id="goalMsSection">
    <div class="gv-ms-head">
        <h4 class="widgettitle title-light gv-ms-bars-head"><span class="fa fa-flag-checkered" aria-hidden="true"></span> {{ __('headlines.milestones') }}</h4>

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
                    $msPct = (int) ($ms['percentDone'] ?? 0);
                @endphp
                <div class="gv-ms-item">
                    {{-- Status dot carries the milestone's colour, which is the only
                         signal left when the bar sits at 0%. --}}
                    <span class="gv-msb-dot" style="background:{{ $ms['color'] }};" aria-hidden="true"></span>

                    <a class="gv-ms-name" href="#/tickets/editMilestone/{{ (int) $ms['id'] }}" title="{{ __('links.edit_milestone') }}: {{ $ms['headline'] }}">{{ $ms['headline'] }}</a>

                    <span class="gv-ms-due">@if ($msDue !== null){{ __('label.due') }} {{ format($msDue)->date() }}@endif</span>

                    {{-- Milestone progress is context only — it never aggregates into
                         the goal's own metric above (goal progress stays
                         metric-defined). --}}
                    <div class="gv-msb-track"><div class="gv-msb-fill" style="width:{{ $msPct }}%;background:{{ $ms['color'] }};"></div></div>
                    <span class="gv-msb-pct">{{ $msPct }}%</span>

                    @if ($login::userIsAtLeast($roles::$editor))
                        <button type="button"
                                hx-post="{{ BASE_URL }}/goalcanvas/editCanvasItem/{{ $id }}"
                                hx-vals='{"removeMilestone": {{ (int) $ms['id'] }}}'
                                hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                hx-target="#goalMsSection"
                                hx-swap="outerHTML"
                                class="delete gv-ms-remove"
                                aria-label="{{ __("links.remove") }}: {{ $ms['headline'] }}" title="{{ __("links.remove") }}"><i class="fa fa-close" aria-hidden="true"></i></button>
                    @else
                        <span></span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

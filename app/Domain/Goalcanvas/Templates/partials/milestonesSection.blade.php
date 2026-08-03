{{--
    Goal editor — linked-milestones section (summary line + chip row).

    Lives in its own partial so the chip-remove hx-post can re-render the WHOLE
    section (hx-target="#goalMsSection" outerHTML): deleting only the chip node
    left the "N milestones · X in progress" summary and the scroll arrow stale
    until the modal reopened.

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
        <style>
            .goalMsWrap{position:relative;}
            .goalMsRow{display:flex;gap:8px;overflow-x:auto;overflow-y:hidden;padding-bottom:9px;scroll-snap-type:x proximity;-webkit-overflow-scrolling:touch;}
            .goalMsRow::-webkit-scrollbar{height:10px;}
            .goalMsRow::-webkit-scrollbar-thumb{background:#9aa7ad;border-radius:10px;border:2px solid transparent;background-clip:padding-box;}
            .goalMsRow::-webkit-scrollbar-thumb:hover{background:#7d8b92;}
            .goalMsRow::-webkit-scrollbar-track{background:var(--secondary-background,#eef1f2);border-radius:10px;}
            .goalMsRow > .goalMsChip{scroll-snap-align:start;transition:border-color .12s;}
            .goalMsChip:hover{border-color:var(--primary-color,#004666)!important;}
            .goalMsChip .msLink{text-decoration:none;color:inherit;cursor:pointer;display:flex;align-items:center;min-width:0;flex:1;}
            .goalMsChip .msLink:hover .msName{color:var(--primary-color,#004666);}
            .goalMsNext{position:absolute;right:-3px;top:5px;width:29px;height:29px;border-radius:50%;border:1px solid var(--main-border-color,#e4e7ec);background:var(--primary-background,#fff);color:var(--primary-color,#004666);display:flex;align-items:center;justify-content:center;font-size:19px;line-height:1;cursor:pointer;box-shadow:0 2px 8px rgba(20,40,50,.16);z-index:5;}
            .goalMsNext:hover{background:var(--secondary-background,#f2f4f7);}
        </style>
        <div class="goalMsWrap">
            <div class="goalMsRow">
                @foreach ($goalMilestones as $ms)
                    <div class="goalMsChip" style="position:relative;flex:0 0 auto;min-width:150px;max-width:220px;height:42px;border-radius:9px;border:1px solid var(--main-border-color,#e4e7ec);background:var(--secondary-background,#f2f4f7);overflow:hidden;display:flex;align-items:center;padding:0 10px;">
                        <span style="position:absolute;left:0;top:0;bottom:0;width:{{ (int) $ms['percentDone'] }}%;background:{{ $ms['color'] }};opacity:.18;border-right:2px solid {{ $ms['color'] }};"></span>
                        <a href="#/tickets/editMilestone/{{ (int) $ms['id'] }}" class="msLink" style="position:relative;z-index:1;" title="{{ __('links.edit_milestone') }}: {{ $ms['headline'] }}">
                            <span class="msName" style="flex:1;min-width:0;font-size:12.5px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $ms['headline'] }}</span>
                            <span style="flex:none;font-size:11px;font-weight:600;opacity:.7;margin-left:6px;">{{ (int) $ms['percentDone'] }}%</span>
                        </a>
                        @if ($login::userIsAtLeast($roles::$editor))
                            <button type="button"
                                    hx-post="{{ BASE_URL }}/goalcanvas/editCanvasItem/{{ $id }}"
                                    hx-vals='{"removeMilestone": {{ (int) $ms['id'] }}}'
                                    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                    hx-target="#goalMsSection"
                                    hx-swap="outerHTML"
                                    class="delete"
                                    style="position:relative;z-index:1;margin-left:8px;opacity:.6;background:transparent;border:none;cursor:pointer;padding:0;flex:none;"
                                    aria-label="{{ __("links.remove") }}: {{ $ms['headline'] }}" title="{{ __("links.remove") }}"><i class="fa fa-close" aria-hidden="true"></i></button>
                        @endif
                    </div>
                @endforeach
            </div>
            @if (count($goalMilestones) > 2)
                <button type="button" class="goalMsNext" onclick="this.parentElement.querySelector('.goalMsRow').scrollBy({left:210,behavior:'smooth'});" aria-label="{{ __('goalcanvas.scroll_more_milestones') }}" title="{{ __('goalcanvas.scroll_more_milestones') }}"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
            @endif
        </div>
    @endif
</div>

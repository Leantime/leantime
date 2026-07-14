{{--
    Stakeholder Report — Page 1 (Overview)

    §5 page 1: KPI band + peak-this-period hero + needs-attention +
    Theory-of-Change narrative + theory-health strip.

    Vars in (from deck):
      $completedCount   int
      $completedDelta   int (may be negative)
      $goalsOnTrack     int
      $goalsTotal       int
      $overdueCount     int
      $hoursLogged      float
      $needsAttn        array — $report['needsAttention']
      $logicModel       null | {narrative, healthBadges, ...}
      $hasLM            bool
--}}

<style>
.rd-scope .p1-topband{display:grid;grid-template-columns:1.55fr minmax(0,1fr);gap:12px;margin-bottom:14px;align-items:stretch;}
.rd-scope .p1-hero{border:1px solid #d3e4dc;border-radius:var(--rd-r-sm);background:linear-gradient(180deg,var(--rd-s5-bg) 0%,#fff 80px);padding:14px 16px;}
.rd-scope .p1-hero .eye{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;}
.rd-scope .p1-hero .slabel{font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-s5);}
.rd-scope .p1-hero .rec{font-size:11px;font-weight:500;color:var(--rd-text-3);display:flex;align-items:center;gap:6px;}
.rd-scope .p1-hero .rec i{font-size:11px;color:var(--rd-ok);}
.rd-scope .p1-hero .rec .rec-change{color:var(--rd-accent);text-decoration:none;font-weight:600;}
.rd-scope .p1-hero .rec .rec-change:hover{text-decoration:underline;}
.rd-scope .p1-hero .rec-note{display:inline-flex;align-items:center;gap:4px;font-size:11px;color:var(--rd-text-3);font-style:italic;}
.rd-scope .p1-hero .rec-note i{font-size:10px;color:var(--rd-text-4);}
.rd-scope .p1-hero h3{font-size:17px;font-weight:600;letter-spacing:-.2px;line-height:1.3;color:var(--rd-text-1);margin:0;}
.rd-scope .p1-hero p{font-size:13px;color:var(--rd-text-2);line-height:1.5;margin-top:6px;margin-bottom:0;}
.rd-scope .p1-hero .hf{display:flex;align-items:center;gap:10px;margin-top:10px;flex-wrap:wrap;}
.rd-scope .p1-hero .badge-goal{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:500;padding:3px 10px;border-radius:20px;background:var(--rd-s3-bg);color:#7d5c18;}
.rd-scope .p1-hero .badge-goal i{font-size:9px;}
.rd-scope .p1-hero .hm{font-size:11.5px;color:var(--rd-text-3);}
.rd-scope .p1-hero.empty{background:none;border-style:dashed;color:var(--rd-text-3);font-size:12.5px;text-align:center;padding:20px 16px;}
.rd-scope .p1-hero.empty .h{font-size:13.5px;font-weight:600;color:var(--rd-text-2);margin-bottom:4px;}

.rd-scope .p1-needs{background:var(--rd-danger-bg);border-radius:var(--rd-r-sm);border-left:4px solid var(--rd-danger);padding:14px 16px;}
.rd-scope .p1-needs.calm{background:var(--rd-bg);border-left-color:var(--rd-ok);}
.rd-scope .p1-needs .nt{margin-bottom:7px;display:flex;align-items:center;gap:7px;font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-danger);}
.rd-scope .p1-needs.calm .nt{color:var(--rd-ok);}
.rd-scope .p1-needs .nt i{font-size:10px;}
.rd-scope .p1-needs .nx{font-size:13px;color:var(--rd-text-1);line-height:1.55;}
.rd-scope .p1-needs .na-grp{margin-top:10px;}
.rd-scope .p1-needs .na-grp:first-child{margin-top:0;}
.rd-scope .p1-needs .na-hd{font-size:10px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--rd-danger);margin-bottom:5px;}
.rd-scope .p1-needs.calm .na-hd{color:var(--rd-ok);}
.rd-scope .p1-needs .na-items{list-style:none;margin:0;padding:0;}
.rd-scope .p1-needs .na-items li{display:flex;flex-wrap:wrap;align-items:center;gap:6px;padding:3px 0;font-size:12.5px;color:var(--rd-text-1);}
.rd-scope .p1-needs .na-name{font-weight:600;}
.rd-scope .p1-needs .na-badge{display:inline-flex;align-items:center;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:var(--rd-warn-bg);color:var(--rd-warn-tx);text-transform:none;letter-spacing:.2px;}
.rd-scope .p1-needs .na-badge.miss{background:var(--rd-danger-bg);color:var(--rd-danger);}
.rd-scope .p1-needs .na-meta{color:var(--rd-text-3);font-size:11.5px;font-weight:400;}
.rd-scope .p1-needs .na-more{color:var(--rd-text-3);font-size:11.5px;font-style:italic;padding-top:2px;}

.rd-scope .p1-toc{border:1px solid var(--rd-line);border-left:4px solid var(--rd-s5);border-radius:var(--rd-r-sm);padding:13px 17px;margin-bottom:10px;}
.rd-scope .p1-toc .tl{margin-bottom:6px;font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-s5);display:inline-flex;align-items:center;gap:6px;}
.rd-scope .p1-toc .tx{font-size:13.5px;line-height:1.6;color:var(--rd-text-2);}
.rd-scope .p1-toc .n1{color:var(--rd-s1);font-weight:600;}
.rd-scope .p1-toc .n2{color:var(--rd-s2);font-weight:600;}
.rd-scope .p1-toc .n3{color:var(--rd-s3);font-weight:600;}
.rd-scope .p1-toc .n4{color:var(--rd-s4);font-weight:600;}
.rd-scope .p1-toc .n5{color:var(--rd-s5);font-weight:600;}
.rd-scope .p1-toc.empty{color:var(--rd-text-3);font-style:italic;border-left-color:var(--rd-line);}

/* Color legend popover — hover the info icon on the ToC label to show
   which color maps to which stage. Vanilla CSS hover; no JS needed. */
.rd-scope .p1-toc-info{position:relative;display:inline-flex;}
.rd-scope .p1-toc-info .ii{width:14px;height:14px;border-radius:50%;background:var(--rd-line-soft);color:var(--rd-text-3);display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;cursor:help;text-transform:none;letter-spacing:0;}
.rd-scope .p1-toc-info .ii:hover{background:var(--rd-line);color:var(--rd-text-1);}
.rd-scope .p1-toc-info .pop{position:absolute;top:calc(100% + 8px);left:-6px;background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);box-shadow:var(--rd-sh-lg);padding:10px 12px;width:220px;font-size:11.5px;line-height:1.4;color:var(--rd-text-2);z-index:50;opacity:0;visibility:hidden;transform:translateY(-3px);transition:opacity .12s,transform .12s,visibility .12s;text-transform:none;letter-spacing:0;font-weight:400;}
.rd-scope .p1-toc-info:hover .pop,
.rd-scope .p1-toc-info:focus-within .pop{opacity:1;visibility:visible;transform:translateY(0);}
.rd-scope .p1-toc-info .pop .h{font-size:10px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--rd-text-3);margin-bottom:6px;}
.rd-scope .p1-toc-info .pop .lg{display:flex;align-items:center;gap:8px;padding:3px 0;}
.rd-scope .p1-toc-info .pop .lg .sw{width:12px;height:12px;border-radius:3px;flex:none;}
.rd-scope .p1-toc-info .pop .lg .nm{font-weight:600;color:var(--rd-text-1);}
.rd-scope .p1-toc-info .pop .lg .sub{color:var(--rd-text-3);margin-left:auto;font-size:10.5px;}

/* Strategic goals — a compact goal-per-row list. Progress bar + status pill
   + current/target read. Enough detail for a board to grok how the goals sit,
   without a full goal-board dive (that's Goals & Outcomes). */
.rd-scope .p1-goals{margin-bottom:14px;}
.rd-scope .p1-goals-hd{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:8px;}
.rd-scope .p1-goals-hd .l{font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-accent);}
.rd-scope .p1-goals-hd .sub{font-size:11.5px;color:var(--rd-text-3);}
.rd-scope .p1-goals-list{border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);overflow:hidden;background:var(--rd-panel);}
.rd-scope .p1-goal{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr) 84px 78px;gap:12px;align-items:center;padding:11px 14px;border-top:1px solid var(--rd-line-soft);}
.rd-scope .p1-goal:first-child{border-top:none;}
.rd-scope .p1-goal .gn{min-width:0;}
.rd-scope .p1-goal .gn .t{font-size:13px;font-weight:600;color:var(--rd-text-1);line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.rd-scope .p1-goal .gn .m{font-size:11px;color:var(--rd-text-3);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.rd-scope .p1-goal .gbar-wrap{min-width:0;}
.rd-scope .p1-goal .gbar{height:6px;background:#eef1f3;border-radius:3px;overflow:hidden;}
.rd-scope .p1-goal .gbar > i{display:block;height:100%;border-radius:3px;background:var(--rd-ok);}
.rd-scope .p1-goal.atrisk .gbar > i{background:var(--rd-warn);}
.rd-scope .p1-goal.miss .gbar > i{background:var(--rd-danger);}
.rd-scope .p1-goal .gvals{font-size:11.5px;color:var(--rd-text-3);margin-top:4px;font-variant-numeric:tabular-nums;}
.rd-scope .p1-goal .gpct{font-size:15px;font-weight:600;color:var(--rd-text-1);text-align:right;font-variant-numeric:tabular-nums;}
.rd-scope .p1-goal .gstat{display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:600;padding:3px 9px;border-radius:20px;text-transform:none;letter-spacing:.2px;justify-self:end;}
.rd-scope .p1-goal .gstat.ontrack{background:#dcf1e6;color:#1a7d52;}
.rd-scope .p1-goal .gstat.atrisk{background:var(--rd-warn-bg);color:var(--rd-warn-tx);}
.rd-scope .p1-goal .gstat.miss{background:var(--rd-danger-bg);color:var(--rd-danger);}
.rd-scope .p1-goal .gstat.none{background:var(--rd-bg);color:var(--rd-text-3);}
.rd-scope .p1-goals-empty{padding:22px;text-align:center;color:var(--rd-text-3);font-size:12.5px;background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);}

.rd-scope .p1-theory{display:flex;gap:9px;align-items:flex-start;font-size:13px;color:var(--rd-warn-tx);background:var(--rd-warn-bg);border-radius:var(--rd-r-xs);padding:11px 15px;line-height:1.5;}
.rd-scope .p1-theory i{font-size:12px;color:#b8860b;margin-top:2px;flex:none;}
.rd-scope .p1-theory b{font-weight:600;color:var(--rd-text-1);}
.rd-scope .p1-theory.ok{color:var(--rd-text-2);background:var(--rd-bg);}
.rd-scope .p1-theory.ok i{color:var(--rd-ok);}
</style>

{{-- ── KPI band (value-first, delta inline, lowercase label) ──────── --}}
<div class="rd-kpi">
    <div class="rd-kcell">
        <div class="kv">
            {{ $completedCount }}
            @if ($completedDelta > 0)
                <span class="up" title="{{ sprintf(__('stakeholder.kpi.delta_vs_prior'), $completedDelta) }}"><i class="fa fa-arrow-up"></i> +{{ $completedDelta }}</span>
            @elseif ($completedDelta < 0)
                <span class="down" title="{{ sprintf(__('stakeholder.kpi.delta_vs_prior'), $completedDelta) }}"><i class="fa fa-arrow-down"></i> {{ $completedDelta }}</span>
            @endif
        </div>
        <div class="kl">{{ __('stakeholder.kpi.completed_this_period') }}</div>
    </div>
    <div class="rd-kcell">
        <div class="kv">{{ $goalsOnTrack }}<small>/{{ $goalsTotal }}</small></div>
        <div class="kl">{{ __('stakeholder.kpi.goals_on_track_lc') }}</div>
    </div>
    <div class="rd-kcell @if ($overdueCount > 0) risk @endif">
        <div class="kv">{{ $overdueCount }}</div>
        <div class="kl">{{ __('stakeholder.kpi.milestones_overdue') }}</div>
    </div>
    <div class="rd-kcell">
        <div class="kv">{{ number_format($hoursLogged, $hoursLogged >= 100 ? 0 : 1) }}<small>h</small></div>
        <div class="kl">{{ __('stakeholder.kpi.hours_this_period') }}</div>
    </div>
</div>

{{-- ── Hero (peak this period) + Needs attention ─────────────────── --}}
@php
    // Needs-attention detail from ReportEngine::buildNeedsAttention. Key names
    // verified against the service — the block reads items by NAME, not counts.
    $overdueMilestones = $needsAttn['overdueMilestones'] ?? [];
    $goalsAtRisk      = $needsAttn['goalsAtRisk'] ?? [];
    $staleProjects    = $needsAttn['staleProjects'] ?? [];
    $statusAlerts     = $needsAttn['statusAlerts'] ?? [];
    $needsCount = count($overdueMilestones) + count($goalsAtRisk) + count($staleProjects) + count($statusAlerts);

    // Peak-this-period nomination (recommend-and-override, §5 page 1 hero).
    // Rank candidates from completed-this-period milestones:
    //   1. Has non-empty outcomeImpact (structural closure carrying its own board narrative)
    //   2. Otherwise, most recent completion
    // The top pick is the recommendation; the (future) owner override lives here.
    $completed = $report['milestones']['completed'] ?? [];
    $withImpact = [];
    $withoutImpact = [];
    foreach ($completed as $m) {
        $m = (object) $m;
        $impact = trim((string) ($m->outcomeImpact ?? ''));
        if ($impact !== '') {
            $withImpact[] = $m;
        } else {
            $withoutImpact[] = $m;
        }
    }
    $sortByCompletion = fn ($a, $b) => strcmp((string) ($b->completedOn ?? $b->modified ?? ''), (string) ($a->completedOn ?? $a->modified ?? ''));
    usort($withImpact, $sortByCompletion);
    usort($withoutImpact, $sortByCompletion);
    $peak = $withImpact[0] ?? $withoutImpact[0] ?? null;
    $peakIsStrong = $peak !== null && trim((string) ($peak->outcomeImpact ?? '')) !== '';

    if ($peak !== null) {
        $peakDate = ! empty($peak->completedOn)
            ? (is_object($peak->completedOn) ? $peak->completedOn->setToUserTimezone()->format('M j') : date('M j', strtotime((string) $peak->completedOn)))
            : (! empty($peak->modified) ? date('M j', strtotime((string) $peak->modified)) : '');
        $peakBody = trim((string) ($peak->outcomeImpact ?? $peak->description ?? ''));
        // Strip any HTML that survived from a rich-text editor.
        $peakBody = trim(strip_tags($peakBody));
    }
@endphp
<div class="p1-topband">
    {{-- Peak this period — recommend-and-override. Ranked from completed
         milestones; owner override is a future write path. --}}
    @if ($peak === null)
        <div class="p1-hero empty">
            <div class="eye"><span class="slabel">{{ __('stakeholder.overview.peak_label') }}</span><span class="rec"><i class="fa fa-wand-magic-sparkles"></i> {{ __('stakeholder.overview.peak_coming') }}</span></div>
            <div class="h">{{ __('stakeholder.overview.peak_none_title') }}</div>
            <div>{{ __('stakeholder.overview.peak_none_hint') }}</div>
        </div>
    @else
        <div class="p1-hero">
            <div class="eye">
                <span class="slabel">{{ __('stakeholder.overview.peak_label') }}</span>
                <span class="rec"><i class="fa fa-wand-magic-sparkles"></i> {{ __('stakeholder.overview.peak_recommended') }} · <a href="javascript:void(0)" class="rec-change" title="{{ __('stakeholder.overview.peak_override_tip') }}">{{ __('stakeholder.overview.peak_change') }}</a></span>
            </div>
            <h3>{{ $tpl->escape($peak->headline ?? '') }}</h3>
            @if ($peakBody !== '')
                <p>{{ $tpl->escape(mb_strlen($peakBody) > 260 ? mb_substr($peakBody, 0, 257).'…' : $peakBody) }}</p>
            @endif
            <div class="hf">
                @if (! empty($peak->projectName))
                    <span class="badge-goal"><i class="fa fa-diagram-project"></i> {{ $tpl->escape($peak->projectName) }}</span>
                @endif
                @if (! $peakIsStrong)
                    <span class="rec-note" title="{{ __('stakeholder.overview.peak_weak_tip') }}"><i class="fa fa-circle-info"></i> {{ __('stakeholder.overview.peak_weak_note') }}</span>
                @endif
                @if ($peakDate !== '')
                    <span class="hm">{{ $peakDate }}</span>
                @endif
            </div>
        </div>
    @endif

    <div class="p1-needs @if ($needsCount === 0) calm @endif">
        <div class="nt"><i class="fa @if ($needsCount === 0) fa-check-circle @else fa-triangle-exclamation @endif"></i>{{ __('stakeholder.overview.needs_label') }}</div>
        <div class="nx">
            @if ($needsCount === 0)
                {{ __('stakeholder.overview.nothing_needs_attention') }}
            @else
                {{-- At-risk goals — named. The block loses its meaning as a plain count;
                     a board wants to know WHICH goal is at risk to decide what to do. --}}
                @if (count($goalsAtRisk) > 0)
                    <div class="na-grp">
                        <div class="na-hd">{{ sprintf(__('stakeholder.overview.na_goals_hd'), count($goalsAtRisk)) }}</div>
                        <ul class="na-items">
                            @foreach (array_slice($goalsAtRisk, 0, 3) as $goal)
                                @php
                                    $goal = (object) $goal;
                                    $isMiss = (string) $goal->status === 'status_miss';
                                    $title = trim((string) ($goal->title ?? $goal->description ?? __('stakeholder.goals.untitled')));
                                    $progress = round((float) ($goal->goalProgress ?? 0));
                                @endphp
                                <li>
                                    <span class="na-name">{{ $tpl->escape($title) }}</span>
                                    <span class="na-badge @if ($isMiss) miss @endif">{{ $isMiss ? __('stakeholder.goals.miss') : __('stakeholder.goals.atrisk') }}</span>
                                    <span class="na-meta">· {{ $progress }}%</span>
                                </li>
                            @endforeach
                            @if (count($goalsAtRisk) > 3)
                                <li class="na-more">{{ sprintf(__('stakeholder.overview.na_more'), count($goalsAtRisk) - 3) }}</li>
                            @endif
                        </ul>
                    </div>
                @endif

                {{-- Overdue milestones — named + project. --}}
                @if (count($overdueMilestones) > 0)
                    <div class="na-grp">
                        <div class="na-hd">{{ sprintf(__('stakeholder.overview.na_milestones_hd'), count($overdueMilestones)) }}</div>
                        <ul class="na-items">
                            @foreach (array_slice($overdueMilestones, 0, 3) as $m)
                                @php
                                    $m = (object) $m;
                                    $mname = trim((string) ($m->headline ?? __('stakeholder.overview.na_untitled_milestone')));
                                    $projName = trim((string) ($m->projectName ?? ''));
                                @endphp
                                <li>
                                    <span class="na-name">{{ $tpl->escape($mname) }}</span>
                                    @if ($projName !== '')
                                        <span class="na-meta">· {{ $tpl->escape($projName) }}</span>
                                    @endif
                                </li>
                            @endforeach
                            @if (count($overdueMilestones) > 3)
                                <li class="na-more">{{ sprintf(__('stakeholder.overview.na_more'), count($overdueMilestones) - 3) }}</li>
                            @endif
                        </ul>
                    </div>
                @endif

                {{-- Stale/silent projects — quiet flag, one line per project. --}}
                @if (count($staleProjects) > 0)
                    <div class="na-grp">
                        <div class="na-hd">{{ sprintf(__('stakeholder.overview.na_silent_hd'), count($staleProjects)) }}</div>
                        <ul class="na-items">
                            @foreach (array_slice($staleProjects, 0, 3) as $p)
                                @php $p = (object) $p; @endphp
                                <li>
                                    <span class="na-name">{{ $tpl->escape($p->name ?? '') }}</span>
                                    <span class="na-meta">· {{ __('stakeholder.overview.na_no_update_30d') }}</span>
                                </li>
                            @endforeach
                            @if (count($staleProjects) > 3)
                                <li class="na-more">{{ sprintf(__('stakeholder.overview.na_more'), count($staleProjects) - 3) }}</li>
                            @endif
                        </ul>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- ── Theory of Change narrative (stage-colored) ────────────────── --}}
@if ($hasLM && ! empty($logicModel['narrative']['hasItems']))
    @php $stageTexts = $logicModel['narrative']['stageTexts'] ?? []; @endphp
    <div class="p1-toc">
        <div class="tl">
            {{ __('stakeholder.overview.toc_label') }}
            <span class="p1-toc-info" tabindex="0">
                <span class="ii" aria-hidden="true">i</span>
                <span class="pop" role="tooltip">
                    <span class="h">{{ __('stakeholder.overview.color_legend') }}</span>
                    <span class="lg"><span class="sw" style="background:var(--rd-s1)"></span><span class="nm">{{ __('box.logicmodel.inputs') }}</span><span class="sub">{{ __('stakeholder.lm.stage_sub.inputs') }}</span></span>
                    <span class="lg"><span class="sw" style="background:var(--rd-s2)"></span><span class="nm">{{ __('box.logicmodel.activities') }}</span><span class="sub">{{ __('stakeholder.lm.stage_sub.activities') }}</span></span>
                    <span class="lg"><span class="sw" style="background:var(--rd-s3)"></span><span class="nm">{{ __('box.logicmodel.outputs') }}</span><span class="sub">{{ __('stakeholder.lm.stage_sub.outputs') }}</span></span>
                    <span class="lg"><span class="sw" style="background:var(--rd-s4)"></span><span class="nm">{{ __('box.logicmodel.outcomes') }}</span><span class="sub">{{ __('stakeholder.lm.stage_sub.outcomes') }}</span></span>
                    <span class="lg"><span class="sw" style="background:var(--rd-s5)"></span><span class="nm">{{ __('box.logicmodel.impact') }}</span><span class="sub">{{ __('stakeholder.lm.stage_sub.impact') }}</span></span>
                </span>
            </span>
        </div>
        <div class="tx">
            {{ __('stakeholder.overview.toc_by_investing') }}
            <span class="n1">{{ $stageTexts['inputs'] ?? '['.__('box.logicmodel.inputs').']' }}</span>
            {{ __('stakeholder.overview.toc_and_delivering') }}
            <span class="n2">{{ $stageTexts['activities'] ?? '['.__('box.logicmodel.activities').']' }}</span>,
            {{ __('stakeholder.overview.toc_we_produce') }}
            <span class="n3">{{ $stageTexts['outputs'] ?? '['.__('box.logicmodel.outputs').']' }}</span>
            — {{ __('stakeholder.overview.toc_toward') }}
            <span class="n4">{{ $stageTexts['outcomes'] ?? '['.__('box.logicmodel.outcomes').']' }}</span>,
            {{ __('stakeholder.overview.toc_in_service_of') }}
            <span class="n5">{{ $stageTexts['impact'] ?? '['.__('box.logicmodel.impact').']' }}</span>.
        </div>
    </div>
@else
    <div class="p1-toc empty">{{ __('stakeholder.overview.toc_empty') }}</div>
@endif

{{-- ── Theory-health strip (fragile-connection warning) ──────────── --}}
@if ($hasLM)
    @php
        $badges = $logicModel['healthBadges'] ?? [];
        $risky = [];
        foreach ($badges as $b) {
            if (($b['has_data'] ?? false) && in_array($b['health_status'] ?? '', ['warning', 'risk'], true)) {
                $risky[] = $b;
            }
        }
    @endphp
    @if (count($risky) > 0)
        @php $first = $risky[0]; @endphp
        <div class="p1-theory">
            <i class="fa fa-triangle-exclamation"></i>
            <div>
                <b>{{ __('stakeholder.overview.theory_health_label') }}:</b>
                {{ sprintf(__('stakeholder.overview.theory_link_needs_work'), $first['connector_label']) }}
                @if (! empty($first['assumption_text']))
                    {{ __('stakeholder.overview.theory_it_rests_on') }} <i>{{ $tpl->escape($first['assumption_text']) }}</i>
                @endif
                @if (empty($first['evidence_notes']))
                    {{ __('stakeholder.overview.theory_no_evidence') }}
                @endif
                @if (count($risky) > 1)
                    · <span style="color:var(--rd-warn-tx);opacity:.8;">{{ sprintf(__('stakeholder.overview.theory_more_fragile'), count($risky) - 1) }}</span>
                @endif
            </div>
        </div>
    @else
        <div class="p1-theory ok">
            <i class="fa fa-check-circle"></i>
            <div><b>{{ __('stakeholder.overview.theory_health_label') }}:</b> {{ __('stakeholder.overview.theory_all_ok') }}</div>
        </div>
    @endif
@endif

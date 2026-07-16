{{--
    Stakeholder Report — 4-page deck shell.

    Reused by both StrategyPro (strategy scope) and PgmPro (program scope).
    Data passed in via @include vars; this partial owns:
      - persistent header (subject, period, updated, status verdict)
      - global controls (period picker, print)
      - deck navigation (4 tabs + swipe + arrow keys + arrow buttons)
      - the 4 page containers (Overview / Logic Model / Resources & Coverage / Programs)
      - scoped CSS with `minmax(0,1fr)` discipline (§2 layout constraint)
      - print stylesheet expanding the deck (§7)

    Vars in:
      $scope            'strategy' | 'program'
      $subject          string — displayed in the header
      $period           ReportPeriod
      $updatedAt        string
      $verdict          'ontrack' | 'atrisk' | 'off' | 'unknown'
      $verdictLabel     string — the visible verdict
      $verdictSource    string — provenance line (never hidden, per §3)
      $report           ReportEngine::buildReport() output
      $stats            $report['stats']
      $deltas           $report['deltas']
      $needsAttn        $report['needsAttention']
      $logicModel       null | {canvasId, narrative, stageProgress, healthBadges, coverageMatrix}
      $goalsGroup       {goals, byProject, counts}   — strategy: strategyGoals; program: programGoals
      $programRows      array — strategy only, empty at program scope
      $programUpdates   array — strategy only, empty at program scope
--}}

@php
    use Leantime\Domain\Reports\Models\ReportPeriod;

    $verdictDotColor = match ($verdict) {
        'ontrack'    => '#3E937A',
        'inprogress' => '#3F72B0',
        'atrisk'     => '#C09035',
        'off'        => '#C2295B',
        default      => '#9CA3AF',
    };
    $completedCount = (int) ($stats['completed'] ?? 0);
    $overdueCount   = (int) ($stats['overdue'] ?? 0);
    $goalsOnTrack   = (int) ($stats['goalsOnTrack'] ?? 0);
    $goalsTotal     = (int) ($stats['goalsTotal'] ?? 0);
    $hoursLogged    = (float) ($stats['hoursLogged'] ?? 0);
    $completedDelta = (int) ($deltas['completedDelta'] ?? 0);
    $hasLM          = $logicModel !== null;

    // Semantic period label — the "why this period" chip in the header sub-line.
    // Board audiences care WHY the report is showing this range (because it's
    // last closed) more than the raw dates, which appear separately in the picker.
    $periodMeaning = match ($period->preset) {
        ReportPeriod::PRESET_LAST_QUARTER => __('stakeholder.period.last_closed'),
        ReportPeriod::PRESET_THIS_QUARTER => __('stakeholder.period.in_progress'),
        ReportPeriod::PRESET_NEXT_QUARTER => __('stakeholder.period.upcoming'),
        ReportPeriod::PRESET_CUSTOM       => __('stakeholder.period.custom'),
        default                           => '',
    };

    // Preset name for the picker button — matches what the user selects in the
    // dropdown ("Last quarter" / "This quarter" / "Next quarter"). Deliberately
    // NOT "Q2 2026" — Leantime doesn't let companies define fiscal quarters, so
    // a calendar Q# label would be a lie for anyone whose fiscal year isn't
    // calendar-aligned. The literal date range is shown next to it.
    $presetName = match ($period->preset) {
        ReportPeriod::PRESET_LAST_QUARTER => __('label.period_last_quarter'),
        ReportPeriod::PRESET_THIS_QUARTER => __('label.period_this_quarter'),
        ReportPeriod::PRESET_NEXT_QUARTER => __('label.period_next_quarter'),
        ReportPeriod::PRESET_CUSTOM       => __('label.period_custom'),
        default                           => __('label.period_this_quarter'),
    };

    // Reload URL bases for the period picker preset links.
    $reportUrl = BASE_URL.'/'.($scope === 'strategy' ? 'strategyPro' : 'pgmPro').'/report';
@endphp

<style>
/*
 * Report deck — scoped styles. `.rd-*` class prefix isolates from the app's
 * Bootstrap / legacy CSS. Layout discipline (§2): `minmax(0,1fr)` on every
 * grid track, `min-width:0` on every grid/flex child, so nothing pushes the
 * deck wider than the viewport.
 */
.rd-scope {
    --rd-accent:#004766;
    --rd-text-1:#182831;
    --rd-text-2:#4a5a63;
    --rd-text-3:#7a8791;
    --rd-text-4:#aab4bb;
    --rd-line:#e3e8ea;
    --rd-line-soft:#eef1f3;
    --rd-bg:#fafbfb;
    --rd-panel:#fff;
    --rd-ok:#3E937A;
    --rd-warn:#C09035;
    --rd-warn-bg:#FBF3E4;
    --rd-warn-tx:#8a6212;
    --rd-danger:#C2295B;
    --rd-danger-bg:#FBE9EF;
    --rd-r-sm:11px;
    --rd-r-xs:8px;
    --rd-sh-sm:0 1px 3px rgba(20,40,50,.06);
    --rd-sh-lg:0 20px 56px rgba(20,40,50,.16);
    color:var(--rd-text-1);
}
.rd-scope *{min-width:0;}

/* Persistent header — sits above the deck. Left: subject + provenance. Right:
   status verdict (stated verdict with provenance line, NOT a tappable pill). */
.rd-hdr{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:20px;align-items:start;padding:14px 20px;background:var(--rd-panel);border-radius:var(--rd-r-sm);box-shadow:var(--rd-sh-sm);margin-bottom:10px;}
.rd-hdr .st{min-width:0;}
.rd-hdr .st .h{font-size:20px;font-weight:600;line-height:1.2;color:var(--rd-text-1);}
.rd-hdr .st .prov{font-size:12px;color:var(--rd-text-3);margin-top:4px;}
.rd-hdr .verdict{text-align:right;min-width:0;}
.rd-hdr .verdict .v{display:inline-flex;align-items:center;gap:8px;font-size:15px;font-weight:600;color:var(--rd-text-1);}
.rd-hdr .verdict .v .dot{width:10px;height:10px;border-radius:50%;flex:none;}
.rd-hdr .verdict .src{font-size:11.5px;color:var(--rd-text-3);margin-top:4px;font-weight:400;}

/* Tab-bar right cluster — period picker + prev/next arrows sit here to keep
   the tab row self-contained instead of a separate "globalbar" row above. */
.rd-tab-right{margin-left:auto;display:flex;align-items:center;gap:8px;flex-wrap:nowrap;}

/* Compact period picker — one button showing current period; click opens a
   dropdown with the 3 quarter presets + a custom-range mini-form. Replaces the
   inline pill row; the design (§3 + board mockup p1) calls for a stated period
   with a single affordance to change it, not four always-visible options. */
.rd-picker{position:relative;}
.rd-picker-btn{display:inline-flex;align-items:center;gap:8px;font:inherit;font-size:13px;color:var(--rd-text-1);background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:22px;padding:7px 14px;cursor:pointer;box-shadow:var(--rd-sh-sm);}
.rd-picker-btn:hover{border-color:var(--rd-text-4);}
.rd-picker-btn .rd-picker-q{font-weight:600;}
.rd-picker-btn .rd-picker-range{color:var(--rd-text-3);font-size:12px;}
.rd-picker-btn i{font-size:11px;color:var(--rd-text-3);}
/* Picker dropdown — sized defensively against the app's global anchor/input
   styles that would otherwise inflate everything (Bootstrap 2.x menus, form
   inputs, etc.). Fixed width + explicit font-size on every text element. */
.rd-picker-menu{position:absolute;right:0;top:calc(100% + 6px);background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);box-shadow:var(--rd-sh-lg);width:280px;padding:4px;z-index:100;font-size:12px;line-height:1.3;}
.rd-picker-menu[hidden]{display:none;}
.rd-picker-menu *{box-sizing:border-box;}

.rd-picker-opt{display:flex;justify-content:space-between;align-items:center;padding:6px 10px;border-radius:6px;text-decoration:none;color:var(--rd-text-1);gap:10px;font-size:12px;line-height:1.3;}
.rd-picker-opt:hover{background:var(--rd-bg);color:var(--rd-text-1);text-decoration:none;}
.rd-picker-opt.on{background:rgba(0,71,102,.06);color:var(--rd-accent);}
.rd-picker-opt .l{font-size:12.5px;font-weight:500;color:inherit;}
.rd-picker-opt.on .l{font-weight:600;}
.rd-picker-opt .d{font-size:10.5px;color:var(--rd-text-3);font-weight:400;}

.rd-picker-sep{height:1px;background:var(--rd-line-soft);margin:4px 4px;}
.rd-picker-custom{padding:6px 10px 8px;margin:0;}
.rd-picker-cl{display:block;font-size:9.5px;color:var(--rd-text-3);text-transform:uppercase;letter-spacing:.4px;margin:0 0 5px;font-weight:600;}
.rd-picker-crow{display:flex;align-items:center;gap:5px;}
.rd-picker-cinput{flex:1;min-width:0;width:auto;font:inherit;font-size:11.5px;line-height:1.2;padding:5px 7px;height:26px;border:1px solid var(--rd-line);border-radius:5px;background:var(--rd-panel);color:var(--rd-text-1);margin:0;box-shadow:none;}
.rd-picker-cinput:focus{outline:none;border-color:var(--rd-accent);}
.rd-picker-cdash{color:var(--rd-text-3);font-size:11px;flex:none;}
.rd-picker-capply{font:inherit;font-size:11px;font-weight:500;color:#fff;background:var(--rd-accent);border:none;border-radius:5px;padding:5px 9px;height:26px;cursor:pointer;flex:none;}

/* Tab bar — sits ON the page background (matches the To-Dos Kanban·Table·List
   pattern). Not on a panel. */
.rd-tabs{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.rd-tab{background:none;border:none;font:inherit;font-size:14px;font-weight:500;color:var(--rd-text-3);padding:6px 10px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;border-radius:6px;transition:color .15s,background .15s;}
.rd-tab:hover{color:var(--rd-text-1);background:rgba(0,0,0,.03);}
.rd-tab.on{color:var(--rd-accent);font-weight:600;background:rgba(0,71,102,.06);}
.rd-tab i{font-size:12px;}
.rd-tab .ct{font-size:11px;color:var(--rd-text-4);background:var(--rd-line-soft);border-radius:10px;padding:1px 7px;margin-left:2px;}
.rd-tab.on .ct{color:var(--rd-accent);background:rgba(0,71,102,.1);}
.rd-arrows{display:flex;gap:4px;}
.rd-arrow{background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--rd-text-2);}
.rd-arrow:hover{color:var(--rd-text-1);border-color:var(--rd-text-4);}
.rd-arrow[disabled]{opacity:.4;cursor:not-allowed;}

/* Deck — one panel that sizes to the active page (no dead space on short pages).
   Pages are stacked in a horizontal track; we translate the track and only the
   active page's content contributes to height. */
.rd-deck{background:var(--rd-panel);border-radius:var(--rd-r-sm);box-shadow:var(--rd-sh-lg);overflow:hidden;position:relative;}
.rd-deck-viewport{overflow:hidden;position:relative;}
.rd-deck-track{display:flex;align-items:flex-start;transition:transform .28s cubic-bezier(.4,.15,.25,1);}
.rd-page{flex:0 0 100%;padding:24px 26px;min-width:100%;width:100%;}
.rd-page:not(.on){visibility:hidden;height:0;padding-top:0;padding-bottom:0;}

/* Page-1 KPI band — value-first, delta inline, lowercase label beneath.
   Matches the mockup: big number reads first, the "what" is a quiet caption. */
.rd-kpi{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:16px;}
.rd-kcell{padding:13px 16px;border-radius:var(--rd-r-xs);background:var(--rd-bg);}
/* KV row: center-align vertically so the delta pill and the /X subscript
   both sit at the number's optical center instead of dropping to its baseline
   (which made them look "small next to" the big number). */
.rd-kcell .kv{font-size:28px;font-weight:600;letter-spacing:-.6px;line-height:1;display:flex;align-items:center;gap:10px;color:var(--rd-text-1);}
.rd-kcell .kv small{font-size:15px;color:var(--rd-text-3);font-weight:500;align-self:flex-end;margin-bottom:2px;margin-left:-2px;letter-spacing:.5px;}
/* Delta pills — the "vs prior" signal needs to READ, not hide. Green pill for
   gains, red pill for losses. Bigger + backed + weighted so it's not lost next
   to the big number. */
.rd-kcell .kv .up,
.rd-kcell .kv .down{display:inline-flex;align-items:center;gap:3px;font-size:12px;font-weight:700;padding:4px 10px;border-radius:20px;line-height:1;letter-spacing:.2px;}
.rd-kcell .kv .up{color:#1a7d52;background:#dcf1e6;}
.rd-kcell .kv .down{color:#a11a44;background:#fbe0e8;}
.rd-kcell .kv .up i,
.rd-kcell .kv .down i{font-size:9px;}
.rd-kcell .kl{font-size:12.5px;color:var(--rd-text-2);margin-top:5px;display:flex;align-items:center;gap:6px;}
.rd-kcell.risk .kv{color:var(--rd-danger);}

/* KPI drill-down — hover the whole cell to see the list of items behind the
   count (which milestones completed, which goals off-track, etc.). Compact
   popover; won't render if the count is zero.
   Affordance: cursor:pointer + subtle hover treatment + a visible "see list"
   chevron at the top-right so the interactivity is discoverable, not a
   guess-the-dot. */
.rd-kcell{position:relative;transition:background .12s,box-shadow .12s;}
.rd-kcell.has-detail{cursor:pointer;}
.rd-kcell.has-detail:hover{background:#fff;box-shadow:var(--rd-sh-sm);}
.rd-kcell.has-detail .see-list{position:absolute;top:8px;right:10px;display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;color:var(--rd-text-3);text-transform:uppercase;letter-spacing:.4px;opacity:.7;transition:opacity .12s,color .12s;pointer-events:none;}
.rd-kcell.has-detail .see-list i{font-size:9px;}
.rd-kcell.has-detail:hover .see-list{opacity:1;color:var(--rd-accent);}
.rd-kcell .kdrill{position:absolute;top:calc(100% + 6px);left:0;right:0;background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);box-shadow:var(--rd-sh-lg);padding:8px;z-index:20;opacity:0;visibility:hidden;transform:translateY(-3px);transition:opacity .12s,transform .12s,visibility .12s;font-size:11.5px;line-height:1.4;text-align:left;letter-spacing:0;}
.rd-kcell.has-detail.open .kdrill{opacity:1;visibility:visible;transform:translateY(0);}
.rd-kcell.has-detail.open{background:#fff;box-shadow:var(--rd-sh-sm);}
.rd-kcell.has-detail.open .see-list{opacity:1;color:var(--rd-accent);}
.rd-kcell.has-detail.open .see-list i{transform:rotate(180deg);}
.rd-kcell.has-detail .see-list i{transition:transform .18s;}
.rd-kcell .kdrill .kd-hd{font-size:9.5px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--rd-text-3);padding:2px 6px 6px;}
.rd-kcell .kdrill ul{list-style:none;margin:0;padding:0;}
.rd-kcell .kdrill li{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:4px 6px;border-radius:4px;color:var(--rd-text-1);font-weight:400;}
.rd-kcell .kdrill li:hover{background:var(--rd-bg);}
.rd-kcell .kdrill li .nm{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0;flex:1;}
.rd-kcell .kdrill li .mt{color:var(--rd-text-3);font-size:10.5px;flex:none;}
.rd-kcell .kdrill .kd-more{font-size:10.5px;color:var(--rd-text-3);font-style:italic;padding:4px 6px 2px;}

/* Section container inside a page. */
.rd-section{margin-top:20px;}
.rd-section:first-child{margin-top:0;}
.rd-section .hd{display:flex;align-items:baseline;gap:10px;margin-bottom:10px;}
.rd-section .hd .t{font-size:14px;font-weight:600;color:var(--rd-text-1);}
.rd-section .hd .sub{font-size:12px;color:var(--rd-text-3);}
.rd-empty{padding:22px;text-align:center;color:var(--rd-text-3);font-size:13px;background:var(--rd-bg);border-radius:var(--rd-r-xs);}

/* Stage colors — five Logic Model stages, matching the canvas board. Used
   across Page 1 (ToC narrative colored spans), Page 2 (5-stage board), and
   Page 3 (coverage matrix rows tied to stages). */
.rd-scope{
    --rd-s1:#4A85B5; --rd-s1-bg:#e5eef6;
    --rd-s2:#3E937A; --rd-s2-bg:#e2efe9;
    --rd-s3:#C09035; --rd-s3-bg:#f6ecd7;
    --rd-s4:#8E6AAD; --rd-s4-bg:#eee5f4;
    --rd-s5:#2D7D5E; --rd-s5-bg:#dbeae1;
}

/* Task-card standard (from the canvas board): title left, status icon +
   owner avatar top-right, assumption line beneath, optional read-out
   fold below. Used on Page 2 for the 5-stage LM cards. Tight padding —
   5 columns share the deck width so cards can't afford bulk. */
.rd-cardx{border-radius:var(--rd-r-xs);padding:8px 10px;background:#fff;border:1px solid var(--rd-line-soft);border-left:3px solid var(--rd-text-4);min-width:0;}
.rd-cx-top{display:flex;align-items:flex-start;justify-content:space-between;gap:6px;}
.rd-cx-t{font-size:12.5px;font-weight:600;line-height:1.3;min-width:0;color:var(--rd-text-1);flex:1;}
.rd-cx-t .cd{display:none;}   /* colored border already carries stage identity */
.rd-cx-corner{display:flex;align-items:center;gap:5px;flex-shrink:0;}
/* Icon-only status pill — compact, hover for full label. Colored circle
   maps to the LM canvas status icons (fa-circle-check / -exclamation / etc). */
.rd-pill-icon{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:var(--rd-bg);flex:none;cursor:help;}
.rd-pill-icon i{font-size:14px;line-height:1;}
.rd-pill-ok i{color:#3E937A;}
.rd-pill-wip i{color:#4A85B5;}
.rd-pill-draft i{color:#9aa4ab;}
.rd-pill-flag i{color:var(--rd-danger);}
.rd-avatar{width:18px;height:18px;border-radius:50%;background:#5aa889;color:#fff;font-size:8px;font-weight:600;display:inline-flex;align-items:center;justify-content:center;flex:none;text-decoration:none;}
.rd-cx-hyp{font-size:10.5px;color:var(--rd-text-3);line-height:1.4;margin-top:5px;font-style:italic;}
.rd-cx-hyp .hl{font-style:normal;font-weight:600;color:var(--rd-text-2);}
.rd-cx-status{font-size:11.5px;color:var(--rd-text-3);margin-top:7px;display:inline-flex;align-items:center;gap:5px;}
.rd-cx-status .sd{width:7px;height:7px;border-radius:50%;}
.rd-cx-empty{border:1px dashed var(--rd-line);border-radius:var(--rd-r-xs);padding:10px 11px;font-size:11.5px;color:var(--rd-text-3);text-align:center;}

/* Placeholder for pages not yet built. Removed as each page lands. */
.rd-placeholder{padding:38px 24px;text-align:center;color:var(--rd-text-3);font-size:13px;line-height:1.5;background:repeating-linear-gradient(135deg,#fafbfb,#fafbfb 8px,#f4f6f7 8px,#f4f6f7 16px);border-radius:var(--rd-r-xs);}
.rd-placeholder .h{font-size:15px;font-weight:600;color:var(--rd-text-2);margin-bottom:6px;}
.rd-placeholder .coming{display:inline-block;font-size:10px;font-weight:600;color:var(--rd-accent);background:rgba(0,71,102,.08);border-radius:10px;padding:2px 8px;text-transform:uppercase;letter-spacing:.4px;margin-bottom:12px;}

/* Print (§7) — expand all pages, hide screen affordances. */
@media print {
    .rd-tabs, .rd-arrows, .rd-tab-right, .hideOnPrint { display: none !important; }
    .rd-deck { box-shadow: none; }
    .rd-deck-viewport { overflow: visible !important; }
    .rd-deck-track { transform: none !important; display: block !important; }
    .rd-page { min-width: 0 !important; width: 100% !important; padding: 12px 0 24px; border-top: 1px solid var(--rd-line-soft); page-break-inside: avoid; }
    .rd-page:first-child { border-top: 0; padding-top: 0; }
    .rd-page:not(.on) { visibility: visible !important; height: auto !important; padding-top: 12px !important; padding-bottom: 24px !important; }
}
</style>

<div class="rd-scope">

    {{-- ── Persistent header ────────────────────────────────────────── --}}
    <div class="rd-hdr">
        <div class="st">
            <div class="h">{{ $subject }}</div>
            <div class="prov">
                {{ $scope === 'strategy' ? __('stakeholder.header.strategy_report') : __('stakeholder.header.program_report') }}
                @if ($periodMeaning !== '') · {{ $periodMeaning }} @endif
                · {{ __('stakeholder.header.updated') }} {{ $updatedAt }}
            </div>
        </div>
        <div class="verdict">
            <div class="v"><span class="dot" style="background:{{ $verdictDotColor }}"></span>{{ $verdictLabel }}</div>
            <div class="src">{{ $verdictSource }}</div>
        </div>
    </div>

    {{-- ── Tab bar + period picker on ONE row (saves a full row of vertical
         space; picker sits with the view-mode controls it belongs with) ── --}}
    <div class="rd-tabs hideOnPrint">
        <button type="button" class="rd-tab on" data-page="0" onclick="rdGo(0)"><i class="fa fa-gauge-simple-high"></i> {{ __('stakeholder.tab.overview') }}</button>
        <button type="button" class="rd-tab" data-page="1" onclick="rdGo(1)"><i class="fa fa-diagram-project"></i> {{ __('stakeholder.tab.logic_model') }}</button>
        <button type="button" class="rd-tab" data-page="2" onclick="rdGo(2)"><i class="fa fa-people-arrows"></i> {{ __('stakeholder.tab.resources_coverage') }}</button>
        <button type="button" class="rd-tab" data-page="3" onclick="rdGo(3)"><i class="fa fa-compass"></i> {{ __('stakeholder.tab.impact_journey') }}</button>

        <div class="rd-tab-right">
            <div class="rd-picker" id="rdPicker">
                <button type="button" class="rd-picker-btn" onclick="rdTogglePicker(event)">
                    <i class="fa fa-calendar"></i>
                    <span class="rd-picker-q">{{ $presetName }}</span>
                    <span class="rd-picker-range">· {{ $period->from->setToUserTimezone()->format('M j') }} – {{ $period->to->setToUserTimezone()->format('M j, Y') }}</span>
                    <i class="fa fa-caret-down"></i>
                </button>
                <div class="rd-picker-menu" id="rdPickerMenu" hidden>
                    <a href="{{ $reportUrl }}?preset={{ ReportPeriod::PRESET_LAST_QUARTER }}"
                       class="rd-picker-opt @if ($period->preset === ReportPeriod::PRESET_LAST_QUARTER) on @endif">
                        <span class="l">{{ __('label.period_last_quarter') }}</span>
                        <span class="d">{{ __('stakeholder.period.default_hint') }}</span>
                    </a>
                    <a href="{{ $reportUrl }}?preset={{ ReportPeriod::PRESET_THIS_QUARTER }}"
                       class="rd-picker-opt @if ($period->preset === ReportPeriod::PRESET_THIS_QUARTER) on @endif">
                        <span class="l">{{ __('label.period_this_quarter') }}</span>
                        <span class="d">{{ __('stakeholder.period.in_progress_hint') }}</span>
                    </a>
                    <a href="{{ $reportUrl }}?preset={{ ReportPeriod::PRESET_NEXT_QUARTER }}"
                       class="rd-picker-opt @if ($period->preset === ReportPeriod::PRESET_NEXT_QUARTER) on @endif">
                        <span class="l">{{ __('label.period_next_quarter') }}</span>
                        <span class="d">{{ __('stakeholder.period.upcoming_hint') }}</span>
                    </a>
                    <div class="rd-picker-sep"></div>
                    <form method="GET" action="{{ $reportUrl }}" class="rd-picker-custom">
                        <input type="hidden" name="preset" value="{{ ReportPeriod::PRESET_CUSTOM }}">
                        <label class="rd-picker-cl">{{ __('label.period_custom') }}</label>
                        <div class="rd-picker-crow">
                            <input type="text" name="from" class="rd-picker-cinput periodPickerDate"
                                   placeholder="{{ __('label.period_from') }}"
                                   value="{{ $period->preset === ReportPeriod::PRESET_CUSTOM ? $period->from->setToUserTimezone()->formatDateForUser() : '' }}">
                            <span class="rd-picker-cdash">–</span>
                            <input type="text" name="to" class="rd-picker-cinput periodPickerDate"
                                   placeholder="{{ __('label.period_to') }}"
                                   value="{{ $period->preset === ReportPeriod::PRESET_CUSTOM ? $period->to->setToUserTimezone()->formatDateForUser() : '' }}">
                            <button type="submit" class="rd-picker-capply">{{ __('label.period_apply') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="rd-arrows">
                <button type="button" class="rd-arrow" id="rdPrev" onclick="rdGo(rdActive - 1)" aria-label="{{ __('stakeholder.nav.prev') }}"><i class="fa fa-chevron-left"></i></button>
                <button type="button" class="rd-arrow" id="rdNext" onclick="rdGo(rdActive + 1)" aria-label="{{ __('stakeholder.nav.next') }}"><i class="fa fa-chevron-right"></i></button>
            </div>
        </div>
    </div>

    {{-- ── Deck ─────────────────────────────────────────────────────── --}}
    <div class="rd-deck">
        <div class="rd-deck-viewport">
            <div class="rd-deck-track" id="rdTrack">

                {{-- ═══ Page 1 — Overview ═════════════════════════════ --}}
                <div class="rd-page on">
                    @include('reports::partials.stakeholder.page-overview', compact(
                        'completedCount', 'completedDelta', 'goalsOnTrack', 'goalsTotal',
                        'overdueCount', 'hoursLogged', 'needsAttn', 'logicModel', 'hasLM',
                        'goalsGroup', 'report', 'strategyUpdates', 'programUpdates',
                        'programRows'
                    ))
                </div>

                {{-- ═══ Page 2 — Logic Model read-out ═════════════════ --}}
                <div class="rd-page">
                    @include('reports::partials.stakeholder.page-lm', compact('logicModel', 'hasLM', 'report'))
                </div>

                {{-- ═══ Page 3 — Resources & Coverage ═════════════════ --}}
                <div class="rd-page">
                    @include('reports::partials.stakeholder.page-resources', compact('logicModel', 'hasLM', 'resourceSummary', 'report', 'scope', 'capacityAnalysis', 'programMeta', 'programChildMap', 'capacityByProgram'))
                </div>

                {{-- ═══ Page 4 — Impact Journey ═══════════════════════ --}}
                <div class="rd-page">
                    @include('reports::partials.stakeholder.page-impact-journey', compact('scope', 'logicModel', 'hasLM'))
                </div>

            </div>
        </div>
    </div>

</div>

<script>
/*
 * Report deck navigation. Vanilla JS — no Alpine, no jQuery dependency for the
 * core interaction. Supports: tab click, prev/next buttons, arrow keys,
 * horizontal swipe.
 */
(function () {
    if (window.__rdDeckInit) return;
    window.__rdDeckInit = true;

    window.rdActive = 0;
    window.rdCount = 4;

    // Per-user last-viewed page persists in localStorage so a refresh (and
    // returning to the report) lands you back where you were, not on the
    // Overview every time.
    var LS_PAGE = 'lt.stakeholderReport.activePage';

    window.rdGo = function (idx, opts) {
        if (idx < 0 || idx >= window.rdCount) return;
        window.rdActive = idx;

        var track = document.getElementById('rdTrack');
        if (!track) return;
        track.style.transform = 'translateX(' + (-100 * idx) + '%)';

        // Only the active page contributes to height (no dead space on short pages).
        var pages = track.querySelectorAll('.rd-page');
        pages.forEach(function (p, i) { p.classList.toggle('on', i === idx); });

        // Tab state.
        document.querySelectorAll('.rd-tab').forEach(function (btn) {
            btn.classList.toggle('on', parseInt(btn.dataset.page, 10) === idx);
        });

        // Arrow enable state.
        var prev = document.getElementById('rdPrev');
        var next = document.getElementById('rdNext');
        if (prev) prev.toggleAttribute('disabled', idx === 0);
        if (next) next.toggleAttribute('disabled', idx === window.rdCount - 1);

        // Persist unless the caller says otherwise (used on initial restore
        // so we don't rewrite the value with the very value we just read).
        if (!opts || opts.persist !== false) {
            try { localStorage.setItem(LS_PAGE, String(idx)); } catch (e) {}
        }
    };

    // Arrow keys — only when focus isn't in a text input.
    document.addEventListener('keydown', function (e) {
        var t = e.target;
        if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable)) return;
        if (e.key === 'ArrowLeft')  window.rdGo(window.rdActive - 1);
        if (e.key === 'ArrowRight') window.rdGo(window.rdActive + 1);
    });

    // Swipe (touch). Threshold 60px so accidental drags don't switch pages.
    var deck = document.querySelector('.rd-deck-viewport');
    if (deck) {
        var startX = 0, startY = 0, tracking = false;
        deck.addEventListener('touchstart', function (e) {
            if (e.touches.length !== 1) return;
            startX = e.touches[0].clientX; startY = e.touches[0].clientY; tracking = true;
        }, { passive: true });
        deck.addEventListener('touchend', function (e) {
            if (!tracking) return; tracking = false;
            var dx = e.changedTouches[0].clientX - startX;
            var dy = e.changedTouches[0].clientY - startY;
            if (Math.abs(dx) < 60 || Math.abs(dy) > Math.abs(dx)) return;
            window.rdGo(window.rdActive + (dx < 0 ? 1 : -1));
        }, { passive: true });
    }

    // Initial state — restore the last-viewed page if persisted, else Overview.
    var initialPage = 0;
    try {
        var saved = parseInt(localStorage.getItem(LS_PAGE) || '', 10);
        if (!isNaN(saved) && saved >= 0 && saved < window.rdCount) initialPage = saved;
    } catch (e) {}
    window.rdGo(initialPage, { persist: false });

    // Compact period-picker dropdown: toggle open, dismiss on outside click.
    window.rdTogglePicker = function (e) {
        if (e) e.stopPropagation();
        var menu = document.getElementById('rdPickerMenu');
        if (!menu) return;
        menu.toggleAttribute('hidden');
    };
    document.addEventListener('click', function (e) {
        var picker = document.getElementById('rdPicker');
        if (!picker || picker.contains(e.target)) return;
        var menu = document.getElementById('rdPickerMenu');
        if (menu && !menu.hasAttribute('hidden')) menu.setAttribute('hidden', '');
    });

    // Wire the datepicker to the two custom-range inputs (same helper Marcel's
    // periodpicker uses). Only if jQuery + the helper are present.
    if (typeof jQuery !== 'undefined' && jQuery.fn.datepicker && window.leantime?.dateHelper) {
        jQuery('.rd-picker-cinput').datepicker({
            dateFormat: window.leantime.dateHelper.getFormatFromSettings('dateformat', 'jquery')
        });
    }

    // KPI drill toggle — click a cell with .has-detail to open its drill list.
    // Click elsewhere closes it. Only one open at a time.
    document.addEventListener('click', function (e) {
        var cell = e.target.closest('.rd-kcell.has-detail');
        // Clicked inside the open drill? Let the click through (don't close).
        if (e.target.closest('.rd-kcell.has-detail .kdrill')) return;

        // Close every other open drill first (single-open behavior).
        document.querySelectorAll('.rd-kcell.has-detail.open').forEach(function (c) {
            if (c !== cell) c.classList.remove('open');
        });

        // Toggle the clicked cell (if any).
        if (cell) cell.classList.toggle('open');
    });
})();
</script>

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
        'ontrack' => '#3E937A',
        'atrisk'  => '#C09035',
        'off'     => '#C2295B',
        default   => '#9CA3AF',
    };
    $completedCount = (int) ($stats['completed'] ?? 0);
    $overdueCount   = (int) ($stats['overdue'] ?? 0);
    $goalsOnTrack   = (int) ($stats['goalsOnTrack'] ?? 0);
    $goalsTotal     = (int) ($stats['goalsTotal'] ?? 0);
    $hoursLogged    = (float) ($stats['hoursLogged'] ?? 0);
    $completedDelta = (int) ($deltas['completedDelta'] ?? 0);
    $hasLM          = $logicModel !== null;

    // Semantic period label — the "why this period" chip in the header sub-line.
    // Board audiences care WHY the report is showing Q2 (because it's last closed)
    // more than the raw date range, which repeats below.
    $periodMeaning = match ($period->preset) {
        ReportPeriod::PRESET_LAST_QUARTER => __('stakeholder.period.last_closed'),
        ReportPeriod::PRESET_THIS_QUARTER => __('stakeholder.period.in_progress'),
        ReportPeriod::PRESET_NEXT_QUARTER => __('stakeholder.period.upcoming'),
        ReportPeriod::PRESET_CUSTOM       => __('stakeholder.period.custom'),
        default                           => '',
    };

    // Compact quarter label ("Q3 2026") — for the picker button and header chip.
    $qLabel = 'Q'.(int) ceil((int) $period->from->setToUserTimezone()->format('n') / 3).' '.$period->from->setToUserTimezone()->format('Y');

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
.rd-hdr{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:20px;align-items:start;padding:18px 22px;background:var(--rd-panel);border-radius:var(--rd-r-sm);box-shadow:var(--rd-sh-sm);margin-bottom:14px;}
.rd-hdr .st{min-width:0;}
.rd-hdr .st .h{font-size:20px;font-weight:600;line-height:1.2;color:var(--rd-text-1);}
.rd-hdr .st .prov{font-size:12px;color:var(--rd-text-3);margin-top:4px;}
.rd-hdr .verdict{text-align:right;min-width:0;}
.rd-hdr .verdict .v{display:inline-flex;align-items:center;gap:8px;font-size:15px;font-weight:600;color:var(--rd-text-1);}
.rd-hdr .verdict .v .dot{width:10px;height:10px;border-radius:50%;flex:none;}
.rd-hdr .verdict .src{font-size:11.5px;color:var(--rd-text-3);margin-top:4px;font-weight:400;}

/* Global controls — sit above the tab bar. Period picker on the right; view
   toggle (Board ↔ Report) implied by the "back to board" button in the page
   header for now. */
.rd-globalbar{display:flex;align-items:center;gap:10px;margin-bottom:11px;flex-wrap:wrap;}
.rd-globalbar .fill{flex:1;}

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
.rd-arrows{margin-left:auto;display:flex;gap:4px;}
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

/* Page-1 KPI band — 4 equal cells, honest big numbers. */
.rd-kpi{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px;}
.rd-kcell{padding:14px 16px;border-radius:var(--rd-r-xs);background:var(--rd-bg);}
.rd-kcell .l{font-size:11.5px;color:var(--rd-text-2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;}
.rd-kcell .v{font-size:26px;font-weight:600;letter-spacing:-.5px;line-height:1;color:var(--rd-text-1);}
.rd-kcell .v small{font-size:14px;color:var(--rd-text-3);font-weight:400;margin-left:2px;}
.rd-kcell .d{font-size:11.5px;color:var(--rd-text-3);margin-top:5px;}
.rd-kcell .d.up{color:var(--rd-ok);}
.rd-kcell .d.down{color:var(--rd-danger);}
.rd-kcell.danger .v{color:var(--rd-danger);}

/* Section container inside a page. */
.rd-section{margin-top:20px;}
.rd-section:first-child{margin-top:0;}
.rd-section .hd{display:flex;align-items:baseline;gap:10px;margin-bottom:10px;}
.rd-section .hd .t{font-size:14px;font-weight:600;color:var(--rd-text-1);}
.rd-section .hd .sub{font-size:12px;color:var(--rd-text-3);}
.rd-empty{padding:22px;text-align:center;color:var(--rd-text-3);font-size:13px;background:var(--rd-bg);border-radius:var(--rd-r-xs);}

/* Placeholder for pages not yet built. Removed as each page lands. */
.rd-placeholder{padding:38px 24px;text-align:center;color:var(--rd-text-3);font-size:13px;line-height:1.5;background:repeating-linear-gradient(135deg,#fafbfb,#fafbfb 8px,#f4f6f7 8px,#f4f6f7 16px);border-radius:var(--rd-r-xs);}
.rd-placeholder .h{font-size:15px;font-weight:600;color:var(--rd-text-2);margin-bottom:6px;}
.rd-placeholder .coming{display:inline-block;font-size:10px;font-weight:600;color:var(--rd-accent);background:rgba(0,71,102,.08);border-radius:10px;padding:2px 8px;text-transform:uppercase;letter-spacing:.4px;margin-bottom:12px;}

/* Print (§7) — expand all pages, hide screen affordances. */
@media print {
    .rd-globalbar, .rd-tabs, .rd-arrows, .hideOnPrint { display: none !important; }
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
            <div class="h">{{ $tpl->escape($subject) }}</div>
            <div class="prov">
                {{ $scope === 'strategy' ? __('stakeholder.header.strategy_report') : __('stakeholder.header.program_report') }}
                · {{ $qLabel }}
                @if ($periodMeaning !== '') · {{ $periodMeaning }} @endif
                · {{ __('stakeholder.header.updated') }} {{ $updatedAt }}
            </div>
        </div>
        <div class="verdict">
            <div class="v"><span class="dot" style="background:{{ $verdictDotColor }}"></span>{{ $verdictLabel }}</div>
            <div class="src">{{ $verdictSource }}</div>
        </div>
    </div>

    {{-- ── Global controls: compact period picker (single button → dropdown) ── --}}
    <div class="rd-globalbar hideOnPrint">
        <span class="fill"></span>

        <div class="rd-picker" id="rdPicker">
            <button type="button" class="rd-picker-btn" onclick="rdTogglePicker(event)">
                <i class="fa fa-calendar"></i>
                <span class="rd-picker-q">{{ $qLabel }}</span>
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
    </div>

    {{-- ── Tab bar (screen-only, expanded in print) ─────────────────── --}}
    <div class="rd-tabs hideOnPrint">
        <button type="button" class="rd-tab on" data-page="0" onclick="rdGo(0)"><i class="fa fa-gauge-simple-high"></i> {{ __('stakeholder.tab.overview') }}</button>
        <button type="button" class="rd-tab" data-page="1" onclick="rdGo(1)"><i class="fa fa-diagram-project"></i> {{ __('stakeholder.tab.logic_model') }}</button>
        <button type="button" class="rd-tab" data-page="2" onclick="rdGo(2)"><i class="fa fa-people-arrows"></i> {{ __('stakeholder.tab.resources_coverage') }}</button>
        <button type="button" class="rd-tab" data-page="3" onclick="rdGo(3)"><i class="fa fa-sitemap"></i> {{ __('stakeholder.tab.programs') }} @if (count($programRows) > 0) <span class="ct">{{ count($programRows) }}</span> @endif</button>
        <div class="rd-arrows">
            <button type="button" class="rd-arrow" id="rdPrev" onclick="rdGo(rdActive - 1)" aria-label="{{ __('stakeholder.nav.prev') }}"><i class="fa fa-chevron-left"></i></button>
            <button type="button" class="rd-arrow" id="rdNext" onclick="rdGo(rdActive + 1)" aria-label="{{ __('stakeholder.nav.next') }}"><i class="fa fa-chevron-right"></i></button>
        </div>
    </div>

    {{-- ── Deck ─────────────────────────────────────────────────────── --}}
    <div class="rd-deck">
        <div class="rd-deck-viewport">
            <div class="rd-deck-track" id="rdTrack">

                {{-- ═══ Page 1 — Overview ═════════════════════════════ --}}
                <div class="rd-page on">
                    <div class="rd-kpi">
                        <div class="rd-kcell">
                            <div class="l">{{ __('stakeholder.kpi.completed') }}</div>
                            <div class="v">{{ $completedCount }}</div>
                            @if ($completedDelta !== 0)
                                <div class="d {{ $completedDelta > 0 ? 'up' : 'down' }}">
                                    {{ $completedDelta > 0 ? '▲ +' : '▼ ' }}{{ $completedDelta }} {{ __('stakeholder.kpi.vs_prior') }}
                                </div>
                            @else
                                <div class="d">{{ __('stakeholder.kpi.same_as_prior') }}</div>
                            @endif
                        </div>
                        <div class="rd-kcell">
                            <div class="l">{{ __('stakeholder.kpi.goals_on_track') }}</div>
                            <div class="v">{{ $goalsOnTrack }}<small>/{{ $goalsTotal }}</small></div>
                            <div class="d">{{ __('stakeholder.kpi.strategic_goals') }}</div>
                        </div>
                        <div class="rd-kcell @if ($overdueCount > 0) danger @endif">
                            <div class="l">{{ __('stakeholder.kpi.overdue') }}</div>
                            <div class="v">{{ $overdueCount }}</div>
                            <div class="d">{{ __('stakeholder.kpi.milestones') }}</div>
                        </div>
                        <div class="rd-kcell">
                            <div class="l">{{ __('stakeholder.kpi.hours_logged') }}</div>
                            <div class="v">{{ number_format($hoursLogged, $hoursLogged >= 100 ? 0 : 1) }}<small>h</small></div>
                            <div class="d">{{ __('stakeholder.kpi.this_period') }}</div>
                        </div>
                    </div>

                    <div class="rd-placeholder">
                        <div class="coming">{{ __('stakeholder.next_phase') }}</div>
                        <div class="h">{{ __('stakeholder.overview.coming_title') }}</div>
                        {{ __('stakeholder.overview.coming_hint') }}
                    </div>
                </div>

                {{-- ═══ Page 2 — Logic Model read-out ═════════════════ --}}
                <div class="rd-page">
                    @if (! $hasLM)
                        <div class="rd-empty">{{ __('stakeholder.lm.no_canvas') }}</div>
                    @else
                        <div class="rd-placeholder">
                            <div class="coming">{{ __('stakeholder.next_phase') }}</div>
                            <div class="h">{{ __('stakeholder.lm.coming_title') }}</div>
                            {{ __('stakeholder.lm.coming_hint') }}
                        </div>
                    @endif
                </div>

                {{-- ═══ Page 3 — Resources & Coverage ═════════════════ --}}
                <div class="rd-page">
                    <div class="rd-placeholder">
                        <div class="coming">{{ __('stakeholder.p3_status') }}</div>
                        <div class="h">{{ __('stakeholder.rc.coming_title') }}</div>
                        {{ __('stakeholder.rc.coming_hint') }}
                    </div>
                </div>

                {{-- ═══ Page 4 — Programs & Narrative ═════════════════ --}}
                <div class="rd-page">
                    @if (count($programRows) === 0 && $scope === 'strategy')
                        <div class="rd-empty">{{ __('stakeholder.programs.none') }}</div>
                    @else
                        <div class="rd-placeholder">
                            <div class="coming">{{ __('stakeholder.next_phase') }}</div>
                            <div class="h">{{ __('stakeholder.programs.coming_title') }}</div>
                            {{ __('stakeholder.programs.coming_hint') }}
                        </div>
                    @endif
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

    window.rdGo = function (idx) {
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

    // Initial state.
    window.rdGo(0);

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
})();
</script>

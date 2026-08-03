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


@php
    // Prefer a $rdDark boolean passed by the caller/composer; fall back to the
    // Theme service only when it isn't supplied, so the view isn't required to
    // do a container lookup.
    $rdDark = $rdDark ?? (app()->make(\Leantime\Core\UI\Theme::class)->getColorMode() === 'dark');
@endphp
<div class="rd-scope @if ($rdDark) rd-dark @endif">

    {{-- ── Persistent document header (doc-shell) ───────────────────────
         breadcrumb + subject switcher + status verdict + actions live in the
         card; the plugin collapses the shared teal pageheader (body.report-doc)
         so the report reads as one document. Switcher + actions degrade safely
         when the caller doesn't pass switchableSubjects / projectId. --}}
    <div class="rd-hdr">
        <div class="st">
            {{-- One report = ONE subject; Task-view breadcrumb flow
                 ("Report // {subject}", like "To-Dos // All To-Dos"). The
                 scope label and period meaning are redundant here — the
                 breadcrumb says Report, the period picker below owns the
                 period context — so the under-text keeps only freshness. --}}
            <h1 class="h"><span class="crumb-type">{{ __('stakeholder.header.crumb_report') }}</span> <span class="crumb-sep" aria-hidden="true">/</span> {{ $subject }}</h1>
            <div class="prov">{{ __('stakeholder.header.updated') }} {{ $updatedAt }}</div>
        </div>
        <div class="verdict">
            {{-- Provenance ("set 1 month ago · overrides metrics") is secondary:
                 it lives in the tooltip so the right side stays one balanced,
                 vertically-centered row with the actions menu. --}}
            <div class="v" data-tippy-content="{{ $verdictSource }}"><span class="dot" style="background:{{ $verdictDotColor }}"></span>{{ $verdictLabel }}</div>
        </div>
        @if (! empty($projectId ?? null))
            <div class="rd-actions">
                @include('reports::partials.stakeholder.actionsMenu', [
                    'scope' => $scope,
                    'projectId' => $projectId,
                    'verdictOverride' => $verdictOverride ?? null,
                ])
            </div>
        @endif
    </div>

    {{-- ── Tab bar + period picker on ONE row (saves a full row of vertical
         space; picker sits with the view-mode controls it belongs with) ── --}}
    <div class="lt-tabs lt-tabs--floating hideOnPrint">
        {{-- Framed segmented tab group (mirrors the global .tabs nav): the tabs
             sit in one outlined container so they read as a connected control,
             the active one a white segment inside it. --}}
        <div class="lt-tabs-group" role="tablist" aria-label="{{ __('stakeholder.tab.overview') }}">
            <button type="button" class="lt-tab on" data-page="0" onclick="rdGo(0)"><i class="fa fa-gauge-simple-high"></i> {{ __('stakeholder.tab.overview') }}</button>
            <button type="button" class="lt-tab" data-page="1" onclick="rdGo(1)"><i class="fa fa-diagram-project"></i> {{ __('stakeholder.tab.logic_model') }}</button>
            <button type="button" class="lt-tab" data-page="2" onclick="rdGo(2)"><i class="fa fa-people-arrows"></i> {{ __('stakeholder.tab.resources_coverage') }}</button>
            <button type="button" class="lt-tab" data-page="3" onclick="rdGo(3)"><i class="fa fa-compass"></i> {{ __('stakeholder.tab.impact_journey') }}</button>
        </div>

        <div class="lt-tabs-actions">
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
                    @include('reports::partials.stakeholder.page-resources', compact('logicModel', 'hasLM', 'resourceSummary', 'report', 'scope', 'capacityAnalysis', 'programMeta', 'programChildMap', 'capacityByProgram') + ['projectId' => $projectId ?? null])
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
        document.querySelectorAll('.lt-tab').forEach(function (btn) {
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

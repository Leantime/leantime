{{--
    Stakeholder Report — Page 3 (Resources)

    Pure resource story: aggregate → per-project breakdown → gaps & risks.
    Reads ResourcesGateway (ResourceSummary value object). Three sections:

      1. Aggregate cards       — the 30-second overview (People / Budget / Deps)
      2. Per-project breakdown — the "where's it going" drill-down, one row per project
      3. Resource gaps & risks — plain-English callouts of imbalances that need attention

    The old LM-item→program linkage matrix moved off this page — that question
    belongs on the Logic Model read-out (Page 2), where board readers have
    context for what "linkage" means.

    Sizing note: board audiences skew older and reports often get projected or
    printed. Cards and numbers are intentionally large — this is the default,
    not an accessibility toggle.

    Vars in:
      $resourceSummary   null | \Leantime\Core\Resources\Models\ResourceSummary
      $report            array from ReportEngine::buildReport (has .summaries[] with id/name)
      $scope             'strategy' | 'program'
      $logicModel, $hasLM — passed through but not used here anymore
--}}


@php
    // Does this strategy/program actually have authored resource data?
    // $resourceSummary is non-null whenever the provider (PgmPro) is installed —
    // even with nothing authored — so a null check alone leaves a wall of empty
    // "—" cards. Detect "installed but empty" here to drive the onboarding empty
    // state and suppress the noisy empty sections below. (For a strategy the
    // strategy's own board is always empty by design; data rolls up from its
    // programs — so this is true whenever ANY program in scope has authored it.)
    // Use the aggregates ResourceSummary already computed — no view-level loop
    // over people/allocations. "Any active person" (allocation or capacity > 0)
    // reduces to totalCapacity/totalAllocated being > 0.
    $hasResourceData = $resourceSummary !== null && (
        $resourceSummary->totalCapacity > 0
        || $resourceSummary->totalAllocated > 0
        || $resourceSummary->totalBudgeted > 0
        || count($resourceSummary->dependencies) > 0
    );
@endphp

@if ($hasResourceData)
    {{-- Hours / Days unit toggle. The server always renders hours (source of
         truth); JS swaps any element with `data-hours` to a days display (÷ 8)
         when the toggle is set. Preference stored in localStorage. --}}
    <div class="p3-unit-toggle" data-lt-unit-toggle>
        <span class="p3-unit-lbl">{{ __('stakeholder.rc.unit.show') }}</span>
        <div class="p3-unit-pill">
            <button type="button" class="p3-unit-btn is-active" data-unit="hours">{{ __('stakeholder.rc.unit.hours') }}</button>
            <button type="button" class="p3-unit-btn" data-unit="days">{{ __('stakeholder.rc.unit.days') }}</button>
        </div>
    </div>
@endif

{{-- ── Resources summary from ResourcesGateway ────────────────────── --}}
<div class="p3-sec">
    <div class="p3-sec-hd">
        <span class="l">{{ __('stakeholder.rc.res_label') }}</span>
        <span class="s">{{ __('stakeholder.rc.res_sub_live') }}</span>
    </div>

    @if ($resourceSummary === null)
        {{-- No ResourcesGateway registered (plugin not installed / disabled). --}}
        <div class="p3-res-strip">
            <div class="icn"><i class="fa fa-people-arrows"></i></div>
            <div class="cnt">
                <div class="h">{{ __('stakeholder.rc.no_provider_title') }}</div>
                <div class="d">{{ __('stakeholder.rc.no_provider_hint') }}</div>
            </div>
        </div>
    @elseif (! $hasResourceData)
        {{-- Provider installed, nothing authored yet. Resource data always comes
             from the program(s) — a strategy's own board stays empty by design —
             so guide the reader to the next real action rather than showing a
             wall of empty cards and sections. Three cases:
               • program report            → set up that program's allocation
               • strategy with no programs → create a program first
               • strategy with programs    → open one and add resources --}}
        @php
            $programCount = count($programMeta ?? []);
            if (($scope ?? '') === 'program') {
                $emptyTitle    = __('stakeholder.rc.empty_title');
                $emptyHint     = __('stakeholder.rc.empty_hint_program');
                $emptyCtaHref  = BASE_URL.'/pgmPro/resourceAllocation';
                $emptyCtaLabel = __('stakeholder.rc.empty_cta');
            } elseif ($programCount === 0) {
                $emptyTitle    = __('stakeholder.rc.empty_noprog_title');
                $emptyHint     = __('stakeholder.rc.empty_hint_noprog');
                $emptyCtaHref  = (int) ($projectId ?? 0) > 0 ? BASE_URL.'/projects/newProject?parent='.(int) $projectId : null;
                $emptyCtaLabel = __('stakeholder.rc.empty_cta_create_program');
            } else {
                $emptyTitle    = __('stakeholder.rc.empty_title');
                $emptyHint     = __('stakeholder.rc.empty_hint_strategy');
                $emptyCtaHref  = null;
                $emptyCtaLabel = null;
            }
        @endphp
        <div class="p3-res-strip">
            <div class="icn"><i class="fa fa-people-arrows"></i></div>
            <div class="cnt">
                <div class="h">{{ $emptyTitle }}</div>
                <div class="d">{{ $emptyHint }}</div>
            </div>
            @if ($emptyCtaHref !== null)
                <a href="{{ $emptyCtaHref }}" class="p3-res-cta">{{ $emptyCtaLabel }} <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
            @endif
        </div>
    @else
        @php
            $people = $resourceSummary->people;
            $budget = $resourceSummary->budget;
            $dependencies = $resourceSummary->dependencies;
            $capacityPct = $resourceSummary->capacityUtilization();
            $budgetPct = $resourceSummary->budgetUtilization();
            $totalBudgeted = (float) $resourceSummary->totalBudgeted;
            $totalSpent = (float) $resourceSummary->totalSpent;

            $activePeopleCount = 0;
            foreach ($people as $p) {
                $anyAlloc = false;
                foreach ($p->allocations as $hrs) { if ((float) $hrs > 0) { $anyAlloc = true; break; } }
                if ($anyAlloc || $p->capacity > 0) $activePeopleCount++;
            }

            $atRiskBudget = 0;
            foreach ($budget as $line) {
                if ($line->budgeted > 0 && ($line->spent / $line->budgeted) >= 0.9) {
                    $atRiskBudget++;
                }
            }

            $depConfirmed = 0;
            $depTentative = 0;
            foreach ($dependencies as $d) {
                if ($d->confirmed) $depConfirmed++;
                else $depTentative++;
            }

            $moneyFmt = function ($n) {
                $n = (float) $n;
                if ($n >= 1000000) return '$'.number_format($n / 1000000, 1).'M';
                if ($n >= 1000) return '$'.number_format($n / 1000, $n >= 10000 ? 0 : 1).'K';
                return '$'.number_format($n, 0);
            };
        @endphp

        <div class="p3-res-grid">
            {{-- People --}}
            <div class="p3-rcard @if ($activePeopleCount === 0) empty @endif">
                <div class="rhead">
                    <div class="ricn"><i class="fa fa-users"></i></div>
                    <div class="rlbl">{{ __('stakeholder.rc.res_people') }}</div>
                </div>
                <div class="rv">{{ $activePeopleCount }}<small>{{ __('stakeholder.rc.res_people_unit') }}</small></div>
                @if ($resourceSummary->totalCapacity > 0)
                    <div class="rsub">
                        <strong>{{ (int) $capacityPct }}%</strong> {{ __('stakeholder.rc.res_capacity_used') }}
                        <span class="muted">· {{ round($resourceSummary->totalAllocated) }} / {{ round($resourceSummary->totalCapacity) }}h {{ __('stakeholder.rc.res_hours_weekly') }}</span>
                    </div>
                    <div class="bar ok"><i style="width:{{ min(100, (int) $capacityPct) }}%;"></i></div>
                @else
                    <div class="rsub muted">{{ __('stakeholder.rc.res_no_capacity') }}</div>
                @endif
            </div>

            {{-- Budget --}}
            <div class="p3-rcard @if ($totalBudgeted === 0.0) empty @endif">
                <div class="rhead">
                    <div class="ricn"><i class="fa fa-coins"></i></div>
                    <div class="rlbl">{{ __('stakeholder.rc.res_budget') }}</div>
                </div>
                @if ($totalBudgeted > 0)
                    <div class="rv">{{ $moneyFmt($totalSpent) }}<small>/ {{ $moneyFmt($totalBudgeted) }}</small></div>
                    <div class="rsub">
                        <strong>{{ (int) $budgetPct }}%</strong> {{ __('stakeholder.rc.res_spent') }}
                        @if ($atRiskBudget > 0) · <span class="risk">{{ sprintf(__('stakeholder.rc.res_at_risk'), $atRiskBudget) }}</span> @endif
                    </div>
                    <div class="bar spend @if ($budgetPct >= 100) over @elseif ($atRiskBudget > 0) at-risk @endif"><i style="width:{{ min(100, (int) $budgetPct) }}%;"></i></div>
                @else
                    <div class="rv">—</div>
                    <div class="rsub muted">{{ __('stakeholder.rc.res_no_budget') }}</div>
                @endif
            </div>

            {{-- Dependencies --}}
            <div class="p3-rcard @if (count($dependencies) === 0) empty @endif">
                <div class="rhead">
                    <div class="ricn"><i class="fa fa-handshake"></i></div>
                    <div class="rlbl">{{ __('stakeholder.rc.res_deps') }}</div>
                </div>
                @if (count($dependencies) > 0)
                    <div class="rv">{{ $depConfirmed }}<small>/ {{ count($dependencies) }} {{ __('stakeholder.rc.res_deps_confirmed') }}</small></div>
                    <div class="rtail">
                        @if ($depConfirmed > 0)<span class="rp"><span class="dd ok"></span>{{ sprintf(__('stakeholder.rc.res_deps_confirmed_n'), $depConfirmed) }}</span>@endif
                        @if ($depTentative > 0)<span class="rp"><span class="dd warn"></span>{{ sprintf(__('stakeholder.rc.res_deps_tentative_n'), $depTentative) }}</span>@endif
                    </div>
                @else
                    <div class="rv">—</div>
                    <div class="rsub muted">{{ __('stakeholder.rc.res_no_deps') }}</div>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- ── Per-project breakdown ──────────────────────────────────────────
     The "where's it going?" table. At strategy scope with 5+ programs the
     aggregate cards above are the 30-second read; this table answers the
     first drill-down question: which program is hot, which is idle.
     Skipped when there are 0-1 projects (aggregate is the same as the
     breakdown, no point). --}}
@if ($resourceSummary !== null && $hasResourceData && count($resourceSummary->projectIds) > 1)
    @php
        // Build per-project name lookup from ReportEngine summaries.
        $projectNames = [];
        $projectTypes = [];
        foreach (($report['summaries'] ?? []) as $s) {
            $projectNames[(int) ($s->id ?? 0)] = (string) ($s->name ?? '');
            $projectTypes[(int) ($s->id ?? 0)] = (string) ($s->type ?? 'project');
        }

        // Aggregate per-project people & budget from ResourceSummary.
        $perProject = [];
        foreach ($resourceSummary->projectIds as $pid) {
            $pid = (int) $pid;
            $perProject[$pid] = [
                'id'            => $pid,
                'name'          => $projectNames[$pid] ?? ('#'.$pid),
                'type'          => $projectTypes[$pid] ?? 'project',
                'peopleCount'   => 0,
                'allocatedHrs'  => 0.0,
                'budgeted'      => 0.0,
                'spent'         => 0.0,
                'hasBudget'     => false,
                'hasPeople'     => false,
            ];
        }

        foreach ($resourceSummary->people as $person) {
            foreach ($person->allocations as $pid => $hrs) {
                $pid = (int) $pid;
                if (! isset($perProject[$pid])) continue;
                if ((float) $hrs > 0) {
                    $perProject[$pid]['peopleCount']++;
                    $perProject[$pid]['allocatedHrs'] += (float) $hrs;
                    $perProject[$pid]['hasPeople'] = true;
                }
            }
        }

        foreach ($resourceSummary->budget as $line) {
            $pid = (int) $line->projectId;
            if (! isset($perProject[$pid])) continue;
            $perProject[$pid]['budgeted'] += (float) $line->budgeted;
            $perProject[$pid]['spent'] += (float) $line->spent;
            if ($line->budgeted > 0) $perProject[$pid]['hasBudget'] = true;
        }

        // Drop container-only rows (strategy + program IDs that ride along in
        // the resource walk but aren't real work projects) so the breakdown
        // table only shows leaf projects — same filter idea as the capacity
        // analyzer, keeps the two sections consistent.
        $perProject = array_filter($perProject, fn ($r) => isset($projectNames[$r['id']]));

        // Sort: authored projects first (any people or budget), then by
        // budget spend % descending so the hottest surface first.
        uasort($perProject, function ($a, $b) {
            $aAny = $a['hasPeople'] || $a['hasBudget'];
            $bAny = $b['hasPeople'] || $b['hasBudget'];
            if ($aAny !== $bAny) return $aAny ? -1 : 1;
            $aPct = $a['budgeted'] > 0 ? $a['spent'] / $a['budgeted'] : -1;
            $bPct = $b['budgeted'] > 0 ? $b['spent'] / $b['budgeted'] : -1;
            if ($aPct !== $bPct) return $bPct <=> $aPct;
            return $b['peopleCount'] <=> $a['peopleCount'];
        });

        // Program-rollup: at strategy scope with a programChildMap, aggregate
        // per-project rows into per-program rows. Row rendering uses <details>
        // for native expand/collapse — child project rows show up inline.
        $useProgramRollup = ($scope ?? '') === 'strategy'
            && ! empty($programChildMap)
            && count($programMeta ?? []) >= 1;

        $perProgram = [];
        if ($useProgramRollup) {
            foreach ($programMeta as $progId => $progInfo) {
                $childIds = $programChildMap[$progId] ?? [];
                $peopleSet = []; // Union of person ids across children (unique count).
                $hrs = 0.0;
                $budgeted = 0.0;
                $spent = 0.0;

                foreach ($resourceSummary->people as $person) {
                    $touched = false;
                    foreach ($childIds as $cid) {
                        $h = (float) ($person->allocations[$cid] ?? 0.0);
                        if ($h > 0) {
                            $touched = true;
                            $hrs += $h;
                        }
                    }
                    if ($touched) $peopleSet[$person->itemId] = 1;
                }

                foreach ($childIds as $cid) {
                    $r = $perProject[$cid] ?? null;
                    if ($r === null) continue;
                    $budgeted += $r['budgeted'];
                    $spent    += $r['spent'];
                }

                $children = array_values(array_filter(
                    array_map(fn ($cid) => $perProject[$cid] ?? null, $childIds),
                    fn ($r) => $r !== null,
                ));
                usort($children, function ($a, $b) {
                    $aPct = $a['budgeted'] > 0 ? $a['spent'] / $a['budgeted'] : -1;
                    $bPct = $b['budgeted'] > 0 ? $b['spent'] / $b['budgeted'] : -1;
                    return $bPct <=> $aPct;
                });

                $perProgram[$progId] = [
                    'id'          => $progId,
                    'name'        => $progInfo['name'],
                    'peopleCount' => count($peopleSet),
                    'hrs'         => $hrs,
                    'budgeted'    => $budgeted,
                    'spent'       => $spent,
                    'hasBudget'   => $budgeted > 0,
                    'hasPeople'   => count($peopleSet) > 0,
                    'children'    => $children,
                    'childCount'  => count($children),
                ];
            }

            // Sort programs same way projects are sorted.
            uasort($perProgram, function ($a, $b) {
                $aAny = $a['hasPeople'] || $a['hasBudget'];
                $bAny = $b['hasPeople'] || $b['hasBudget'];
                if ($aAny !== $bAny) return $aAny ? -1 : 1;
                $aPct = $a['budgeted'] > 0 ? $a['spent'] / $a['budgeted'] : -1;
                $bPct = $b['budgeted'] > 0 ? $b['spent'] / $b['budgeted'] : -1;
                if ($aPct !== $bPct) return $bPct <=> $aPct;
                return $b['peopleCount'] <=> $a['peopleCount'];
            });
        }

        // Helper closure to render one budget cell (used by both program row and
        // child project row so the two levels stay visually aligned).
        $renderRow = function ($row, $isChild = false) use ($moneyFmt) {
            return $row; // (no-op; rendering done inline below to keep Blade access simple)
        };
    @endphp

    <div class="p3-sec">
        <div class="p3-sec-hd">
            <span class="l">{{ __('stakeholder.rc.bd_label') }}</span>
            <span class="s">
                @if ($useProgramRollup)
                    {!! sprintf(__('stakeholder.rc.bd_sub_program'), count($perProgram)) !!}
                @else
                    {!! sprintf(__('stakeholder.rc.bd_sub'), count($perProject)) !!}
                @endif
            </span>
        </div>

        <div class="p3-bd">
            <div class="p3-bd-row head">
                <div class="p3-bd-cell">
                    @if ($useProgramRollup)
                        {{ __('stakeholder.rc.bd_col_program') }}
                    @else
                        {{ __('stakeholder.rc.bd_col_project') }}
                    @endif
                </div>
                <div class="p3-bd-cell">{{ __('stakeholder.rc.bd_col_people') }}</div>
                <div class="p3-bd-cell">{{ __('stakeholder.rc.bd_col_budget') }}</div>
                <div class="p3-bd-cell">{{ __('stakeholder.rc.bd_col_hours') }}</div>
            </div>

            @if ($useProgramRollup)
                {{-- One row per program with its rolled-up numbers. Click to see
                     the child projects with their basic budget/people numbers.
                     The DEEP capacity-vs-demand story stays on the individual
                     Program report — this expand is just the list. --}}
                @foreach ($perProgram as $prog)
                    @php
                        $pSpendPct = $prog['budgeted'] > 0 ? min(999, ($prog['spent'] / $prog['budgeted']) * 100) : 0;
                        $pSpendClass = 'ok';
                        if ($pSpendPct >= 100) $pSpendClass = 'over';
                        elseif ($pSpendPct >= 90) $pSpendClass = 'at-risk';
                    @endphp
                    <details class="p3-bd-program">
                        <summary class="p3-bd-row program-row">
                            <div class="p3-bd-cell name-cell">
                                <div class="expand-chevron"><i class="fa fa-chevron-right"></i></div>
                                <div>
                                    <div class="name">{{ $prog['name'] }}</div>
                                    <div class="type">{{ __('projectType.program') }} · {{ sprintf(__('stakeholder.rc.bd_n_projects'), $prog['childCount']) }}</div>
                                </div>
                            </div>

                            <div class="p3-bd-cell" data-label="{{ __('stakeholder.rc.bd_col_people') }}">
                                @if ($prog['hasPeople'])
                                    <div class="num">{{ $prog['peopleCount'] }}<small>{{ __('stakeholder.rc.res_people_unit') }}</small></div>
                                @else
                                    <div class="zero">{{ __('stakeholder.rc.bd_none') }}</div>
                                @endif
                            </div>

                            <div class="p3-bd-cell" data-label="{{ __('stakeholder.rc.bd_col_budget') }}">
                                @if ($prog['hasBudget'])
                                    <div class="num">{{ $moneyFmt($prog['spent']) }}<small>/ {{ $moneyFmt($prog['budgeted']) }}</small></div>
                                    <div class="sublabel">{{ (int) $pSpendPct }}% {{ __('stakeholder.rc.res_spent') }}</div>
                                    <div class="minibar spend {{ $pSpendClass }}"><i style="width:{{ min(100, (int) $pSpendPct) }}%;"></i></div>
                                @else
                                    <div class="zero">{{ __('stakeholder.rc.bd_none') }}</div>
                                @endif
                            </div>

                            <div class="p3-bd-cell" data-label="{{ __('stakeholder.rc.bd_col_hours') }}">
                                @if ($prog['hrs'] > 0)
                                    <div class="num"><span data-hours="{{ round($prog['hrs']) }}">{{ round($prog['hrs']) }}h</span><small>/wk</small></div>
                                @else
                                    <div class="zero">{{ __('stakeholder.rc.bd_none') }}</div>
                                @endif
                            </div>
                        </summary>

                        @foreach ($prog['children'] as $child)
                            @php
                                $spendPct = $child['budgeted'] > 0 ? min(999, ($child['spent'] / $child['budgeted']) * 100) : 0;
                                $spendClass = 'ok';
                                if ($spendPct >= 100) $spendClass = 'over';
                                elseif ($spendPct >= 90) $spendClass = 'at-risk';
                            @endphp
                            <div class="p3-bd-row child-row">
                                <div class="p3-bd-cell name-cell">
                                    <div class="child-indent">↳</div>
                                    <div>
                                        <div class="name">{{ $child['name'] }}</div>
                                        <div class="type">{{ __('projectType.'.$child['type']) }}</div>
                                    </div>
                                </div>

                                <div class="p3-bd-cell" data-label="{{ __('stakeholder.rc.bd_col_people') }}">
                                    @if ($child['hasPeople'])
                                        <div class="num">{{ $child['peopleCount'] }}<small>{{ __('stakeholder.rc.res_people_unit') }}</small></div>
                                    @else
                                        <div class="zero">{{ __('stakeholder.rc.bd_none') }}</div>
                                    @endif
                                </div>

                                <div class="p3-bd-cell" data-label="{{ __('stakeholder.rc.bd_col_budget') }}">
                                    @if ($child['hasBudget'])
                                        <div class="num">{{ $moneyFmt($child['spent']) }}<small>/ {{ $moneyFmt($child['budgeted']) }}</small></div>
                                        <div class="sublabel">{{ (int) $spendPct }}% {{ __('stakeholder.rc.res_spent') }}</div>
                                        <div class="minibar spend {{ $spendClass }}"><i style="width:{{ min(100, (int) $spendPct) }}%;"></i></div>
                                    @else
                                        <div class="zero">{{ __('stakeholder.rc.bd_none') }}</div>
                                    @endif
                                </div>

                                <div class="p3-bd-cell" data-label="{{ __('stakeholder.rc.bd_col_hours') }}">
                                    @if ($child['allocatedHrs'] > 0)
                                        <div class="num"><span data-hours="{{ round($child['allocatedHrs']) }}">{{ round($child['allocatedHrs']) }}h</span><small>/wk</small></div>
                                    @else
                                        <div class="zero">{{ __('stakeholder.rc.bd_none') }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </details>
                @endforeach
            @else
                {{-- Flat per-project view: program scope, or strategy with no program map. --}}
                @foreach ($perProject as $row)
                    @php
                        $spendPct = $row['budgeted'] > 0 ? min(999, ($row['spent'] / $row['budgeted']) * 100) : 0;
                        $spendClass = 'ok';
                        if ($spendPct >= 100) $spendClass = 'over';
                        elseif ($spendPct >= 90) $spendClass = 'at-risk';
                    @endphp
                    <div class="p3-bd-row">
                        <div class="p3-bd-cell name-cell">
                            <div class="name">{{ $row['name'] }}</div>
                            <div class="type">{{ __('projectType.'.$row['type']) }}</div>
                        </div>

                        <div class="p3-bd-cell" data-label="{{ __('stakeholder.rc.bd_col_people') }}">
                            @if ($row['hasPeople'])
                                <div class="num">{{ $row['peopleCount'] }}<small>{{ __('stakeholder.rc.res_people_unit') }}</small></div>
                            @else
                                <div class="zero">{{ __('stakeholder.rc.bd_none') }}</div>
                            @endif
                        </div>

                        <div class="p3-bd-cell" data-label="{{ __('stakeholder.rc.bd_col_budget') }}">
                            @if ($row['hasBudget'])
                                <div class="num">{{ $moneyFmt($row['spent']) }}<small>/ {{ $moneyFmt($row['budgeted']) }}</small></div>
                                <div class="sublabel">{{ (int) $spendPct }}% {{ __('stakeholder.rc.res_spent') }}</div>
                                <div class="minibar spend {{ $spendClass }}"><i style="width:{{ min(100, (int) $spendPct) }}%;"></i></div>
                            @else
                                <div class="zero">{{ __('stakeholder.rc.bd_none') }}</div>
                            @endif
                        </div>

                        <div class="p3-bd-cell" data-label="{{ __('stakeholder.rc.bd_col_hours') }}">
                            @if ($row['allocatedHrs'] > 0)
                                <div class="num">{{ round($row['allocatedHrs']) }}<small>h/wk</small></div>
                            @else
                                <div class="zero">{{ __('stakeholder.rc.bd_none') }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endif

{{-- ── Capacity vs. demand — per project (with sensitivity + rebalance) ──
     The board-level "can this plan actually be delivered by these people in
     this time?" answer. For each project, joins three independent estimates:
     budgeted hours (planHours), effort (storypoints × conversion), and
     capacity (people × weeks × allocation). Shows the sensitivity, not just a
     flag. When the plan doesn't fit, lists the three rebalance levers with
     specific numbers. --}}
@if (! empty($capacityAnalysis) && $hasResourceData)
    @php
        // Verdict ordering — worst first so board can scan top-down.
        $verdictRank = ['critical' => 0, 'tight' => 1, 'balanced' => 2, 'buffer' => 3, 'no_capacity' => 4, 'no_work' => 5];

        $verdictLabels = [
            'critical'    => __('stakeholder.rc.cap.v_critical'),
            'tight'       => __('stakeholder.rc.cap.v_tight'),
            'balanced'    => __('stakeholder.rc.cap.v_balanced'),
            'buffer'      => __('stakeholder.rc.cap.v_buffer'),
            'no_work'     => __('stakeholder.rc.cap.v_no_work'),
            'no_capacity' => __('stakeholder.rc.cap.v_no_capacity'),
        ];

        // At strategy scope, iterate program rollups (with children); otherwise
        // fall back to flat per-project. The rollup is what a board consumes —
        // per-project drill-down lives inside the expand.
        $capacityRows = ! empty($capacityByProgram) ? $capacityByProgram : $capacityAnalysis;
        uasort($capacityRows, fn ($a, $b) => ($verdictRank[$a['verdict']] ?? 9) <=> ($verdictRank[$b['verdict']] ?? 9));
        $isRollup = ! empty($capacityByProgram);
    @endphp

    <div class="p3-sec">
        <div class="p3-sec-hd">
            <span class="l">{{ __('stakeholder.rc.cap.label') }}</span>
            <span class="s">
                @if ($isRollup)
                    {!! sprintf(__('stakeholder.rc.cap.sub_program'), count($capacityRows)) !!}
                @else
                    {{ __('stakeholder.rc.cap.sub') }}
                @endif
            </span>
        </div>

        <div class="p3-cap-stack">
            @foreach ($capacityRows as $c)
                @include('reports::partials.stakeholder.capacity-card', ['c' => $c, 'verdictLabels' => $verdictLabels])
            @endforeach
        </div>
    </div>
@endif

{{-- ── Dependencies ────────────────────────────────────────────────
     External commitments the strategy relies on (partnerships, grants,
     regulatory approvals). Rendered as its own section because a board packet
     needs to see WHO is owning each risky dep and WHEN the decision lands —
     not just a "N tentative" chip. Sort: tentative first (by dueDate ascending
     so nearest-decision is at top), then confirmed. --}}
@if ($resourceSummary !== null && count($resourceSummary->dependencies) > 0)
    @php
        $depTotal = count($resourceSummary->dependencies);
        $depConfirmedCount = 0;
        $depTentativeCount = 0;
        foreach ($resourceSummary->dependencies as $d) {
            $d->confirmed ? $depConfirmedCount++ : $depTentativeCount++;
        }

        // Sort: tentative first, ordered by soonest dueDate (nulls last);
        // then confirmed (most-recently-modified first).
        $depsSorted = $resourceSummary->dependencies;
        usort($depsSorted, function ($a, $b) {
            if ($a->confirmed !== $b->confirmed) return $a->confirmed ? 1 : -1;
            if (! $a->confirmed && ! $b->confirmed) {
                if ($a->dueDate === null && $b->dueDate !== null) return 1;
                if ($a->dueDate !== null && $b->dueDate === null) return -1;
                return ($a->dueDate ?? '') <=> ($b->dueDate ?? '');
            }
            return ($b->lastModified ?? '') <=> ($a->lastModified ?? '');
        });

        // Find the most-urgent tentative dep with a dueDate for the risk banner.
        $urgent = null;
        $today = new \DateTimeImmutable('today');
        foreach ($depsSorted as $d) {
            if (! $d->confirmed && $d->dueDate !== null) {
                $urgent = $d;
                break;
            }
        }

        $daysUntil = function (?string $iso) use ($today) {
            if ($iso === null || $iso === '') return null;
            try {
                $d = new \DateTimeImmutable($iso);
                return (int) $today->diff($d)->format('%r%a');
            } catch (\Exception $e) { return null; }
        };

        $typeLabels = [
            'government' => __('stakeholder.rc.dep.type_government'),
            'corporate'  => __('stakeholder.rc.dep.type_corporate'),
            'nonprofit'  => __('stakeholder.rc.dep.type_nonprofit'),
            'academic'   => __('stakeholder.rc.dep.type_academic'),
            'community'  => __('stakeholder.rc.dep.type_community'),
            'vendor'     => __('stakeholder.rc.dep.type_vendor'),
        ];

        $fmtDate = function (?string $iso) {
            if ($iso === null) return null;
            try {
                return (new \DateTimeImmutable($iso))->format('M j, Y');
            } catch (\Exception $e) { return $iso; }
        };
        $fmtAgo = function (?string $iso) use ($today) {
            if ($iso === null) return null;
            try {
                $d = new \DateTimeImmutable(substr($iso, 0, 10));
                $days = (int) $today->diff($d)->format('%a');
                if ($days === 0) return __('stakeholder.rc.dep.today');
                if ($days === 1) return __('stakeholder.rc.dep.yesterday');
                if ($days < 7)   return sprintf(__('stakeholder.rc.dep.days_ago'), $days);
                if ($days < 30) {
                    $weeks = (int) floor($days / 7);
                    return sprintf(__($weeks === 1 ? 'stakeholder.rc.dep.week_ago' : 'stakeholder.rc.dep.weeks_ago'), $weeks);
                }
                $months = (int) floor($days / 30);
                return sprintf(__($months === 1 ? 'stakeholder.rc.dep.month_ago' : 'stakeholder.rc.dep.months_ago'), $months);
            } catch (\Exception $e) { return null; }
        };
    @endphp

    <div class="p3-sec">
        <div class="p3-sec-hd">
            <span class="l">{{ __('stakeholder.rc.dep.label') }}</span>
            <span class="s">{!! sprintf(__('stakeholder.rc.dep.sub'), $depTotal, $depConfirmedCount, $depTentativeCount) !!}</span>
        </div>

        @if ($urgent !== null)
            @php $urgentDays = $daysUntil($urgent->dueDate); @endphp
            <div class="p3-dep-urgent {{ $urgentDays !== null && $urgentDays <= 30 ? 'soon' : '' }}">
                <div class="ic"><i class="fa fa-clock"></i></div>
                <div class="body">
                    <div class="hd">
                        @if ($urgentDays !== null && $urgentDays >= 0)
                            {{ sprintf(__('stakeholder.rc.dep.nearest_decision_in'), $urgentDays) }}:
                        @else
                            {{ __('stakeholder.rc.dep.nearest_decision') }}:
                        @endif
                        <strong>{{ $urgent->partnerName }}</strong>
                    </div>
                    <div class="meta">
                        {{ $fmtDate($urgent->dueDate) }}
                        @if ($urgent->owner) · {{ __('stakeholder.rc.dep.owner') }} <strong>{{ $urgent->owner }}</strong> @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="p3-dep-grid">
            @foreach ($depsSorted as $d)
                @php
                    $typeKey = strtolower($d->type);
                    $typeLbl = $typeLabels[$typeKey] ?? ucfirst($typeKey);
                    $due = $d->dueDate;
                    $dueDays = $daysUntil($due);
                    $ago = $fmtAgo($d->lastModified);
                @endphp
                <div class="p3-dep {{ $d->confirmed ? 'confirmed' : 'tentative' }}">
                    <div class="p3-dep-hd">
                        <span class="status">
                            @if ($d->confirmed)
                                <i class="fa fa-circle-check"></i> {{ __('stakeholder.rc.dep.confirmed') }}
                            @else
                                <i class="fa fa-clock"></i> {{ __('stakeholder.rc.dep.tentative') }}
                            @endif
                        </span>
                        @if ($typeLbl !== '')
                            <span class="type-badge type-{{ $typeKey }}">{{ $typeLbl }}</span>
                        @endif
                    </div>

                    <div class="p3-dep-name">{{ $d->partnerName }}</div>

                    <div class="p3-dep-meta">
                        @if (! $d->confirmed && $due !== null)
                            <div class="row {{ $dueDays !== null && $dueDays <= 30 ? 'urgent' : '' }}">
                                <span class="lbl">{{ __('stakeholder.rc.dep.decision_by') }}</span>
                                <span class="val">
                                    {{ $fmtDate($due) }}
                                    @if ($dueDays !== null)
                                        <span class="soft">
                                            @if ($dueDays < 0) ({{ sprintf(__('stakeholder.rc.dep.days_overdue'), abs($dueDays)) }})
                                            @elseif ($dueDays === 0) ({{ __('stakeholder.rc.dep.due_today') }})
                                            @else ({{ sprintf(__('stakeholder.rc.dep.in_days'), $dueDays) }})
                                            @endif
                                        </span>
                                    @endif
                                </span>
                            </div>
                        @endif

                        @if ($d->owner)
                            <div class="row">
                                <span class="lbl">{{ __('stakeholder.rc.dep.owner') }}</span>
                                <span class="val">{{ $d->owner }}</span>
                            </div>
                        @endif
                    </div>

                    @if ($d->notes)
                        <div class="p3-dep-notes">{{ $d->notes }}</div>
                    @endif

                    @if ($ago !== null)
                        <div class="p3-dep-foot">{{ sprintf(__('stakeholder.rc.dep.updated'), $ago) }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- ── Resource gaps & risks ────────────────────────────────────────
     Observations that aren't per-project capacity math: budget-vs-authorized
     imbalances, and (at program scope only) per-person over-allocation and
     idle capacity. Individual hours are the wrong altitude for a strategy
     report — a portfolio audience reads at program/project scope; per-person
     analysis belongs on the program report where a manager can act on it.
     Capacity vs demand above already covers program-level tightness. --}}
@if ($resourceSummary !== null && $hasResourceData)
    @php
        $gaps = [];
        $isProgramScope = ($scope ?? '') === 'program';

        // Capacity vs. demand tightness — at STRATEGY scope, escalate the
        // per-program tight/critical verdicts from CapacityAnalyzer into a
        // gap row. That's the honest program-level altitude a portfolio
        // reader acts on — matches what the Capacity block above says, so
        // the two sections no longer read as contradictions. Prefers the
        // rollup ($capacityByProgram) so we surface program lines, not per-
        // leaf-project ones.
        if (! $isProgramScope) {
            $tightSource = ! empty($capacityByProgram) ? $capacityByProgram : ($capacityAnalysis ?? []);
            foreach ((array) $tightSource as $capRow) {
                $verdict = (string) ($capRow['verdict'] ?? '');
                if (! in_array($verdict, ['tight', 'critical'], true)) continue;

                $gap = (float) ($capRow['gap'] ?? 0);
                if ($gap <= 0) continue;

                $sev = $verdict === 'critical' ? 'red' : 'yellow';
                $gaps[] = [
                    'sev' => $sev,
                    'icon' => 'fa-scale-unbalanced',
                    'headline' => sprintf(
                        __('stakeholder.rc.gap.program_tight'),
                        e((string) ($capRow['name'] ?? '')),
                        round($gap)
                    ),
                    'detail' => sprintf(
                        __('stakeholder.rc.gap.program_tight_detail'),
                        // referenceDemand is what the gap/verdict were computed from (it
                        // switches to effort hours when planHours coverage is low) — the
                        // detail must cite the same demand number as the headline.
                        round((float) ($capRow['referenceDemand'] ?? ($capRow['budgetedHours'] ?? 0))),
                        round((float) ($capRow['availableHours'] ?? 0))
                    ),
                ];
            }
        }

        // Per-person over-allocation & idle capacity — PROGRAM SCOPE ONLY.
        // On the strategy report these are wrong altitude: individual hour
        // math is what a program manager needs, not what a portfolio reader
        // needs. Duplicating them at strategy scope also reads as noise next
        // to the program-level tightness read above.
        if ($isProgramScope) {
            $overPeople = [];
            foreach ($resourceSummary->people as $p) {
                if ($p->capacity > 0 && $p->totalAllocated() > $p->capacity) {
                    $overPeople[] = [
                        'name' => $p->displayName,
                        'over' => round($p->totalAllocated() - $p->capacity, 1),
                        'planned' => round($p->totalAllocated(), 1),
                        'capacity' => round($p->capacity, 1),
                    ];
                }
            }
            if (count($overPeople) === 1) {
                $g = $overPeople[0];
                $gaps[] = [
                    'sev' => 'red', 'icon' => 'fa-user-clock',
                    'headline' => sprintf(__('stakeholder.rc.gap.over_alloc_one'), e($g['name']), $g['over']),
                    'detail' => sprintf(__('stakeholder.rc.gap.over_alloc_one_detail'), $g['planned'], $g['capacity']),
                ];
            } elseif (count($overPeople) > 1) {
                $totalOver = array_sum(array_column($overPeople, 'over'));
                $names = array_slice(array_column($overPeople, 'name'), 0, 3);
                $moreN = max(0, count($overPeople) - 3);
                $namesStr = implode(', ', array_map('e', $names)) . ($moreN > 0 ? sprintf(__('stakeholder.rc.gap.and_more'), $moreN) : '');
                $gaps[] = [
                    'sev' => 'red', 'icon' => 'fa-user-clock',
                    'headline' => sprintf(__('stakeholder.rc.gap.over_alloc_many'), count($overPeople), round($totalOver, 1)),
                    'detail' => $namesStr,
                ];
            }

            // Idle capacity — people with capacity but essentially no allocation.
            $idlePeople = [];
            foreach ($resourceSummary->people as $p) {
                if ($p->capacity > 0 && $p->totalAllocated() / $p->capacity < 0.2) {
                    $idlePeople[] = $p->displayName;
                }
            }
            if (count($idlePeople) > 0) {
                $names = array_slice($idlePeople, 0, 3);
                $moreN = max(0, count($idlePeople) - 3);
                $namesStr = implode(', ', array_map('e', $names)) . ($moreN > 0 ? sprintf(__('stakeholder.rc.gap.and_more'), $moreN) : '');
                $gaps[] = [
                    'sev' => 'blue', 'icon' => 'fa-user-slash',
                    'headline' => sprintf(__('stakeholder.rc.gap.idle_capacity'), count($idlePeople)),
                    'detail' => $namesStr,
                ];
            }
        }

        // Budget: over-budget and burn-risk per project. Reuses the same per-project
        // aggregation as the breakdown table.
        $overBudget = [];
        $burnRisk = [];
        $ghostBudget = [];   // money authorized, nobody assigned
        $unfundedWork = [];  // people assigned, no budget line
        $projectNamesForGaps = [];
        foreach (($report['summaries'] ?? []) as $s) {
            $projectNamesForGaps[(int) ($s->id ?? 0)] = (string) ($s->name ?? '');
        }
        // Rebuild per-project aggregates (mirror of the breakdown table).
        $ppGap = [];
        foreach ($resourceSummary->projectIds as $pid) {
            $pid = (int) $pid;
            $ppGap[$pid] = ['name' => $projectNamesForGaps[$pid] ?? ('#'.$pid), 'people' => 0, 'budgeted' => 0.0, 'spent' => 0.0];
        }
        foreach ($resourceSummary->people as $person) {
            foreach ($person->allocations as $pid => $hrs) {
                if ((float) $hrs > 0 && isset($ppGap[(int) $pid])) $ppGap[(int) $pid]['people']++;
            }
        }
        foreach ($resourceSummary->budget as $line) {
            if (! isset($ppGap[(int) $line->projectId])) continue;
            $ppGap[(int) $line->projectId]['budgeted'] += (float) $line->budgeted;
            $ppGap[(int) $line->projectId]['spent'] += (float) $line->spent;
        }
        foreach ($ppGap as $pid => $row) {
            if ($row['budgeted'] > 0) {
                $pct = $row['spent'] / $row['budgeted'];
                if ($pct >= 1.0) {
                    $overBudget[] = ['name' => $row['name'], 'over' => $row['spent'] - $row['budgeted'], 'pct' => $pct];
                } elseif ($pct >= 0.9) {
                    $burnRisk[] = ['name' => $row['name'], 'pct' => $pct, 'spent' => $row['spent'], 'budgeted' => $row['budgeted']];
                }
                if ($row['people'] === 0) {
                    $ghostBudget[] = ['name' => $row['name'], 'budgeted' => $row['budgeted']];
                }
            }
            if ($row['people'] > 0 && $row['budgeted'] === 0.0) {
                $unfundedWork[] = ['name' => $row['name'], 'people' => $row['people']];
            }
        }

        foreach ($overBudget as $g) {
            $gaps[] = [
                'sev' => 'red', 'icon' => 'fa-dollar-sign',
                'headline' => sprintf(__('stakeholder.rc.gap.over_budget'), e($g['name']), $moneyFmt($g['over'])),
                'detail' => sprintf(__('stakeholder.rc.gap.over_budget_detail'), (int) ($g['pct'] * 100)),
            ];
        }
        foreach ($burnRisk as $g) {
            $gaps[] = [
                'sev' => 'yellow', 'icon' => 'fa-fire',
                'headline' => sprintf(__('stakeholder.rc.gap.burn_risk'), e($g['name']), (int) ($g['pct'] * 100)),
                'detail' => sprintf(__('stakeholder.rc.gap.burn_risk_detail'), $moneyFmt($g['spent']), $moneyFmt($g['budgeted'])),
            ];
        }
        foreach ($ghostBudget as $g) {
            $gaps[] = [
                'sev' => 'yellow', 'icon' => 'fa-ghost',
                'headline' => sprintf(__('stakeholder.rc.gap.ghost_budget'), e($g['name']), $moneyFmt($g['budgeted'])),
                'detail' => __('stakeholder.rc.gap.ghost_budget_detail'),
            ];
        }
        foreach ($unfundedWork as $g) {
            $gaps[] = [
                'sev' => 'blue', 'icon' => 'fa-hand-holding-dollar',
                'headline' => sprintf(__('stakeholder.rc.gap.unfunded'), e($g['name']), $g['people']),
                'detail' => __('stakeholder.rc.gap.unfunded_detail'),
            ];
        }

        // Tentative dependencies moved to their own Dependencies section above
        // — surfacing them here would duplicate the same names in two places.

        // Sort: red → yellow → blue, in insertion order within a severity.
        $sevRank = ['red' => 0, 'yellow' => 1, 'blue' => 2];
        usort($gaps, fn ($a, $b) => ($sevRank[$a['sev']] ?? 9) <=> ($sevRank[$b['sev']] ?? 9));
    @endphp

    <div class="p3-sec">
        <div class="p3-sec-hd">
            <span class="l">{{ __('stakeholder.rc.gaps_label') }}</span>
            <span class="s">{{ __('stakeholder.rc.gaps_sub') }}</span>
        </div>

        <div class="p3-gaps">
            @if (count($gaps) === 0)
                <div class="p3-gap ok">
                    <div class="sev"><i class="fa fa-check"></i></div>
                    <div class="body">
                        <div class="headline">{{ __('stakeholder.rc.gap.none_headline') }}</div>
                        <div class="detail">{{ $isProgramScope ? __('stakeholder.rc.gap.none_detail_program') : __('stakeholder.rc.gap.none_detail_strategy') }}</div>
                    </div>
                </div>
            @else
                @foreach ($gaps as $g)
                    <div class="p3-gap {{ $g['sev'] }}">
                        <div class="sev"><i class="fa {{ $g['icon'] }}"></i></div>
                        <div class="body">
                            <div class="headline">{!! $g['headline'] !!}</div>
                            @if (! empty($g['detail']))
                                <div class="detail">{!! $g['detail'] !!}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endif

{{-- Hours/Days toggle behavior. Preference persists via localStorage under
     'lt.stakeholderReport.unit'. Every [data-hours] node keeps its
     source-of-truth hour value in the attribute; the text is derived from
     that on each toggle change (rendered as "Nh" or "Nd", 8h/day). --}}
<script>
(function () {
    var LS_KEY = 'lt.stakeholderReport.unit';
    var root = document.querySelector('[data-lt-unit-toggle]');
    if (! root) return;

    function applyUnit(unit) {
        document.querySelectorAll('[data-hours]').forEach(function (el) {
            var hrs = parseFloat(el.getAttribute('data-hours'));
            if (isNaN(hrs)) return;
            if (unit === 'days') {
                var d = Math.round((hrs / 8) * 10) / 10;
                var display = (d === Math.round(d) ? d.toFixed(0) : d.toFixed(1)) + 'd';
                el.textContent = display;
            } else {
                el.textContent = Math.round(hrs) + 'h';
            }
        });
        root.querySelectorAll('.p3-unit-btn').forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-unit') === unit);
        });
    }

    var initial = 'hours';
    try { initial = localStorage.getItem(LS_KEY) || 'hours'; } catch (e) {}
    if (initial !== 'hours') applyUnit(initial);

    root.addEventListener('click', function (e) {
        var btn = e.target.closest('.p3-unit-btn');
        if (! btn) return;
        var unit = btn.getAttribute('data-unit');
        try { localStorage.setItem(LS_KEY, unit); } catch (e2) {}
        applyUnit(unit);
    });
})();
</script>

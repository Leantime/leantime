{{--
    Stakeholder Report — Page 2 (Logic Model read-out)

    Fully rewritten against the punch-list. Behavior contracts:

    1. Goal-as-truth. If an item has any linked goal (projectLinks entry with
       linked_entity_type='goal'), the goal record supplies BOTH current and
       target values. The item description is display label only — never
       parsed for a number in that case. Items with no goal link show no
       denominator and no percent.
    2. Guard: if aggregate target <= 0 or current/target > 5, drop the ratio
       (impossible math never reaches a funder) and Log it.
    3. Templated read: max 2 lines per stage, one going-well + one exception,
       materiality-weighted at 20%. Vocabulary is fixed — no freeform prose.
    4. Belief line is ONE sentence, ≤220 chars, built from first 3 activities
       + first impact.
    5. Risk box: one weakest health badge, single sentence, single period.
    6. Layout: max-width 900px, min-width:0 on every grid child, no
       horizontal blowout at 1280px.
    7. Status color map is fixed — at-risk is white bg + inset ring, never cream.

    Vars in:
      $logicModel  null | {canvasId, narrative, stageProgress, healthBadges,
                            coverageMatrix, projectLinks, linkedGoals, projectMeta}
      $hasLM       bool
      $scope       'strategy' | 'program'
--}}

<style>
.rd-scope .p2-wrap{max-width:900px;margin:0 auto;}
.rd-scope .p2-wrap *{min-width:0;}
.rd-scope .p2-subhead{font-size:12.5px;color:var(--rd-text-3);margin:0 0 14px 2px;line-height:1.5;}

/* Belief — quiet card at top with see-more expand */
.rd-scope .p2-believe{background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);padding:16px 20px;margin-bottom:14px;font-size:13.5px;line-height:1.55;color:var(--rd-text-3);}
.rd-scope .p2-believe .lb{font-size:10px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--rd-text-4);display:block;margin-bottom:6px;}
.rd-scope .p2-believe b{color:var(--rd-text-2);font-weight:600;}

/* Stage card */
.rd-scope .p2-stage{background:var(--rd-panel);border:1px solid var(--rd-line);border-left:3px solid var(--rd-s3);border-radius:var(--rd-r-sm);padding:18px 22px;margin-bottom:14px;}
.rd-scope .p2-stage.outcomes{border-left-color:var(--rd-s4);}
.rd-scope .p2-stage .stage-hd{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.rd-scope .p2-stage .frame{font-size:10.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;display:flex;align-items:center;gap:7px;flex:1;min-width:0;}
.rd-scope .p2-stage.outputs .frame{color:var(--rd-s3);}
.rd-scope .p2-stage.outcomes .frame{color:var(--rd-s4);}
.rd-scope .p2-stage .frame i{font-size:11px;}

/* Verdict badge — fixed color map, no cream. */
.rd-scope .p2-vbadge{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;border-radius:22px;padding:5px 13px;flex:none;}
.rd-scope .p2-vbadge.ok{background:#E7F5EE;color:var(--rd-ok);}
.rd-scope .p2-vbadge.wip{background:#EAF1F9;color:#3F72B0;}
.rd-scope .p2-vbadge.risk{background:#fff;color:var(--rd-danger);box-shadow:inset 0 0 0 1.5px var(--rd-danger);}
.rd-scope .p2-vbadge.pending{background:rgba(140,140,140,.08);color:var(--rd-text-3);}
.rd-scope .p2-vbadge .sd{width:7px;height:7px;border-radius:50%;background:currentColor;}

/* Read — templated 1-2 lines with dot leads */
.rd-scope .p2-read{display:flex;flex-direction:column;gap:6px;margin-bottom:14px;}
.rd-scope .p2-readline{font-size:14px;line-height:1.6;color:var(--rd-text-2);display:flex;gap:10px;align-items:flex-start;}
.rd-scope .p2-readline .dot{width:8px;height:8px;border-radius:50%;flex:none;margin-top:8px;}
.rd-scope .p2-readline.good .dot{background:var(--rd-ok);}
.rd-scope .p2-readline.watch .dot{background:#9A6A11;}
.rd-scope .p2-readline.risk .dot{background:var(--rd-danger);}
.rd-scope .p2-readline b{color:var(--rd-text-1);font-weight:600;}
.rd-scope .p2-readline .g{color:var(--rd-ok);font-weight:600;}
.rd-scope .p2-readline .w{color:#9A6A11;font-weight:600;}
.rd-scope .p2-readline .r{color:var(--rd-danger);font-weight:600;}
.rd-scope .p2-readline .mute{color:var(--rd-text-3);}
.rd-scope .p2-readline .lead-label{font-weight:700;}
.rd-scope .p2-readline.watch .lead-label{color:#9A6A11;}
.rd-scope .p2-readline.risk .lead-label{color:var(--rd-danger);}

/* Unresolved-work data-quality note — muted, non-prose, not a bullet */
.rd-scope .p2-unresolved{font-size:12px;color:var(--rd-text-3);margin:6px 0 12px;padding-left:2px;font-style:italic;}
/* Status basis footer — one-line, muted; explains why a status is what it is */
.rd-scope .p2-basis{font-size:11.5px;color:var(--rd-text-4);margin:8px 0 2px;padding-left:2px;}

/* Evidence rows — compact, one per LM item */
.rd-scope .p2-rows{display:flex;flex-direction:column;}
.rd-scope .p2-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:14px;align-items:baseline;padding:9px 0;}
.rd-scope .p2-row + .p2-row{border-top:1px solid var(--rd-line-soft);}
.rd-scope .p2-row-title{font-size:14px;color:var(--rd-text-1);line-height:1.5;min-width:0;}
.rd-scope .p2-row-title .lead{font-weight:700;font-size:15.5px;}
.rd-scope .p2-stage.outputs .p2-row-title .lead{color:var(--rd-s3);}
.rd-scope .p2-stage.outcomes .p2-row-title .lead{color:var(--rd-s4);}
.rd-scope .p2-row-title .planof{color:var(--rd-text-3);font-size:12.5px;margin-left:4px;}
.rd-scope .p2-row-value{color:var(--rd-text-2);font-size:12.5px;margin-left:10px;font-weight:600;font-variant-numeric:tabular-nums;}
.rd-scope .p2-row-status{font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;flex:none;}
.rd-scope .p2-row-status .sd{width:7px;height:7px;border-radius:50%;background:currentColor;}
.rd-scope .p2-row-status.ok{color:var(--rd-ok);}
.rd-scope .p2-row-status.wip{color:#3F72B0;}
.rd-scope .p2-row-status.risk{color:var(--rd-danger);}
.rd-scope .p2-row-status.pending{color:var(--rd-text-4);font-style:italic;font-weight:500;}

/* Show the breakdown */
.rd-scope .p2-brk{margin-top:6px;padding-top:8px;border-top:1px solid var(--rd-line-soft);}
.rd-scope .p2-brk summary{list-style:none;cursor:pointer;display:inline-flex;gap:8px;align-items:center;font-size:11px;font-weight:600;color:var(--rd-text-3);letter-spacing:.3px;text-transform:uppercase;padding:2px 0;}
.rd-scope .p2-brk summary::-webkit-details-marker{display:none;}
.rd-scope .p2-brk summary:focus{outline:none;}
.rd-scope .p2-brk summary:focus-visible{outline:2px solid var(--rd-accent);outline-offset:2px;border-radius:3px;}
.rd-scope .p2-brk summary i{font-size:10px;transition:transform .15s ease;}
.rd-scope .p2-brk[open] > summary i{transform:rotate(90deg);}
.rd-scope .p2-brk-body{margin-top:10px;}
.rd-scope .p2-brk-prog{padding:8px 0;}
.rd-scope .p2-brk-prog + .p2-brk-prog{border-top:1px solid var(--rd-line-soft);}
.rd-scope .p2-brk-progrow{display:grid;grid-template-columns:13px minmax(0,1fr) 120px 44px 82px;gap:12px;align-items:center;}
.rd-scope .p2-brk-progrow.single{grid-template-columns:13px minmax(0,1fr) 82px;}
.rd-scope .p2-brk-progrow .pdot{width:10px;height:10px;border-radius:3px;justify-self:center;background:var(--rd-accent);}
.rd-scope .p2-brk-progrow .pn{font-size:13px;font-weight:600;color:var(--rd-text-1);}
.rd-scope .p2-brk-progrow .pmeta{font-size:10.5px;color:var(--rd-text-4);}
.rd-scope .p2-brk-progrow .pbar{height:6px;border-radius:3px;background:#eef1f3;overflow:hidden;}
.rd-scope .p2-brk-progrow .pbar > i{display:block;height:100%;border-radius:3px;background:var(--rd-accent);}
.rd-scope .p2-brk-progrow .pshare{text-align:right;font-size:13px;font-weight:700;font-variant-numeric:tabular-nums;color:var(--rd-text-1);}
.rd-scope .p2-brk-progrow .pstat{justify-self:end;display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;border-radius:20px;padding:3px 9px;white-space:nowrap;}
.rd-scope .p2-brk-progrow .pstat.ok{background:#E7F5EE;color:var(--rd-ok);}
.rd-scope .p2-brk-progrow .pstat.wip{background:#EAF1F9;color:#3F72B0;}
.rd-scope .p2-brk-progrow .pstat.risk{background:#fff;color:var(--rd-danger);box-shadow:inset 0 0 0 1.5px var(--rd-danger);}
.rd-scope .p2-brk-progrow .pstat .sd{width:6px;height:6px;border-radius:50%;background:currentColor;}
.rd-scope .p2-brk-projs{margin:6px 0 0 25px;padding-left:14px;border-left:2px solid var(--rd-line-soft);}
.rd-scope .p2-brk-pj{display:grid;grid-template-columns:minmax(0,1fr) 12px;gap:12px;align-items:center;padding:4px 0;}
.rd-scope .p2-brk-pj .pjn{font-size:12.5px;color:var(--rd-text-3);}
.rd-scope .p2-brk-pj .pjd{justify-self:center;width:7px;height:7px;border-radius:50%;}
.rd-scope .p2-brk-pj .pjd.ok{background:var(--rd-ok);}
.rd-scope .p2-brk-pj .pjd.wip{background:#3F72B0;}
.rd-scope .p2-brk-pj .pjd.risk{background:var(--rd-danger);}
.rd-scope .p2-brk-more{font-size:11.5px;color:#3F72B0;padding:6px 0 0;}

/* Impact — the purpose */
.rd-scope .p2-impact{background:#f4f9f6;border:1px solid #dcebe3;border-radius:var(--rd-r-sm);padding:16px 20px;margin-bottom:14px;}
.rd-scope .p2-impact .lb{font-size:10px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--rd-s5);margin-bottom:5px;display:flex;align-items:center;gap:7px;}
.rd-scope .p2-impact .goal{font-size:15px;font-weight:600;color:var(--rd-text-1);line-height:1.4;}
.rd-scope .p2-impact .horizon{font-size:11.5px;color:var(--rd-text-3);margin-top:8px;}

/* Risk — the ONE fragile link */
.rd-scope .p2-risk{background:#FBEAEF;border:1px solid #f2d3dd;border-radius:var(--rd-r-sm);padding:14px 18px;display:flex;gap:12px;margin-bottom:14px;}
.rd-scope .p2-risk i.ri{color:var(--rd-danger);margin-top:2px;font-size:14px;flex:none;}
/* Dark overrides — light status tints → translucent over the dark panel. */
.rd-scope.rd-dark .p2-vbadge.ok,.rd-scope.rd-dark .p2-brk-progrow .pstat.ok{background:rgba(87,181,152,.16);}
.rd-scope.rd-dark .p2-vbadge.wip,.rd-scope.rd-dark .p2-brk-progrow .pstat.wip{background:rgba(63,114,176,.22);color:#8fb4e0;}
.rd-scope.rd-dark .p2-vbadge.risk,.rd-scope.rd-dark .p2-brk-progrow .pstat.risk{background:transparent;}
.rd-scope.rd-dark .p2-impact{background:rgba(87,181,152,.10);border-color:rgba(87,181,152,.25);}
.rd-scope.rd-dark .p2-risk{background:var(--rd-danger-bg);border-color:rgba(228,101,137,.30);}
.rd-scope .p2-risk .rb{font-size:13.5px;line-height:1.55;color:var(--rd-text-2);}
.rd-scope .p2-risk .rb .rl{font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--rd-danger);display:block;margin-bottom:3px;}
.rd-scope .p2-risk .rb b{color:var(--rd-text-1);font-weight:600;}

/* Off-strategy drift */
.rd-scope .p2-drift{padding:10px 14px;background:#FBF3E4;border-radius:var(--rd-r-xs);color:#9A6A11;font-size:12.5px;line-height:1.5;display:flex;gap:9px;align-items:flex-start;}
.rd-scope .p2-drift i{color:#b8860b;font-size:11px;margin-top:2px;flex:none;}
.rd-scope .p2-drift b{color:var(--rd-text-1);font-weight:600;}
.rd-scope .p2-gap{padding:10px 14px;background:var(--rd-danger-bg);border-radius:var(--rd-r-xs);color:var(--rd-danger);font-size:12.5px;line-height:1.5;display:flex;gap:9px;align-items:flex-start;margin-bottom:8px;}
.rd-scope .p2-gap i{font-size:11px;margin-top:2px;flex:none;}
.rd-scope .p2-gap b{color:var(--rd-text-1);font-weight:600;}
.rd-scope .p2-gap em{color:var(--rd-text-2);font-style:normal;font-weight:600;}
</style>

@if (! $hasLM)
    <div class="p2-wrap"><div class="rd-empty">{{ __('stakeholder.lm.no_canvas') }}</div></div>
@else
    @php
        $stages = $logicModel['coverageMatrix']['stages'] ?? [];
        $columns = $logicModel['coverageMatrix']['columns'] ?? [];
        $unaligned = $logicModel['coverageMatrix']['unalignedColumns'] ?? [];
        $covCells = $logicModel['coverageMatrix']['cells'] ?? [];
        $healthBadges = $logicModel['healthBadges'] ?? [];
        $projectLinks = $logicModel['projectLinks'] ?? [];
        $linkedGoals = $logicModel['linkedGoals'] ?? [];
        $projectMeta = $logicModel['projectMeta'] ?? [];

        $activityItems = $stages['activities']['items'] ?? [];
        $outputItems   = $stages['outputs']['items'] ?? [];
        $outcomeItems  = $stages['outcomes']['items'] ?? [];
        $impactItems   = $stages['impact']['items'] ?? [];

        $isEvaluated = fn ($item) => isset($projectLinks[(int) (((array) $item)['id'] ?? 0)])
            && count($projectLinks[(int) (((array) $item)['id'] ?? 0)]) > 0;

        // Belief line — templated, ≤220 chars.
        $activityDescs = array_slice(
            array_values(array_filter(array_map(fn ($it) => trim((string) (((array) $it)['description'] ?? '')), $activityItems))),
            0, 3
        );
        $activitiesSummary = '';
        if (count($activityDescs) > 0) {
            $lowered = array_map(fn ($s) => (mb_strtolower(mb_substr($s, 0, 1)) . mb_substr($s, 1)), $activityDescs);
            $activitiesSummary = count($lowered) === 1
                ? $lowered[0]
                : (count($lowered) === 2
                    ? $lowered[0] . ' and ' . $lowered[1]
                    : $lowered[0] . ', ' . $lowered[1] . ', and ' . $lowered[2]);
        }
        $impactSummary = '';
        if (count($impactItems) > 0) {
            $impactSummary = trim((string) (((array) $impactItems[0])['description'] ?? ''));
            // lowercase first char for grammar
            if ($impactSummary !== '') $impactSummary = mb_strtolower(mb_substr($impactSummary, 0, 1)) . mb_substr($impactSummary, 1);
        }
        $beliefLine = '';
        if ($activitiesSummary !== '' && $impactSummary !== '') {
            $beliefLine = sprintf(__('stakeholder.lm.belief_full'), $activitiesSummary, $impactSummary);
        } elseif ($activitiesSummary !== '') {
            $beliefLine = sprintf(__('stakeholder.lm.belief_activities_only'), $activitiesSummary);
        }
        if (mb_strlen($beliefLine) > 220 && $activitiesSummary !== '') {
            $beliefLine = sprintf(__('stakeholder.lm.belief_activities_only'), $activitiesSummary);
        }

        // Infer the item's expected metric unit ONCE from its authored label.
        // Cached in $itemExpectedUnit so render never regexes. `%` → percent,
        // `$` → currency, anything else → number. This is the label's contract;
        // goals whose metricType disagrees are dropped from the item's rollup
        // (rather than silently pretending unrelated units are the same thing).
        $itemExpectedUnit = [];
        $inferItemUnit = function (string $desc): string {
            $d = trim($desc);
            if ($d === '') return 'number';
            if (str_contains($d, '%')) return 'percent';
            if (str_contains($d, '$')) return 'currency';
            return 'number';
        };
        foreach (array_merge($outputItems, $outcomeItems, $activityItems, $impactItems) as $it) {
            $iid = (int) (((array) $it)['id'] ?? 0);
            if ($iid > 0) $itemExpectedUnit[$iid] = $inferItemUnit((string) (((array) $it)['description'] ?? ''));
        }

        // Goal-based aggregate for an item. Only goals whose unit matches the
        // item's expected unit contribute — mismatched goals are logged and
        // dropped (safety over silent lying). Returns null when no matching
        // goals exist, or the ratio is impossible.
        $itemGoalAggregate = function ($item) use ($projectLinks, $linkedGoals, $itemExpectedUnit) {
            $itemId = (int) (((array) $item)['id'] ?? 0);
            $expectedUnit = $itemExpectedUnit[$itemId] ?? 'number';
            $links = $projectLinks[$itemId] ?? [];
            $goals = [];
            $mismatched = 0;
            foreach ($links as $link) {
                if (($link['linked_entity_type'] ?? '') !== 'goal') continue;
                $gid = (int) ($link['linked_entity_id'] ?? 0);
                if (! isset($linkedGoals[$gid])) continue;
                $g = $linkedGoals[$gid];
                $gUnit = ($g['metricType'] ?? 'number') === 'percent' ? 'percent'
                       : (($g['metricType'] ?? 'number') === 'currency' ? 'currency' : 'number');
                if ($gUnit !== $expectedUnit) {
                    $mismatched++;
                    \Illuminate\Support\Facades\Log::info('page2: unit mismatch on link', [
                        'itemId' => $itemId, 'expected' => $expectedUnit,
                        'goalId' => $gid,   'goalUnit' => $gUnit,
                    ]);
                    continue;
                }
                $goals[] = $g;
            }
            if (count($goals) === 0) return null;

            $current = array_sum(array_column($goals, 'currentValue'));
            $end     = array_sum(array_column($goals, 'endValue'));
            $hasTarget = $end > 0;
            $ratio = $hasTarget ? ($current / $end) : null;
            if ($ratio !== null && $ratio > 5) {
                \Illuminate\Support\Facades\Log::warning('page2: implausible ratio', ['itemId' => $itemId, 'current' => $current, 'target' => $end]);
                $hasTarget = false;
            }

            $anyRisk = false; $allOnTrack = true;
            foreach ($goals as $g) {
                if ($g['status'] === 'status_atrisk' || $g['status'] === 'status_offtrack') $anyRisk = true;
                if ($g['status'] !== 'status_ontrack') $allOnTrack = false;
            }
            return [
                'goals'      => $goals,
                'current'    => $current,
                'end'        => $end,
                'hasTarget'  => $hasTarget,
                'pct'        => $hasTarget ? min(100, (int) round($ratio * 100)) : 0,
                'metricType' => $expectedUnit,
                'ragClass'   => $anyRisk ? 'risk' : ($allOnTrack ? 'ok' : 'wip'),
                'mismatched' => $mismatched,
            ];
        };

        // Format a metric respecting its type (percent vs number).
        $fmtMetric = function ($value, string $metricType) {
            $value = (float) $value;
            if ($metricType === 'percent') return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.').'%';
            if ($value == floor($value)) return number_format($value, 0, '.', ',');
            return number_format($value, 1, '.', ',');
        };

        // Strip leading numeric token from label when it's redundant with a goal
        // aggregate. "1,200 screenings completed" → "screenings completed".
        $stripLeadingNumber = function (string $desc): string {
            if (preg_match('/^[\d][\d,\.]*(?:%|\+|)?\s+(.+)$/u', trim($desc), $m)) {
                return trim($m[1]);
            }
            return trim($desc);
        };

        // Stage-level aggregate — total contributions across items in the stage.
        $stageAgg = function (array $items) use ($isEvaluated, $itemGoalAggregate) {
            $total = 0.0; $end = 0.0; $anyRisk = false; $rolled = [];
            foreach ($items as $item) {
                if (! $isEvaluated($item)) continue;
                $a = $itemGoalAggregate($item);
                if ($a === null) continue;
                $itemId = (int) (((array) $item)['id'] ?? 0);
                $rolled[$itemId] = $a;
                $total += $a['current'];
                $end   += $a['end'];
                if ($a['ragClass'] === 'risk') $anyRisk = true;
            }
            $pct = $end > 0 ? min(100, (int) round(($total / $end) * 100)) : 0;
            return ['rolled' => $rolled, 'pct' => $pct, 'anyRisk' => $anyRisk, 'total' => $total, 'end' => $end];
        };

        // Per-program contribution across a stage — for Show-the-breakdown and
        // the templated read. Returns {contributors, unresolvedShare}.
        //
        // A goal is UNRESOLVED when its owning project is missing, has no
        // parent program, or is itself the strategy — those are never
        // contributors (a strategy is not its own program; a null bucket is
        // not a colleague). Unresolved value is tracked separately so the
        // page can render an honest data-quality note without personifying
        // the null bucket as a named entity.
        $stageProgramRollup = function (array $items) use ($isEvaluated, $itemGoalAggregate, $projectMeta) {
            $perProgram = [];
            $unresolvedCurrent = 0.0;
            $unresolvedEnd = 0.0;

            foreach ($items as $item) {
                if (! $isEvaluated($item)) continue;
                $a = $itemGoalAggregate($item);
                if ($a === null) continue;

                $itemId = (int) (((array) $item)['id'] ?? 0);
                $itemDesc = trim((string) (((array) $item)['description'] ?? ''));

                foreach ($a['goals'] as $g) {
                    $pid    = $g['projectId'];
                    $progId = $g['programId'];
                    // Unresolved: no project, no program, or the "project" is
                    // actually a strategy row (which happens when a goal lives
                    // on the strategy's own canvas).
                    $isUnresolved = ($pid === null)
                        || ($progId === null)
                        || (($g['projectType'] ?? '') === 'strategy');
                    if ($isUnresolved) {
                        $unresolvedCurrent += $g['currentValue'];
                        $unresolvedEnd     += $g['endValue'];
                        continue;
                    }

                    $key = $progId;
                    if (! isset($perProgram[$key])) {
                        $perProgram[$key] = [
                            'id'         => $progId,
                            'name'       => $projectMeta[$pid]['programName'] ?? ('#'.$progId),
                            'current'    => 0.0,
                            'end'        => 0.0,
                            'anyRisk'    => false,
                            'allOnTrack' => true,
                            'projects'   => [],
                            'riskItems'  => [],  // {id, description} for citation in exception line
                        ];
                    }
                    $perProgram[$key]['current'] += $g['currentValue'];
                    $perProgram[$key]['end']     += $g['endValue'];
                    if ($g['status'] === 'status_atrisk' || $g['status'] === 'status_offtrack') {
                        $perProgram[$key]['anyRisk'] = true;
                        // Track the ITEM this contributor caused risk on — the read
                        // uses it to make exception lines cite what's off.
                        $alreadyTracked = false;
                        foreach ($perProgram[$key]['riskItems'] as $ri) {
                            if ($ri['id'] === $itemId) { $alreadyTracked = true; break; }
                        }
                        if (! $alreadyTracked) {
                            $perProgram[$key]['riskItems'][] = ['id' => $itemId, 'description' => $itemDesc];
                        }
                    }
                    if ($g['status'] !== 'status_ontrack') $perProgram[$key]['allOnTrack'] = false;

                    // Project row aggregation inside program. Skip strategy-typed
                    // projects (defense-in-depth: already filtered above).
                    if (($g['projectType'] ?? '') === 'strategy') continue;

                    $found = false;
                    foreach ($perProgram[$key]['projects'] as &$pj) {
                        if ($pj['id'] === $pid) {
                            $pj['current'] += $g['currentValue'];
                            $pj['end']     += $g['endValue'];
                            if ($g['status'] === 'status_atrisk') $pj['ragClass'] = 'risk';
                            $found = true;
                            break;
                        }
                    }
                    unset($pj);
                    if (! $found) {
                        $perProgram[$key]['projects'][] = [
                            'id'       => $pid,
                            'name'     => $projectMeta[$pid]['name'] ?? ('#'.$pid),
                            'current'  => $g['currentValue'],
                            'end'      => $g['endValue'],
                            'ragClass' => $g['status'] === 'status_atrisk' ? 'risk' : ($g['status'] === 'status_ontrack' ? 'ok' : 'wip'),
                        ];
                    }
                }
            }

            // Renormalize shares over RESOLVED contributors only. Unresolved
            // value doesn't dilute the lead's share — it's tracked separately
            // as a data-quality signal.
            $resolvedTotal = array_sum(array_column($perProgram, 'current'));
            foreach ($perProgram as &$prog) {
                $prog['pct']        = $resolvedTotal > 0 ? (int) round(($prog['current'] / $resolvedTotal) * 100) : 0;
                $prog['ragClass']   = $prog['anyRisk'] ? 'risk' : ($prog['allOnTrack'] ? 'ok' : 'wip');
                $prog['statusWord'] = $prog['anyRisk'] ? __('stakeholder.lm.status_word_behind') : ($prog['allOnTrack'] ? __('stakeholder.lm.status_word_ontrack') : __('stakeholder.lm.status_word_ramping'));
                usort($prog['projects'], fn ($a, $b) => $b['current'] <=> $a['current']);
            }
            unset($prog);
            uasort($perProgram, fn ($a, $b) => $b['pct'] <=> $a['pct']);

            $grandTotal = $resolvedTotal + $unresolvedCurrent;
            $unresolvedShare = $grandTotal > 0 ? (int) round(($unresolvedCurrent / $grandTotal) * 100) : 0;

            return [
                'contributors'    => array_values($perProgram),
                'unresolvedShare' => $unresolvedShare,
                'hasUnresolved'   => $unresolvedCurrent > 0 || $unresolvedEnd > 0,
            ];
        };

        // Templated read — max 2 lines. Vocabulary is fixed per punch-list §2.
        // BRANCHES ON CONTRIBUTOR COUNT:
        //  1  → single-contributor sentence (scoped to the stage — "on outputs
        //       here", not bare "on this" — so cross-card contradictions read
        //       as legitimate scope differences)
        //  2  → lead is carrying + second adds the rest
        //  3+ → lead + weighted exception, materiality threshold 20%
        // Exception lines CITE THE ITEM the contributor is off on — the row
        // status and the read reconcile via the item name.
        $renderRead = function (array $programs, string $scopeLabel) {
            $n = count($programs);
            if ($n === 0) return [];

            $statusLead = fn ($p) => match ($p['ragClass']) {
                'ok'   => '<span class="g">' . e($p['statusWord']) . '</span>',
                'risk' => '<span class="r">' . e($p['statusWord']) . '</span>',
                default => '<span class="w">' . e($p['statusWord']) . '</span>',
            };

            // ── Single-contributor branch: no shares, no driver language.
            // Scoped ("outputs here" / "outcomes here") so a program appearing
            // in both cards with different statuses reads as legitimate scope,
            // not contradiction.
            if ($n === 1) {
                $only = $programs[0];
                $type = $only['ragClass'] === 'risk' ? 'risk' : ($only['ragClass'] === 'ok' ? 'good' : 'watch');
                return [[
                    'type' => $type,
                    'html' => sprintf(
                        __('stakeholder.lm.read_only_program_scoped'),
                        e($only['name']),
                        e($scopeLabel),
                        $statusLead($only)
                    ),
                ]];
            }

            $lead   = $programs[0];
            $second = $programs[1] ?? null;
            $lines  = [];

            // Helper: name the specific item this contributor is off on.
            // Cites the first risk item; if none, empty string skips the
            // "on X" clause.
            $riskItemName = function (array $prog): string {
                $items = $prog['riskItems'] ?? [];
                return count($items) > 0 ? (string) ($items[0]['description'] ?? '') : '';
            };

            $exceptionLine = function (array $prog, bool $critical) use ($statusLead, $riskItemName) {
                $item = $riskItemName($prog);
                $onClause = $item !== '' ? sprintf(__('stakeholder.lm.read_on_item_clause'), e($item)) : '';
                $tmpl = $critical ? 'stakeholder.lm.read_critical_cited' : 'stakeholder.lm.read_watch_cited';
                // Order: NAME is STATUS_WORD on ITEM — but at X% ...
                // statusLead comes BEFORE onClause; onClause carries its own
                // leading space so tokens don't run together.
                return [
                    'type' => $critical ? 'risk' : 'watch',
                    'html' => '<span class="lead-label">' . e($critical ? __('stakeholder.lm.critical_label') : __('stakeholder.lm.watch_label')) . ':</span> '
                        . sprintf(
                            __($tmpl),
                            e($prog['name']),
                            $statusLead($prog),
                            $onClause,
                            $prog['pct']
                        ),
                ];
            };

            // ── Two-contributor branch: lead + second.
            // If second is the exception, DROP the "adds the rest" clause —
            // otherwise it names the second twice (benign in line 1, behind
            // in line 2). Never two lines about the same entity.
            if ($n === 2) {
                $secondIsException = $second['ragClass'] !== 'ok';
                $goingWellHtml = sprintf(
                    __('stakeholder.lm.read_going_well'),
                    e($lead['name']),
                    $lead['pct'],
                    $statusLead($lead)
                );
                if (! $secondIsException) {
                    $goingWellHtml .= sprintf(__('stakeholder.lm.read_second_clause'), e($second['name']));
                }
                $lines[] = ['type' => 'good', 'html' => $goingWellHtml];
                if ($secondIsException) {
                    $lines[] = $exceptionLine($second, $second['pct'] >= 20);
                }
                return $lines;
            }

            // ── 3+ contributor branch: lead + weighted exception.
            $exception = null;
            foreach ($programs as $p) {
                if ($p['ragClass'] !== 'ok') { $exception = $p; break; }
            }

            $leadIsException = $exception !== null && $exception['id'] === $lead['id'];
            if (! $leadIsException) {
                $lines[] = [
                    'type' => 'good',
                    'html' => sprintf(
                        __('stakeholder.lm.read_going_well'),
                        e($lead['name']),
                        $lead['pct'],
                        $statusLead($lead)
                    ),
                ];
            }

            if ($exception !== null) {
                $lines[] = $exceptionLine($exception, $exception['pct'] >= 20 || $leadIsException);
            }

            return $lines;
        };

        // Weakest fragile link for the Risk block — one, not four.
        $fragileLink = null;
        $riskPref = ['risk' => 0, 'warning' => 1];
        foreach ($healthBadges as $badge) {
            $s = $badge['health_status'] ?? '';
            if (! isset($riskPref[$s])) continue;
            if ($fragileLink === null || $riskPref[$s] < $riskPref[$fragileLink['health_status']]) {
                $fragileLink = $badge;
                continue;
            }
            // Tie-break on risk_level (higher = worse).
            if ($s === $fragileLink['health_status'] && ((int) ($badge['risk_level'] ?? 0)) > ((int) ($fragileLink['risk_level'] ?? 0))) {
                $fragileLink = $badge;
            }
        }
    @endphp

    <div class="p2-wrap">

    <div class="p2-subhead">{{ __('stakeholder.lm.page_subhead') }}</div>

    {{-- Belief — one templated sentence --}}
    <div class="p2-believe">
        <span class="lb">{{ __('stakeholder.lm.believe_label') }}</span>
        @if ($beliefLine !== '')
            {{ $beliefLine }}
        @else
            {{ __('stakeholder.lm.belief_empty') }}
        @endif
    </div>

    {{-- Stage card renderer — one call for Outputs, one for Outcomes. --}}
    @foreach ([
        ['key' => 'outputs',  'items' => $outputItems,  'frame' => __('stakeholder.lm.frame_producing_outputs'), 'icon' => 'fa-boxes-stacked', 'cls' => 'outputs'],
        ['key' => 'outcomes', 'items' => $outcomeItems, 'frame' => __('stakeholder.lm.frame_achieving_outcomes'), 'icon' => 'fa-chart-line',    'cls' => 'outcomes'],
    ] as $stage)
        @php
            $stageItems = array_values(array_filter($stage['items'], $isEvaluated));
            if (count($stageItems) === 0) continue;

            $agg          = $stageAgg($stage['items']);
            $rollupResult = $stageProgramRollup($stage['items']);
            $rollup       = $rollupResult['contributors'];
            $scopeLabel   = $stage['key'] === 'outputs' ? __('stakeholder.lm.scope_outputs') : __('stakeholder.lm.scope_outcomes');
            $readLns      = $renderRead($rollup, $scopeLabel);

            // Badge derives from the READ's outcome, not from item-level rollup.
            // This makes the badge the read's headline — a reader can't catch
            // the card contradicting itself (v4 Fix 4).
            //   No resolved contributors  → "no evidence yet"
            //   Any Critical line         → "At risk"
            //   Any Watch line only       → "In progress"
            //   All going-well            → "On track"
            $readTypes = array_column($readLns, 'type');
            if (count($rollup) === 0) {
                $badgeCls = 'pending';
                $badgeLbl = __('stakeholder.lm.no_evidence_yet');
            } elseif (in_array('risk', $readTypes, true)) {
                $badgeCls = 'risk';
                $badgeLbl = __('stakeholder.lm.verdict_at_risk');
            } elseif (in_array('watch', $readTypes, true)) {
                $badgeCls = 'wip';
                $badgeLbl = __('stakeholder.lm.verdict_in_progress');
            } else {
                $badgeCls = 'ok';
                $badgeLbl = __('stakeholder.lm.verdict_on_track');
            }
        @endphp

        <div class="p2-stage {{ $stage['cls'] }}">
            <div class="stage-hd">
                <div class="frame"><i class="fa {{ $stage['icon'] }}"></i> {{ $stage['frame'] }}</div>
                <span class="p2-vbadge {{ $badgeCls }}"><span class="sd"></span> {{ $badgeLbl }}</span>
            </div>

            @if (count($readLns) > 0)
                <div class="p2-read">
                    @foreach ($readLns as $ln)
                        <div class="p2-readline {{ $ln['type'] }}">
                            <span class="dot"></span>
                            <div>{!! $ln['html'] !!}</div>
                        </div>
                    @endforeach
                </div>
            @elseif ($rollupResult['hasUnresolved'])
                {{-- No resolved contributors, only unresolved: don't render a
                     read; render the not-linked note alone. --}}
                <div class="p2-unresolved">{{ __('stakeholder.lm.not_linked_note') }}</div>
            @endif

            @if (count($readLns) > 0 && $rollupResult['unresolvedShare'] >= 10)
                {{-- Data-quality note, muted, non-prose. Not a bullet, not in
                     the read's voice. --}}
                <div class="p2-unresolved">{{ sprintf(__('stakeholder.lm.unresolved_note'), $rollupResult['unresolvedShare']) }}</div>
            @endif

            <div class="p2-rows">
                @foreach ($stageItems as $item)
                    @php
                        $itemId = (int) (((array) $item)['id'] ?? 0);
                        $arr = (array) $item;
                        // Label is ALWAYS the authored description, verbatim
                        // — never prefixed, never regex-parsed for a number.
                        $label = trim((string) ($arr['description'] ?? ''));
                        // Aggregate carries only unit-matched goals (see
                        // itemGoalAggregate). When present, we can safely
                        // render the current value as its own element beside
                        // the label — no splice.
                        $a = $agg['rolled'][$itemId] ?? null;
                        $currentDisplay = '';
                        if ($a !== null) {
                            // Row status = goal RAG (same as the contributor
                            // rollup that feeds the badge). Not a pct
                            // threshold — a row and its card must agree.
                            if ($a['ragClass'] === 'risk') {
                                $rowCls = 'risk'; $rowLbl = __('stakeholder.lm.status_at_risk');
                            } elseif ($a['ragClass'] === 'ok') {
                                $rowCls = 'ok'; $rowLbl = __('stakeholder.lm.status_on_track');
                            } else {
                                $rowCls = 'wip'; $rowLbl = __('stakeholder.lm.status_in_progress');
                            }
                            $currentDisplay = sprintf(__('stakeholder.lm.so_far'), $fmtMetric($a['current'], $a['metricType']));
                        } else {
                            $rowCls = 'pending';
                            $rowLbl = __('stakeholder.lm.status_pending');
                        }
                    @endphp
                    @php
                        // Provenance for the row status — surfaced as a
                        // tooltip on hover so the reader can trace it back to
                        // a person and date without instructional prose on
                        // the page. Format: "Set by {Author} · {Date}". Falls
                        // back to a generic note when author/date is missing.
                        $statusBasis = '';
                        if ($a !== null && count($a['goals']) > 0) {
                            $sourceGoal = null;
                            foreach ($a['goals'] as $ag) {
                                if ($sourceGoal === null || (string) ($ag['modified'] ?? '') > (string) ($sourceGoal['modified'] ?? '')) {
                                    $sourceGoal = $ag;
                                }
                            }
                            $author = trim((string) ($sourceGoal['authorName'] ?? ''));
                            $dateStr = '';
                            $mod = (string) ($sourceGoal['modified'] ?? '');
                            if ($mod !== '') {
                                try { $dateStr = (new \DateTimeImmutable($mod))->format('M j'); } catch (\Exception $e) {}
                            }
                            if ($author !== '' && $dateStr !== '') $statusBasis = sprintf(__('stakeholder.lm.status_basis_who_when'), $author, $dateStr);
                            elseif ($author !== '')                $statusBasis = sprintf(__('stakeholder.lm.status_basis_who'), $author);
                            elseif ($dateStr !== '')               $statusBasis = sprintf(__('stakeholder.lm.status_basis_when'), $dateStr);
                        }
                    @endphp
                    <div class="p2-row">
                        <div class="p2-row-title">
                            {{ $label }}
                            @if ($currentDisplay !== '')
                                <span class="p2-row-value">{{ $currentDisplay }}</span>
                            @endif
                        </div>
                        <span class="p2-row-status {{ $rowCls }}"@if ($statusBasis !== '') data-tippy-content="{{ $statusBasis }}"@endif><span class="sd"></span> {{ $rowLbl }}</span>
                    </div>
                @endforeach
            </div>

            @if (count($rollup) > 0)
                <details class="p2-brk">
                    <summary><i class="fa fa-chevron-right"></i> {{ __('stakeholder.lm.show_breakdown') }}</summary>
                    <div class="p2-brk-body">
                        @php
                            // Same contributor-count branch as the read:
                            //  n = 1 → no bar, no percent, no "100%" tautology
                            //  n ≥ 2 → name + count + share bar + share % + status
                            $contribCount = count($rollup);
                        @endphp
                        @foreach ($rollup as $prog)
                            @php
                                $showProjects = array_slice($prog['projects'], 0, 4);
                                $moreProj = max(0, count($prog['projects']) - 4);
                                $projCount = count($prog['projects']);
                                $nProjectsLbl = $projCount === 1
                                    ? __('stakeholder.lm.n_project_one')
                                    : sprintf(__('stakeholder.lm.n_projects'), $projCount);
                                $pstatLbl = $prog['ragClass'] === 'risk'
                                    ? __('stakeholder.lm.status_at_risk')
                                    : ($prog['ragClass'] === 'ok' ? __('stakeholder.lm.status_on_track') : __('stakeholder.lm.status_in_progress'));
                            @endphp
                            <div class="p2-brk-prog">
                                @if ($contribCount === 1)
                                    <div class="p2-brk-progrow single">
                                        <span class="pdot"></span>
                                        <div>
                                            <div class="pn">{{ $prog['name'] }}</div>
                                            <div class="pmeta">{{ $nProjectsLbl }}</div>
                                        </div>
                                        <span class="pstat {{ $prog['ragClass'] }}"><span class="sd"></span> {{ $pstatLbl }}</span>
                                    </div>
                                @else
                                    <div class="p2-brk-progrow">
                                        <span class="pdot"></span>
                                        <div>
                                            <div class="pn">{{ $prog['name'] }}</div>
                                            <div class="pmeta">{{ $nProjectsLbl }}</div>
                                        </div>
                                        <div class="pbar"><i style="width:{{ min(100, $prog['pct']) }}%;"></i></div>
                                        <div class="pshare">{{ $prog['pct'] }}%</div>
                                        <span class="pstat {{ $prog['ragClass'] }}"><span class="sd"></span> {{ $pstatLbl }}</span>
                                    </div>
                                @endif
                                @if (count($showProjects) > 0)
                                    <div class="p2-brk-projs">
                                        @foreach ($showProjects as $pj)
                                            <div class="p2-brk-pj">
                                                <span class="pjn">{{ $pj['name'] }}</span>
                                                <span class="pjd {{ $pj['ragClass'] }}"></span>
                                            </div>
                                        @endforeach
                                        @if ($moreProj > 0)
                                            <div class="p2-brk-more">+ {{ $moreProj }} {{ __('stakeholder.lm.more_word') }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    @endforeach

    {{-- Impact — one aim, not a list --}}
    @if (count($impactItems) > 0)
        @php $primaryImpact = trim((string) (((array) $impactItems[0])['description'] ?? '')); @endphp
        @if ($primaryImpact !== '')
            <div class="p2-impact">
                <div class="lb"><i class="fa fa-bullseye"></i> {{ __('stakeholder.lm.for_what_label') }}</div>
                <div class="goal">{{ $primaryImpact }}</div>
                <div class="horizon">{{ __('stakeholder.lm.impact_horizon') }}</div>
            </div>
        @endif
    @endif

    {{-- Risk — one weakest fragile link, single sentence, single period --}}
    @if ($fragileLink !== null)
        @php
            $assumption = trim((string) ($fragileLink['assumption_text'] ?? ''));
            $assumption = rtrim($assumption, '.!?');
            $connector  = trim((string) ($fragileLink['connector_label'] ?? ''));
        @endphp
        <div class="p2-risk">
            <i class="fa fa-triangle-exclamation ri"></i>
            <div class="rb">
                <span class="rl">{{ __('stakeholder.lm.risk_label') }}</span>
                @if ($assumption !== '')
                    {{ __('stakeholder.lm.risk_leap_intro') }} <b>{{ $assumption }}</b>.
                @elseif ($connector !== '')
                    {{ __('stakeholder.lm.risk_generic_intro') }} <b>{{ $connector }}</b>.
                @endif
                @if (empty($fragileLink['has_data']))
                    <b>{{ __('stakeholder.lm.risk_no_evidence') }}</b>{{ __('stakeholder.lm.risk_keep_honest') }}
                @endif
            </div>
        </div>
    @endif

    {{-- ── Also this period — real completed work with NO LM link.
         Item-grain drift (unalignedColumns names program-grain drift; this
         is one level finer). Each row is a completion that touched real
         numbers but maps to nothing in the model — a link-me-to-an-outcome
         invitation, not a scold. Silent when nothing is unlinked. --}}
    @php
        $completedThisPeriod = (array) ($report['milestones']['completed'] ?? []);
        // Build the set of milestone IDs any LM item links to.
        $linkedMilestoneIds = [];
        foreach ($projectLinks as $itemLinks) {
            foreach ($itemLinks as $link) {
                if (($link['linked_entity_type'] ?? '') === 'milestone') {
                    $linkedMilestoneIds[(int) $link['linked_entity_id']] = true;
                }
            }
        }
        // "Something real to show" per the spec — a metric, a count, OR
        // a completion. A completed milestone IS a completion, so a
        // headline is sufficient; task stats are optional decoration.
        // Filter out anything with no headline (a bare row is noise).
        $alsoThisPeriod = [];
        foreach ($completedThisPeriod as $ms) {
            $mid = (int) ($ms->id ?? 0);
            if ($mid === 0 || isset($linkedMilestoneIds[$mid])) continue;
            $headline = trim((string) ($ms->headline ?? ''));
            if ($headline === '') continue;
            $taskStats = (array) ($ms->taskStats ?? []);
            $doneCount = (int) ($taskStats['done'] ?? 0);
            $totalCount = (int) ($taskStats['total'] ?? 0);
            $metric = $totalCount > 0
                ? sprintf(__('stakeholder.lm.also_metric_tasks'), $doneCount, $totalCount)
                : '';
            $alsoThisPeriod[] = [
                'id'        => $mid,
                'headline'  => $headline,
                'metric'    => $metric,
                'projectId' => (int) ($ms->projectId ?? 0),
                'canvasId'  => (int) ($logicModel['canvasId'] ?? 0),
            ];
        }
        $alsoCount = count($alsoThisPeriod);
        $alsoShown = array_slice($alsoThisPeriod, 0, 3);
        $alsoMore  = max(0, $alsoCount - 3);
    @endphp

    @if ($alsoCount > 0)
        <style>
        .rd-scope .p2-also{margin-top:14px;padding:10px 14px;background:var(--rd-bg);border-radius:var(--rd-r-xs);border:1px solid var(--rd-line);}
        .rd-scope .p2-also .lb{font-size:10.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-text-3);margin-bottom:8px;display:flex;align-items:center;gap:7px;}
        .rd-scope .p2-also .lb i{font-size:11px;}
        .rd-scope .p2-also .row{font-size:12.5px;line-height:1.55;color:var(--rd-text-2);padding:3px 0;}
        .rd-scope .p2-also .row b{color:var(--rd-text-1);font-weight:600;}
        .rd-scope .p2-also .row .metric{color:var(--rd-text-3);}
        .rd-scope .p2-also .more{font-size:11.5px;color:var(--rd-text-3);margin-top:4px;font-style:italic;}
        </style>
        <div class="p2-also">
            <div class="lb"><i class="fa fa-code-branch"></i> {{ __('stakeholder.lm.also_label') }}</div>
            @foreach ($alsoShown as $row)
                {{-- Plain drift note: a milestone that closed this period but
                     doesn't map to a Logic Model outcome. No CTA — the app
                     doesn't currently have a one-click "link a milestone to
                     an outcome" flow, and a dead link on the honesty page is
                     worse than no link. When the flow lands, wire it here. --}}
                <div class="row">
                    <b>{{ $row['headline'] }}</b>@if ($row['metric'] !== '') · <span class="metric">{{ $row['metric'] }}</span>@endif
                </div>
            @endforeach
            @if ($alsoMore > 0)
                <div class="more">{{ sprintf(__('stakeholder.lm.also_more'), $alsoMore) }}</div>
            @endif
        </div>
    @endif

    {{-- Coverage gap (strategy scope): a "result" outcome/output/impact item
         that no program is working toward. The actionable hole — a funder
         reads it as "nobody is delivering this intended result". Derived from
         the same coverage matrix as drift: an item with no covering cell. --}}
    @if (($scope ?? '') === 'strategy')
        @php
            $resultStages = ['outputs', 'outcomes', 'impact'];
            $coverageGaps = [];
            foreach ($stages as $stageKey => $stage) {
                if (! in_array($stageKey, $resultStages, true)) {
                    continue;
                }
                foreach (($stage['items'] ?? []) as $covItem) {
                    $covArr = (array) $covItem;
                    $covId = (int) ($covArr['id'] ?? 0);
                    $covLabel = trim((string) ($covArr['description'] ?? ''));
                    if ($covId > 0 && $covLabel !== '' && empty($covCells[$covId])) {
                        $coverageGaps[] = $covLabel;
                    }
                }
            }
            $gapNames = array_slice($coverageGaps, 0, 5);
            $moreGaps = max(0, count($coverageGaps) - 5);
        @endphp
        @if (count($coverageGaps) > 0)
            <div class="p2-gap">
                <i class="fa fa-circle-exclamation"></i>
                <div>
                    <b>{{ __('stakeholder.lm.gap_label') }}:</b>
                    {{ sprintf(__('stakeholder.lm.gap_hint'), count($coverageGaps)) }}
                    <em>{{ implode(', ', $gapNames) }}@if ($moreGaps > 0) +{{ $moreGaps }} @endif</em>
                </div>
            </div>
        @endif
    @endif

    {{-- Off-strategy drift (strategy scope) --}}
    @if (($scope ?? '') === 'strategy' && count($unaligned) > 0)
        @php
            $unalignedNames = array_map(
                fn ($id) => $columns[$id]['name'] ?? ('#'.$id),
                array_slice($unaligned, 0, 5)
            );
            $moreDrift = max(0, count($unaligned) - 5);
        @endphp
        <div class="p2-drift">
            <i class="fa fa-diagram-project"></i>
            <div>
                <b>{{ __('stakeholder.lm.drift_label') }}:</b>
                {{ sprintf(__('stakeholder.lm.drift_hint'), count($unaligned)) }}
                <em>{{ implode(', ', $unalignedNames) }}@if ($moreDrift > 0) +{{ $moreDrift }} @endif</em>
            </div>
        </div>
    @endif

    </div>
@endif

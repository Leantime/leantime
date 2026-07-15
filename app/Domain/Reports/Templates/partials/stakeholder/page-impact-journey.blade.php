{{--
    Stakeholder Report — Page 4 (Impact Journey)

    "Not a tracker — a vision that becomes proof."

    One artifact, three lens views, tense-driven. Same bars, targets, and
    people stay on screen; the framing and tense change around them.

      Vision   (Day 1, always available)   — targets only, "will"
      Progress (unlocks with any snapshot) — captured + remaining, "is becoming"
      Impact   (unlocks when targets met)  — achieved, "did"

    Bookends both authored, both present Day 1:
      STARTED (the world / the problem) → the journey → DIFFERENT (the impact)

    Human meaning is AUTHORED on the canvas (item.description / item.assumptions
    / item.conclusion), never generated — §7 rule 12. A concatenated narrative
    is a fabrication when this ends up in a funder's hands.

    Per-lens content is rendered server-side and CSS-toggled by the parent
    wrapper's data-active-lens attribute — so the "same component, words &
    fill change" reads as one journey maturing, not hard cuts.

    Vars in:
      $logicModel  null | {narrative, coverageMatrix, projectLinks, linkedGoals, ...}
      $hasLM       bool
      $scope       'strategy' | 'program'
--}}

<style>
.rd-scope .p4-wrap{max-width:900px;margin:0 auto;}
.rd-scope .p4-wrap *{min-width:0;}

/* Lens visibility — active data-lens shows; others hide. One component per
   metric renders all three variants server-side; CSS reveals the current
   lens's version. Keeps the JS toggle trivial and the DOM stable. */
.rd-scope .p4-wrap[data-active-lens="vision"]   [data-lens]:not([data-lens="vision"])   {display:none;}
.rd-scope .p4-wrap[data-active-lens="progress"] [data-lens]:not([data-lens="progress"]) {display:none;}
.rd-scope .p4-wrap[data-active-lens="impact"]   [data-lens]:not([data-lens="impact"])   {display:none;}

/* Header — subject + lens toggle */
.rd-scope .p4-hd{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px;flex-wrap:wrap;}
.rd-scope .p4-hd .t{font-size:17px;font-weight:600;color:var(--rd-text-1);letter-spacing:-.2px;}
.rd-scope .p4-hd .s{font-size:12.5px;color:var(--rd-text-3);margin-top:2px;}
.rd-scope .p4-hd .exp{display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:600;color:var(--rd-accent);background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:20px;padding:7px 14px;cursor:pointer;flex:none;}
.rd-scope .p4-hd .exp:hover{background:var(--rd-bg);}

/* Lens toggle */
.rd-scope .p4-lens{display:flex;gap:0;margin-bottom:16px;background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:100px;padding:3px;width:fit-content;}
.rd-scope .p4-lens .lopt{background:none;border:0;padding:6px 14px;font-size:12.5px;font-weight:600;color:var(--rd-text-3);cursor:pointer;border-radius:100px;transition:all .12s ease;letter-spacing:.1px;}
.rd-scope .p4-lens .lopt.is-active{background:var(--rd-accent);color:#fff;}
.rd-scope .p4-lens .lopt:hover:not(.is-active):not(.locked){color:var(--rd-text-1);}
.rd-scope .p4-lens .lopt.locked{color:var(--rd-text-4);cursor:not-allowed;}
.rd-scope .p4-lens .lopt.locked i{margin-right:5px;font-size:10px;}

/* Bookend cards */
.rd-scope .p4-bookend{background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);padding:18px 22px;margin-bottom:14px;}
.rd-scope .p4-bookend.started{border-left:3px solid #9A6A11;}
.rd-scope .p4-bookend.different{border-left:3px solid var(--rd-ok);}
.rd-scope .p4-bookend .lb{font-size:10.5px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;margin-bottom:8px;display:flex;align-items:center;gap:7px;}
.rd-scope .p4-bookend.started .lb{color:#9A6A11;}
.rd-scope .p4-bookend.different .lb{color:var(--rd-ok);}
.rd-scope .p4-bookend .txt{font-size:15px;font-weight:600;color:var(--rd-text-1);line-height:1.4;}
.rd-scope .p4-bookend .meaning{font-size:13.5px;color:var(--rd-text-2);margin-top:8px;line-height:1.55;}
.rd-scope .p4-bookend .tense-hint{font-size:11.5px;color:var(--rd-text-3);margin-top:10px;font-style:italic;display:block;}
.rd-scope .p4-bookend .empty{font-size:13px;color:var(--rd-text-3);font-style:italic;}
.rd-scope .p4-bookend .empty .nudge{color:var(--rd-accent);font-style:normal;font-weight:600;margin-left:4px;}

/* Metrics — the arc */
.rd-scope .p4-arc{background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);padding:18px 22px;margin-bottom:14px;}
.rd-scope .p4-arc .lb{font-size:10.5px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--rd-text-3);margin-bottom:14px;display:flex;align-items:center;gap:7px;}
.rd-scope .p4-arc .lb i{font-size:11px;}
.rd-scope .p4-mgroup{margin-top:18px;}
.rd-scope .p4-mgroup:first-of-type{margin-top:0;}
.rd-scope .p4-mgroup .gh{font-size:10.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-text-3);margin-bottom:12px;display:flex;align-items:center;gap:7px;}
.rd-scope .p4-mgroup.outputs .gh{color:var(--rd-s3);}
.rd-scope .p4-mgroup.outcomes .gh{color:var(--rd-s4);}

/* Metric row */
.rd-scope .p4-metric{display:grid;grid-template-columns:minmax(0,1.4fr) 200px 140px;gap:16px;align-items:center;padding:12px 0;border-top:1px solid var(--rd-line-soft);}
.rd-scope .p4-metric:first-child{border-top:none;padding-top:2px;}
.rd-scope .p4-metric .mn{font-size:13.5px;font-weight:600;color:var(--rd-text-1);line-height:1.35;}
.rd-scope .p4-metric .mn .tgt{font-size:11.5px;color:var(--rd-text-3);font-weight:400;display:block;margin-top:2px;}
.rd-scope .p4-metric .mn .meaning{font-size:11.5px;color:var(--rd-text-3);font-style:italic;display:block;margin-top:6px;line-height:1.5;}

/* ────────────────────────────────────────────────────────────────────────
   The bar is ONE component per metric, persistent across lenses. The lens
   changes its STATE (fill amount + label + verdict), never its existence.
   Continuity is the reason lenses aren't tabs — the promise fills in as
   you switch, and that morph is the moment the page tells its story.
   ──────────────────────────────────────────────────────────────────────── */
.rd-scope .p4-arc-viz{display:flex;flex-direction:column;gap:6px;}
.rd-scope .p4-arc-viz .p4-scale{display:flex;justify-content:space-between;align-items:baseline;font-size:11px;font-variant-numeric:tabular-nums;line-height:1.2;}
.rd-scope .p4-arc-viz .p4-scale .p4-fill-lbl{font-weight:700;color:var(--rd-text-1);transition:opacity .18s ease;}
.rd-scope .p4-arc-viz .p4-scale .p4-target-lbl{color:var(--rd-text-3);font-size:10.5px;letter-spacing:.2px;}

/* The track: fixed length outline in the stage color at low opacity. This
   is the target — always visible, same shape across all lenses. */
.rd-scope .p4-track{position:relative;height:12px;background:#eef1f3;border-radius:6px;overflow:hidden;}
.rd-scope .p4-mgroup.outputs .p4-track{background:rgba(192,144,53,.14);}
.rd-scope .p4-mgroup.outcomes .p4-track{background:rgba(142,106,173,.14);}

/* The fill: rises from 0 to current/target on lens change. Animates via
   CSS transition; JS sets the inline width. Respects reduced motion. */
.rd-scope .p4-fill{position:absolute;top:0;left:0;bottom:0;width:0;border-radius:6px;transition:width 700ms cubic-bezier(.22,1,.36,1);}
.rd-scope .p4-mgroup.outputs .p4-fill{background:var(--rd-s3);}
.rd-scope .p4-mgroup.outcomes .p4-fill{background:var(--rd-s4);}

@media (prefers-reduced-motion: reduce) {
    .rd-scope .p4-fill,
    .rd-scope .p4-arc-viz .p4-scale .p4-fill-lbl {transition:none;}
}

/* Verdict — right column */
.rd-scope .p4-verdict{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;justify-self:end;white-space:nowrap;}
.rd-scope .p4-verdict .sd{width:7px;height:7px;border-radius:50%;background:currentColor;}
.rd-scope .p4-verdict.willbe{color:var(--rd-text-3);}
.rd-scope .p4-verdict.trending{color:#3F72B0;}
.rd-scope .p4-verdict.short{color:#9A6A11;}
.rd-scope .p4-verdict.hit{color:var(--rd-ok);}
.rd-scope .p4-verdict.captured{color:var(--rd-text-3);font-style:italic;font-weight:500;}

/* Encouragement chip */
.rd-scope .p4-chip{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--rd-text-3);background:var(--rd-bg);border-radius:20px;padding:4px 10px;margin-top:10px;}
.rd-scope .p4-chip i{font-size:9px;}

/* Empty state */
.rd-scope .p4-empty{background:var(--rd-panel);border:1px dashed var(--rd-line);border-radius:var(--rd-r-sm);padding:26px 22px;text-align:center;color:var(--rd-text-3);}
.rd-scope .p4-empty .lb{font-size:12.5px;color:var(--rd-text-2);margin-bottom:4px;font-weight:600;}
.rd-scope .p4-empty .s{font-size:12px;color:var(--rd-text-3);}

/* ────────────────────────────────────────────────────────────────────────
   Vision lens — three beats. NOT a table. NOT waiting for data. This is
   the shareable, screenshot-for-a-funder view: the world today (authored),
   the measurable arc as one prose statement (assembled from authored
   labels), the world when we've delivered (authored). No tracks, no
   status column, no bars — the Vision is complete on day one, by design.
   ──────────────────────────────────────────────────────────────────────── */
.rd-scope .p4-vision{margin-bottom:14px;}
.rd-scope .p4-vision .beat{background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);padding:24px 28px;margin-bottom:14px;}
.rd-scope .p4-vision .beat.today{border-left:3px solid #9A6A11;}
.rd-scope .p4-vision .beat.arc{border-left:3px solid var(--rd-accent);background:var(--rd-bg);}
.rd-scope .p4-vision .beat.delivered{border-left:3px solid var(--rd-ok);}
.rd-scope .p4-vision .beat .lb{font-size:10.5px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;margin-bottom:12px;display:flex;align-items:center;gap:7px;}
.rd-scope .p4-vision .beat.today .lb{color:#9A6A11;}
.rd-scope .p4-vision .beat.arc .lb{color:var(--rd-accent);}
.rd-scope .p4-vision .beat.delivered .lb{color:var(--rd-ok);}
.rd-scope .p4-vision .beat.today .txt,
.rd-scope .p4-vision .beat.delivered .txt{font-size:19px;font-weight:600;color:var(--rd-text-1);line-height:1.45;letter-spacing:-.2px;}
.rd-scope .p4-vision .beat.arc .statement{font-size:16px;font-weight:500;color:var(--rd-text-1);line-height:1.65;letter-spacing:-.05px;}
.rd-scope .p4-vision .beat.delivered .meaning{font-size:14px;color:var(--rd-text-2);margin-top:12px;line-height:1.55;}
.rd-scope .p4-vision .beat .empty{font-size:13.5px;color:var(--rd-text-3);font-style:italic;}
.rd-scope .p4-vision .beat .empty .nudge{color:var(--rd-accent);font-style:normal;font-weight:600;margin-left:4px;}

/* Block-level lens visibility — the beats show on Vision only, the tracks
   block shows on Progress/Impact only. Per-element [data-lens] rules
   still apply INSIDE each block (verdicts, tense hints). */
.rd-scope .p4-wrap[data-active-lens="vision"]   [data-lens-block="tracks"]{display:none;}
.rd-scope .p4-wrap[data-active-lens="progress"] [data-lens-block="vision"]{display:none;}
.rd-scope .p4-wrap[data-active-lens="impact"]   [data-lens-block="vision"]{display:none;}

/* Arrival — when the reader flips Vision → Progress/Impact, the arc
   statement recedes (block swap is instant) and the metric rows stagger in.
   `.arriving` is added by JS immediately after the block swap; the row is
   already opacity:1 in its resting state so SSR-rendered non-Vision pages
   (returning readers) never see a flash. */
.rd-scope .p4-metric.arriving{opacity:0;transform:translateY(6px);}
.rd-scope .p4-metric.arrived{opacity:1;transform:none;transition:opacity 260ms ease, transform 260ms ease;}
@media (prefers-reduced-motion: reduce) {
    .rd-scope .p4-metric.arriving,
    .rd-scope .p4-metric.arrived {opacity:1;transform:none;transition:none;}
}
</style>

@if (! $hasLM)
    <div class="p4-wrap">
        <div class="p4-empty">
            <div class="lb">{{ __('stakeholder.ij.no_lm_title') }}</div>
            <div class="s">{{ __('stakeholder.ij.no_lm_hint') }}</div>
        </div>
    </div>
@else
    @php
        $stages       = $logicModel['coverageMatrix']['stages'] ?? [];
        $projectLinks = $logicModel['projectLinks'] ?? [];
        $linkedGoals  = $logicModel['linkedGoals'] ?? [];

        $outputItems  = $stages['outputs']['items'] ?? [];
        $outcomeItems = $stages['outcomes']['items'] ?? [];
        $impactItems  = $stages['impact']['items'] ?? [];

        // Metric aggregator (unchanged from before; comment retained for context).
        $metricFor = function ($item) use ($projectLinks, $linkedGoals) {
            $itemId = (int) (((array) $item)['id'] ?? 0);
            $links = $projectLinks[$itemId] ?? [];
            $goals = [];
            foreach ($links as $link) {
                if (($link['linked_entity_type'] ?? '') !== 'goal') continue;
                $gid = (int) ($link['linked_entity_id'] ?? 0);
                if (isset($linkedGoals[$gid])) $goals[] = $linkedGoals[$gid];
            }
            if (count($goals) === 0) return null;

            $current = array_sum(array_column($goals, 'currentValue'));
            $target  = array_sum(array_column($goals, 'endValue'));
            $unit    = $goals[0]['metricType'] ?? 'number';

            $byDate = [];
            foreach ($goals as $g) {
                foreach (($g['snapshots'] ?? []) as $s) {
                    $day = substr((string) $s['date'], 0, 10);
                    if (! isset($byDate[$day])) $byDate[$day] = 0.0;
                    $byDate[$day] += (float) $s['value'];
                }
            }
            ksort($byDate);
            $snapshots = [];
            foreach ($byDate as $day => $value) $snapshots[] = ['date' => $day, 'value' => $value];

            $arr = (array) $item;
            // Meaning source per §1 of the authored-meaning spec:
            //   why_this_matters  — the primary source (authored, nullable)
            // No fallback to conclusion — conclusion narrows to "as measured
            // by" methodology only, going forward. Mining it for meaning is
            // exactly the double-duty that broke Page 2 earlier.
            return [
                'id'         => $itemId,
                'label'      => trim((string) ($arr['description'] ?? '')),
                'meaning'    => trim((string) ($arr['why_this_matters'] ?? '')),
                'measuredBy' => trim((string) ($arr['conclusion'] ?? '')),
                'current'    => $current,
                'target'     => $target,
                'unit'       => $unit,
                'snapshots'  => $snapshots,
            ];
        };

        $anySnapshots = false;
        $collectMetrics = function (array $items) use ($metricFor, &$anySnapshots) {
            $metrics = [];
            foreach ($items as $it) {
                $m = $metricFor($it);
                if ($m === null) continue;
                if (count($m['snapshots']) > 0) $anySnapshots = true;
                $metrics[] = $m;
            }
            return $metrics;
        };
        $outMetrics = $collectMetrics($outputItems);
        $ocMetrics  = $collectMetrics($outcomeItems);

        $anyHit = false;
        foreach (array_merge($outMetrics, $ocMetrics) as $m) {
            if ($m['target'] > 0 && $m['current'] >= $m['target']) { $anyHit = true; break; }
        }
        $progressUnlocked = $anySnapshots;
        $impactUnlocked   = $anyHit;

        // Default lens = the most advanced state the data supports. A page
        // with snapshots defaults to Progress (not Vision) so a returning
        // reader lands on the current reality — Vision is reachable via
        // the toggle for the "share the promise" flow.
        $defaultLens = $impactUnlocked ? 'impact' : ($progressUnlocked ? 'progress' : 'vision');

        $fmt = function ($value, string $unit) {
            $value = (float) $value;
            if ($unit === 'percent') return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.').'%';
            if ($value == floor($value)) return number_format($value, 0, '.', ',');
            return number_format($value, 1, '.', ',');
        };

        // Detects whether the authored label starts with the target number
        // (e.g. "1,200 screenings completed" for target 1,200). Used to
        // suppress the redundant "target N" subtitle on Vision — the label
        // is already the promise.
        $labelContainsTarget = function (string $label, float $target, string $unit) use ($fmt): bool {
            if ($target <= 0) return false;
            $formatted = $fmt($target, $unit);
            // strip commas for a looser match too
            $stripped = str_replace(',', '', $formatted);
            $labelStripped = str_replace(',', '', $label);
            return stripos($labelStripped, $stripped) !== false;
        };

        // ── STARTED bookend text.
        // Authored ONLY. Sourced from the Impact item's `starting_picture`
        // (a dedicated field for the world today, before this work). No
        // synthesis, no fallback to narrative concatenation — an empty state
        // is more honest than a fabrication, and this artifact ends up in
        // funders' hands.
        $startedText = '';
        if (count($impactItems) > 0) {
            $startedText = trim((string) (((array) $impactItems[0])['starting_picture'] ?? ''));
        }

        // ── DIFFERENT bookend: authored impact title + authored meaning.
        // Meaning source: why_this_matters ONLY. No fallback to conclusion
        // (which is now "as measured by" methodology). No fallback to
        // assumptions (that's for the theory-of-change assertion, a
        // different concept from the funder-facing meaning).
        $differentTitle = '';
        $differentMeaning = '';
        if (count($impactItems) > 0) {
            $impArr = (array) $impactItems[0];
            $differentTitle   = trim((string) ($impArr['description'] ?? ''));
            $differentMeaning = trim((string) ($impArr['why_this_matters'] ?? ''));
        }

        // ── Arc statement (Vision beat 2). The ONE place a light
        // concatenation is correct: assembled from authored labels, capped
        // at 3 producing + 2 achieving, two sentences maximum. Never the
        // §8 rule-1 canvas dump. Nothing generated — every word is a label
        // a human wrote on the canvas.
        $capProducing = array_slice($outMetrics, 0, 3);
        $capAchieving = array_slice($ocMetrics, 0, 2);
        $producingLabels = array_values(array_filter(array_map(
            static fn ($m) => trim((string) $m['label']),
            $capProducing
        )));
        $achievingLabels = array_values(array_filter(array_map(
            static fn ($m) => trim((string) $m['label']),
            $capAchieving
        )));

        $producingSentence = $producingLabels === []
            ? ''
            : implode('. ', $producingLabels).'.';

        $achievingSentence = '';
        if (count($achievingLabels) === 1) {
            $achievingSentence = sprintf(__('stakeholder.ij.beat_arc_leading_to'), $achievingLabels[0]).'.';
        } elseif (count($achievingLabels) >= 2) {
            // Natural join: "X and Y" for two, "X, Y, and Z" for three (only
            // if the cap is ever raised).
            if (count($achievingLabels) === 2) {
                $joined = $achievingLabels[0].' '.__('stakeholder.ij.beat_arc_and').' '.$achievingLabels[1];
            } else {
                $joined = implode(', ', array_slice($achievingLabels, 0, -1))
                    .', '.__('stakeholder.ij.beat_arc_and').' '.end($achievingLabels);
            }
            $achievingSentence = sprintf(__('stakeholder.ij.beat_arc_leading_to'), $joined).'.';
        }

        $arcStatement = trim($producingSentence.' '.$achievingSentence);
    @endphp

    <div class="p4-wrap" data-active-lens="{{ $defaultLens }}" data-p4-lens-wrap>

        {{-- Header --}}
        <div class="p4-hd">
            <div>
                <div class="t">{{ __('stakeholder.ij.header_title') }}</div>
                <div class="s">{{ __('stakeholder.ij.header_sub') }}</div>
            </div>
            <button type="button" class="exp" data-tippy-content="{{ __('stakeholder.ij.export_hint') }}">
                <i class="fa fa-file-arrow-down"></i> {{ __('stakeholder.ij.export_btn') }}
            </button>
        </div>

        {{-- Lens toggle — shows only when >=2 lenses exist for the reader --}}
        @if ($progressUnlocked || $impactUnlocked)
            <div class="p4-lens" data-p4-lens>
                <button type="button" class="lopt @if ($defaultLens === 'vision') is-active @endif" data-lens-target="vision">{{ __('stakeholder.ij.lens_vision') }}</button>
                <button type="button" class="lopt @if ($defaultLens === 'progress') is-active @endif @if (! $progressUnlocked) locked @endif" data-lens-target="progress" @if (! $progressUnlocked) disabled @endif>
                    @if (! $progressUnlocked)<i class="fa fa-lock"></i>@endif
                    {{ __('stakeholder.ij.lens_progress') }}
                </button>
                <button type="button" class="lopt @if ($defaultLens === 'impact') is-active @endif @if (! $impactUnlocked) locked @endif" data-lens-target="impact" @if (! $impactUnlocked) disabled @endif>
                    @if (! $impactUnlocked)<i class="fa fa-lock"></i>@endif
                    {{ __('stakeholder.ij.lens_impact') }}
                </button>
            </div>
        @endif

        {{-- ── VISION lens: three beats. Complete on day one, by design.
             Beat 1 (world today) and beat 3 (world delivered) carry the
             same authored text as the STARTED/DIFFERENT bookends below
             — the block-level lens toggle means only one shows at a
             time, so there is no duplication for the reader. --}}
        <div class="p4-vision" data-lens-block="vision">
            <div class="beat today">
                <div class="lb"><i class="fa fa-flag"></i> {{ __('stakeholder.ij.beat_today_lb') }}</div>
                @if ($startedText !== '')
                    <div class="txt">{{ $startedText }}</div>
                @else
                    <div class="empty">
                        {{ __('stakeholder.ij.bookend_started_empty') }}
                        <span class="nudge">{{ __('stakeholder.ij.bookend_started_nudge') }}</span>
                    </div>
                @endif
            </div>

            <div class="beat arc">
                <div class="lb"><i class="fa fa-arrow-trend-up"></i> {{ __('stakeholder.ij.beat_arc_lb') }}</div>
                @if ($arcStatement !== '')
                    <div class="statement">{{ $arcStatement }}</div>
                @else
                    <div class="empty">
                        {{ __('stakeholder.ij.beat_arc_empty') }}
                        <span class="nudge">{{ __('stakeholder.ij.beat_arc_empty_nudge') }}</span>
                    </div>
                @endif
            </div>

            <div class="beat delivered">
                <div class="lb"><i class="fa fa-bullseye"></i> {{ __('stakeholder.ij.beat_delivered_lb') }}</div>
                @if ($differentTitle !== '')
                    <div class="txt">{{ $differentTitle }}</div>
                    @if ($differentMeaning !== '')
                        <div class="meaning">{{ $differentMeaning }}</div>
                    @endif
                @else
                    <div class="empty">
                        {{ __('stakeholder.ij.bookend_different_empty') }}
                        <span class="nudge">{{ __('stakeholder.ij.bookend_different_nudge') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── PROGRESS + IMPACT: the same bookends flanking the arc with
             live tracks. Hidden entirely on the Vision lens by the
             block-level toggle above. --}}
        <div data-lens-block="tracks">

        {{-- STARTED bookend — authored problem text or honest empty state.
             NO narrative dump. NO generated summary. --}}
        <div class="p4-bookend started">
            <div class="lb"><i class="fa fa-flag"></i> {{ __('stakeholder.ij.bookend_started') }}</div>
            @if ($startedText !== '')
                <div class="meaning" style="margin-top:0;">{{ $startedText }}</div>
            @else
                <div class="empty">
                    {{ __('stakeholder.ij.bookend_started_empty') }}
                    <span class="nudge">{{ __('stakeholder.ij.bookend_started_nudge') }}</span>
                </div>
            @endif
        </div>

        {{-- The Arc --}}
        @if (count($outMetrics) > 0 || count($ocMetrics) > 0)
            <div class="p4-arc">
                <div class="lb"><i class="fa fa-arrow-trend-up"></i> {{ __('stakeholder.ij.arc_label') }}</div>

                @foreach ([
                    ['key'=>'outputs',  'items'=>$outMetrics,  'title'=>__('stakeholder.ij.arc_producing'),  'icon'=>'fa-boxes-stacked'],
                    ['key'=>'outcomes', 'items'=>$ocMetrics,   'title'=>__('stakeholder.ij.arc_achieving'), 'icon'=>'fa-chart-line'],
                ] as $group)
                    @if (count($group['items']) === 0) @continue @endif
                    <div class="p4-mgroup {{ $group['key'] }}">
                        <div class="gh"><i class="fa {{ $group['icon'] }}"></i> {{ $group['title'] }}</div>

                        @foreach ($group['items'] as $m)
                            @php
                                $n = count($m['snapshots']);
                                $growthState = $n <= 1 ? 'baseline' : ($n === 2 ? 'before_after' : 'full_arc');
                                $hitTarget = $m['target'] > 0 && $m['current'] >= $m['target'];
                                $peak = max($m['target'], $m['current'], 1);
                                foreach ($m['snapshots'] as $s) $peak = max($peak, (float) $s['value']);
                                $barH = fn ($v) => max(4, min(50, (int) round(($v / $peak) * 50)));
                                $labelHasTarget = $labelContainsTarget($m['label'], $m['target'], $m['unit']);
                                $short = $m['target'] > 0 ? $m['target'] - $m['current'] : 0;
                            @endphp
                            <div class="p4-metric">
                                <div class="mn">
                                    @if ($m['meaning'] !== '')
                                        {{-- Meaning leads. The metric is evidence.
                                             "as measured by {label}" reads as the receipt
                                             underneath — same rule as Page 2's verdict + read
                                             pattern, one level down. --}}
                                        {{ $m['meaning'] }}
                                        <span class="meaning">
                                            {{ __('stakeholder.ij.as_measured_by') }} {{ $m['label'] }}
                                            @if ($m['measuredBy'] !== '')
                                                — {{ $m['measuredBy'] }}
                                            @endif
                                        </span>
                                    @else
                                        {{-- No authored meaning yet: today's behavior — the
                                             authored label leads, optional methodology below.
                                             No regression. --}}
                                        {{ $m['label'] }}
                                        @if (! $labelHasTarget && $m['target'] > 0)
                                            <span class="tgt">{{ __('stakeholder.ij.target_lbl') }} {{ $fmt($m['target'], $m['unit']) }}</span>
                                        @endif
                                        @if ($m['measuredBy'] !== '')
                                            <span class="meaning">{{ __('stakeholder.ij.as_measured_by') }} {{ $m['measuredBy'] }}</span>
                                        @endif
                                    @endif
                                </div>

                                {{-- ONE persistent bar per metric. Track = the target
                                     (always visible, same shape all lenses). Fill = current
                                     progress, animates on lens change via CSS transition.
                                     Vision → fill 0%. Progress → fill current/target. Impact
                                     → fill 100%. The morph IS the story. --}}
                                @php
                                    // Cap the visual fill % — over-target still reads as full,
                                    // "we did this" carries the over-delivery in the value label.
                                    $progressPct = $m['target'] > 0
                                        ? min(100, ($m['current'] / $m['target']) * 100)
                                        : 0;
                                    // Fill values per lens — the JS uses these dataset attrs
                                    // to swap width + label text on lens change.
                                    $visionFill    = 0;
                                    $progressFill  = $progressPct;
                                    $impactFill    = 100;
                                    $visionLabel   = __('stakeholder.ij.fill_lbl_vision');
                                    $progressLabel = sprintf(__('stakeholder.ij.fill_lbl_progress'), $fmt($m['current'], $m['unit']));
                                    $impactLabel   = sprintf(__('stakeholder.ij.fill_lbl_impact'), $fmt(max($m['current'], $m['target']), $m['unit']));
                                @endphp
                                <div class="p4-arc-viz"
                                     data-p4-bar
                                     data-vision-fill="{{ $visionFill }}"
                                     data-progress-fill="{{ $progressFill }}"
                                     data-impact-fill="{{ $impactFill }}"
                                     data-vision-lbl="{{ $visionLabel }}"
                                     data-progress-lbl="{{ $progressLabel }}"
                                     data-impact-lbl="{{ $impactLabel }}">
                                    <div class="p4-scale">
                                        <span class="p4-fill-lbl">{{
                                            $defaultLens === 'vision'   ? $visionLabel
                                          : ($defaultLens === 'impact'  ? $impactLabel : $progressLabel)
                                        }}</span>
                                        <span class="p4-target-lbl">{{ __('stakeholder.ij.target_lbl') }} {{ $fmt($m['target'], $m['unit']) }}</span>
                                    </div>
                                    <div class="p4-track">
                                        <div class="p4-fill" style="width:{{
                                            $defaultLens === 'vision'   ? $visionFill
                                          : ($defaultLens === 'impact'  ? $impactFill : $progressFill)
                                        }}%;"></div>
                                    </div>
                                </div>

                                {{-- ─── VERDICTS — tense-aware, encouragement via structure ── --}}
                                {{-- Vision: future tense. --}}
                                <span class="p4-verdict willbe" data-lens="vision">
                                    <span class="sd"></span> {{ __('stakeholder.ij.v_will_be_measured') }}
                                </span>
                                {{-- Progress: present-becoming tense. One framing for both
                                     units — "N% of the way" reads truthfully for counts and
                                     percents alike, and stays consistent down the column
                                     regardless of metric type. --}}
                                <span class="p4-verdict @if ($hitTarget) hit @elseif ($n === 0) captured @elseif ($m['target'] > 0 && ($short / max($m['target'], 1)) > 0.25) short @else trending @endif" data-lens="progress">
                                    <span class="sd"></span>
                                    @if ($n === 0)
                                        {{ __('stakeholder.ij.v_no_snapshots_yet') }}
                                    @elseif ($hitTarget)
                                        {{ __('stakeholder.ij.v_hit_target_progress') }}
                                    @elseif ($m['target'] > 0)
                                        {{ sprintf(__('stakeholder.ij.v_pct_of_way'), (int) round(($m['current'] / $m['target']) * 100)) }}
                                    @else
                                        {{ __('stakeholder.ij.v_trending') }}
                                    @endif
                                </span>
                                {{-- Impact: past tense. Only truthful for hit-target rows. --}}
                                <span class="p4-verdict @if ($hitTarget) hit @else captured @endif" data-lens="impact">
                                    <span class="sd"></span>
                                    @if ($hitTarget)
                                        {{ __('stakeholder.ij.v_we_did_this') }}
                                    @else
                                        {{ __('stakeholder.ij.v_not_yet_impact') }}
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                {{-- Structural encouragement — only when the claim can be
                     reconstructed from what's on screen (§7 rule 8 / v6 Fix 2).
                     Progress-lens "one more completes the arc" needed a period
                     cadence the page doesn't render yet; dropped until we do. --}}
                @if (! $progressUnlocked)
                    <div class="p4-chip" data-lens="vision"><i class="fa fa-circle-info"></i> {{ __('stakeholder.ij.chip_first_period') }}</div>
                @endif
            </div>
        @else
            <div class="p4-empty">
                <div class="lb">{{ __('stakeholder.ij.no_metrics_title') }}</div>
                <div class="s">{{ __('stakeholder.ij.no_metrics_hint') }}</div>
            </div>
        @endif

        {{-- DIFFERENT bookend — authored impact + supporting meaning.
             Meaning is the emotional payload: WHY this matters to the
             people it affects. Never generated. --}}
        <div class="p4-bookend different">
            <div class="lb"><i class="fa fa-bullseye"></i> {{ __('stakeholder.ij.bookend_different') }}</div>
            @if ($differentTitle !== '')
                <div class="txt">{{ $differentTitle }}</div>
                @if ($differentMeaning !== '')
                    <div class="meaning">{{ $differentMeaning }}</div>
                @endif
                {{-- Tense hints — one per lens, framing shifts with capture state. --}}
                <span class="tense-hint" data-lens="vision">{{ __('stakeholder.ij.different_tense_vision') }}</span>
                <span class="tense-hint" data-lens="progress">{{ __('stakeholder.ij.different_tense_progress') }}</span>
                <span class="tense-hint" data-lens="impact">{{ __('stakeholder.ij.different_tense_impact') }}</span>
            @else
                <div class="empty">
                    {{ __('stakeholder.ij.bookend_different_empty') }}
                    <span class="nudge">{{ __('stakeholder.ij.bookend_different_nudge') }}</span>
                </div>
            @endif
        </div>

        </div>{{-- /[data-lens-block=tracks] --}}

    </div>

    <script>
    (function () {
        var wrap   = document.querySelector('[data-p4-lens-wrap]');
        var toggle = document.querySelector('[data-p4-lens]');
        if (! wrap || ! toggle) return;

        var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Morph a single bar to the target lens: swap fill width + label text.
        // Width transition is CSS-driven; label crossfade is a quick opacity dip.
        function morphBar (viz, lens) {
            var fillEl = viz.querySelector('.p4-fill');
            var lblEl  = viz.querySelector('.p4-fill-lbl');
            if (! fillEl || ! lblEl) return;
            var pct = viz.getAttribute('data-' + lens + '-fill');
            var lbl = viz.getAttribute('data-' + lens + '-lbl');
            if (pct !== null) fillEl.style.width = pct + '%';
            if (lbl !== null) {
                if (reducedMotion) {
                    lblEl.textContent = lbl;
                } else {
                    lblEl.style.opacity = '0';
                    setTimeout(function () { lblEl.textContent = lbl; lblEl.style.opacity = '1'; }, 140);
                }
            }
        }

        function switchLens (lens) {
            var wasVision = wrap.getAttribute('data-active-lens') === 'vision';
            wrap.setAttribute('data-active-lens', lens);

            // Arrival — when leaving Vision for Progress/Impact, the tracks
            // don't just appear (that would be a jump); they stagger in.
            // Rows start hidden (.arriving), then .arrived is added on a
            // stagger so the CSS transition plays. Reduced-motion → instant.
            if (wasVision && lens !== 'vision' && ! reducedMotion) {
                var rows = wrap.querySelectorAll('[data-lens-block="tracks"] .p4-metric');
                rows.forEach(function (row) { row.classList.remove('arrived'); row.classList.add('arriving'); });
                rows.forEach(function (row, i) {
                    setTimeout(function () {
                        row.classList.remove('arriving');
                        row.classList.add('arrived');
                    }, i * 80);
                });
            }

            var bars = wrap.querySelectorAll('[data-p4-bar]');
            bars.forEach(function (viz, i) {
                // Stagger by 80ms so the morph reads as a sequence, not a jump.
                var delay = reducedMotion ? 0 : (i * 80);
                if (delay === 0) morphBar(viz, lens);
                else setTimeout(function () { morphBar(viz, lens); }, delay);
            });
        }

        toggle.addEventListener('click', function (e) {
            var btn = e.target.closest('.lopt');
            if (! btn || btn.classList.contains('locked') || btn.hasAttribute('disabled')) return;
            var target = btn.getAttribute('data-lens-target');
            if (! target) return;
            toggle.querySelectorAll('.lopt').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
            switchLens(target);
        });
    })();
    </script>
@endif

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
.rd-scope .p1-toc .tl{margin-bottom:6px;font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-s5);display:flex;align-items:center;gap:6px;}
.rd-scope .p1-toc .tl .lbl-inner{display:inline-flex;align-items:center;gap:6px;flex:1;}
.rd-scope .p1-toc .tl .toc-toggle{background:none;border:none;padding:2px 6px;margin-left:auto;cursor:pointer;color:var(--rd-text-3);font-size:10px;line-height:1;border-radius:4px;}
.rd-scope .p1-toc .tl .toc-toggle:hover{background:var(--rd-bg);color:var(--rd-text-1);}
.rd-scope .p1-toc .tl .toc-toggle i{transition:transform .18s;display:inline-block;}
.rd-scope .p1-toc.collapsed .tl .toc-toggle i{transform:rotate(-90deg);}
.rd-scope .p1-toc .tx{font-size:13.5px;line-height:1.6;color:var(--rd-text-2);overflow:hidden;max-height:600px;transition:max-height .22s ease,opacity .18s ease,margin .18s ease,padding .18s ease;margin-top:6px;}
.rd-scope .p1-toc.collapsed .tx{max-height:0;opacity:0;margin:0;padding:0;pointer-events:none;}
.rd-scope .p1-toc.collapsed{padding-bottom:8px;}
.rd-scope .p1-toc .n1{color:var(--rd-s1);font-weight:600;}
.rd-scope .p1-toc .n2{color:var(--rd-s2);font-weight:600;}
.rd-scope .p1-toc .n3{color:var(--rd-s3);font-weight:600;}
.rd-scope .p1-toc .n4{color:var(--rd-s4);font-weight:600;}
.rd-scope .p1-toc .n5{color:var(--rd-s5);font-weight:600;}
.rd-scope .p1-toc.empty{color:var(--rd-text-3);font-style:italic;border-left-color:var(--rd-line);}

/* Color legend popover — hover the info icon on the ToC label to show
   which color maps to which stage. Vanilla CSS hover; no JS needed. */
.rd-scope .p1-info{position:relative;display:inline-flex;}
.rd-scope .p1-info .ii{width:14px;height:14px;border-radius:50%;background:var(--rd-line-soft);color:var(--rd-text-3);display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;cursor:help;text-transform:none;letter-spacing:0;}
.rd-scope .p1-info .ii:hover{background:var(--rd-line);color:var(--rd-text-1);}
.rd-scope .p1-info .pop{position:absolute;top:calc(100% + 8px);left:-6px;background:var(--rd-panel);border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);box-shadow:var(--rd-sh-lg);padding:10px 12px;width:220px;font-size:11.5px;line-height:1.4;color:var(--rd-text-2);z-index:50;opacity:0;visibility:hidden;transform:translateY(-3px);transition:opacity .12s,transform .12s,visibility .12s;text-transform:none;letter-spacing:0;font-weight:400;}
.rd-scope .p1-info:hover .pop,
.rd-scope .p1-info:focus-within .pop{opacity:1;visibility:visible;transform:translateY(0);}
.rd-scope .p1-info .pop .h{font-size:10px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--rd-text-3);margin-bottom:6px;}
.rd-scope .p1-info .pop .lg{display:flex;align-items:center;gap:8px;padding:3px 0;}
.rd-scope .p1-info .pop .lg .sw{width:12px;height:12px;border-radius:3px;flex:none;}
.rd-scope .p1-info .pop .lg .nm{font-weight:600;color:var(--rd-text-1);}
.rd-scope .p1-info .pop .lg .sub{color:var(--rd-text-3);margin-left:auto;font-size:10.5px;}

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

/* Theory-health strip — a summary bar showing all 4 stage-to-stage links at
   once (segmented by status), plus the detail on any fragile link beneath.
   Reads positive-first: the segments read left→right so a healthy chain
   shows mostly green; a fragile chain has an amber segment mid-flow. */
.rd-scope .p1-theory{border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);padding:11px 15px;background:var(--rd-panel);}
.rd-scope .p1-theory .hd{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:10px;}
.rd-scope .p1-theory .hd .l{font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-text-3);}
.rd-scope .p1-theory .hd .rd{font-size:12px;color:var(--rd-text-2);}
.rd-scope .p1-theory .hd .rd b{color:var(--rd-text-1);font-weight:600;}
.rd-scope .p1-theory .hd .rd.risk b{color:var(--rd-warn-tx);}
/* Chain layout — 5 stage chips with 4 colored connector "arrows" between
   them. Each connector is a horizontal band with the health icon as a badge
   sitting on top. Reads left-to-right as a mini logic model with per-link
   health surfaced by icon color + prominence (not text). */
.rd-scope .p1-theory .segs{display:flex;align-items:center;gap:0;margin-bottom:12px;padding:2px 0;}
.rd-scope .p1-theory .stage{flex:none;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:600;color:#fff;letter-spacing:.1px;white-space:nowrap;}
.rd-scope .p1-theory .stage.s1{background:var(--rd-s1);}
.rd-scope .p1-theory .stage.s2{background:var(--rd-s2);}
.rd-scope .p1-theory .stage.s3{background:var(--rd-s3);}
.rd-scope .p1-theory .stage.s4{background:var(--rd-s4);}
.rd-scope .p1-theory .stage.s5{background:var(--rd-s5);}
.rd-scope .p1-theory .conn{position:relative;flex:1;min-width:24px;height:3px;margin:0 4px;border-radius:2px;cursor:default;}
.rd-scope .p1-theory .conn.ok{background:#3E937A;}
.rd-scope .p1-theory .conn.warning{background:#C09035;}
.rd-scope .p1-theory .conn.risk{background:var(--rd-danger);}
.rd-scope .p1-theory .conn.none{background:var(--rd-line);}
/* Badge — panel-white circle with a small colored icon centered inside. The
   badge circle carries the boundary; the icon sits at a uniform size that
   works for any glyph shape (circles, triangles, etc.) so no icon looks
   larger, smaller, or off-baseline relative to the others. */
.rd-scope .p1-theory .conn .badge{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:24px;height:24px;border-radius:50%;background:var(--rd-panel);display:grid;place-items:center;}
.rd-scope .p1-theory .conn .badge i{font-size:14px;line-height:1;display:block;}
.rd-scope .p1-theory .conn.ok .badge i{color:#3E937A;}
.rd-scope .p1-theory .conn.warning .badge i{color:#C09035;}
.rd-scope .p1-theory .conn.risk .badge i{color:var(--rd-danger);}
.rd-scope .p1-theory .conn.none .badge i{color:var(--rd-text-3);}
.rd-scope .p1-theory .conn .tip{position:absolute;bottom:calc(100% + 12px);left:50%;transform:translateX(-50%) translateY(4px);background:var(--rd-text-1);color:#fff;font-size:11px;font-weight:400;padding:8px 10px;border-radius:6px;line-height:1.4;box-shadow:var(--rd-sh-lg);opacity:0;visibility:hidden;transition:opacity .12s,transform .12s,visibility .12s;z-index:10;text-align:left;pointer-events:none;width:220px;}
.rd-scope .p1-theory .conn .tip::after{content:"";position:absolute;top:100%;left:50%;transform:translateX(-50%);border:5px solid transparent;border-top-color:var(--rd-text-1);}
.rd-scope .p1-theory .conn .tip b{color:#fff;font-weight:600;display:block;margin-bottom:3px;}
.rd-scope .p1-theory .conn .tip em{font-style:italic;color:rgba(255,255,255,.85);}
.rd-scope .p1-theory .conn:hover .tip{opacity:1;visibility:visible;transform:translateX(-50%) translateY(0);}
.rd-scope .p1-theory .detail{display:flex;gap:10px;align-items:flex-start;font-size:13.5px;color:var(--rd-warn-tx);background:var(--rd-warn-bg);border-radius:var(--rd-r-xs);padding:12px 15px;line-height:1.55;}
.rd-scope .p1-theory .detail.crit{color:var(--rd-danger);background:var(--rd-danger-bg);}
.rd-scope .p1-theory .detail.crit i{color:var(--rd-danger);}
.rd-scope .p1-theory .detail i{font-size:13px;color:#b8860b;margin-top:3px;flex:none;}
.rd-scope .p1-theory .detail b{font-weight:600;}
.rd-scope .p1-theory .detail b{color:var(--rd-text-1);font-weight:600;}
.rd-scope .p1-theory .detail em{font-style:italic;color:var(--rd-text-2);}
.rd-scope .p1-theory .calm-line{display:flex;gap:8px;align-items:center;font-size:12.5px;color:var(--rd-text-2);}
.rd-scope .p1-theory .calm-line i{color:var(--rd-ok);font-size:12px;}

/* "Assumption unproven" pill — a proper visible state chip on its own line,
   not an inline dashed-underlined phrase. Icon + label reads as a status,
   hover reveals the "how to fix" explainer. */
.rd-scope .p1-theory .detail-info{position:relative;display:inline-flex;align-items:center;gap:5px;margin-left:0;}
/* Sits inline with the assumption text — vertical-align:middle + a small left
   margin keeps it on the same visual line without pushing to a new row. */
.rd-scope .p1-theory .detail-info.evidence-tag{display:inline-flex;vertical-align:middle;margin:0 0 0 6px;padding:3px 10px;border-radius:20px;background:rgba(194,41,91,.11);cursor:help;font-weight:600;font-size:12px;color:var(--rd-danger);line-height:1.2;}
.rd-scope .p1-theory .detail:not(.crit) .detail-info.evidence-tag{background:rgba(192,144,53,.13);color:var(--rd-warn-tx);}
.rd-scope .p1-theory .detail-info.evidence-tag i{font-size:11px;opacity:.9;}
.rd-scope .p1-theory .detail-info.evidence-tag .tag{font-weight:600;border-bottom:none;opacity:1;font-size:12px;letter-spacing:.1px;}
.rd-scope .p1-theory .detail-info .lk{font-weight:600;color:inherit;border-bottom:1px dashed currentColor;cursor:help;opacity:.75;font-size:12.5px;}
/* Reset top to auto — without this, the .p1-info base rule's `top: calc(100% + 8px)`
   still applies (both classes are on the element), and the popover stretches
   across the trigger instead of anchoring to bottom. */
.rd-scope .p1-theory .detail-info .pop{position:absolute;top:auto;bottom:calc(100% + 6px);left:0;background:var(--rd-text-1);color:#fff;font-size:12.5px;font-weight:400;padding:10px 12px;border-radius:6px;line-height:1.5;box-shadow:var(--rd-sh-lg);opacity:0;visibility:hidden;transform:translateY(3px);transition:opacity .12s,transform .12s,visibility .12s;z-index:10;text-align:left;pointer-events:none;width:280px;font-style:normal;letter-spacing:0;}
/* These tooltips use var(--rd-text-1) as their fill — which INVERTS to a
   light bg in dark mode, hiding the white text. Pin a fixed dark fill. */
.rd-scope.rd-dark .p1-theory .conn .tip,
.rd-scope.rd-dark .p1-theory .detail-info .pop{background:#0d0e0f;color:#e8eaec;}
.rd-scope .p1-theory .detail-info:hover .pop,
.rd-scope .p1-theory .detail-info:focus-within .pop{opacity:1;visibility:visible;transform:translateY(0);}
.rd-scope .p1-theory .detail-info .pop .h{font-size:10.5px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;}
.rd-scope .p1-theory .detail-info .pop .lg{display:flex;align-items:center;gap:8px;padding:4px 0;font-weight:400;color:#fff;}
.rd-scope .p1-theory .detail-info .pop .lg i{font-size:13px;flex:none;}
.rd-scope .p1-theory .detail-info .pop .lg .nm{font-weight:600;color:#fff;flex:1;min-width:0;font-size:12.5px;}
.rd-scope .p1-theory .detail-info .pop .lg .sub{color:rgba(255,255,255,.65);font-size:11.5px;text-transform:none;}

/* "Also fragile" chip row — sits under the primary callout, one chip per
   additional fragile link so all fragile signals are visible without a hover. */
.rd-scope .p1-theory .also-fragile{display:flex;align-items:center;gap:8px;margin-top:8px;padding:0 3px;font-size:12.5px;flex-wrap:wrap;}
.rd-scope .p1-theory .also-fragile .lbl{font-size:10.5px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--rd-text-3);}
.rd-scope .p1-theory .af-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:500;cursor:default;}
.rd-scope .p1-theory .af-chip i{font-size:11px;}
.rd-scope .p1-theory .af-chip.warning{background:var(--rd-warn-bg);color:var(--rd-warn-tx);}
.rd-scope .p1-theory .af-chip.warning i{color:#C09035;}
.rd-scope .p1-theory .af-chip.risk{background:var(--rd-danger-bg);color:var(--rd-danger);}
.rd-scope .p1-theory .af-chip.risk i{color:var(--rd-danger);}
</style>

{{-- ── KPI band (value-first, delta inline, lowercase label) ──────── --}}
@php
    // Drill-down source data. Show top 5 per cell, "+N more" tail if longer.
    $completedItems = array_slice($report['milestones']['completed'] ?? [], 0, 5);
    $completedMoreCount = max(0, count($report['milestones']['completed'] ?? []) - 5);
    $overdueItems = array_slice($report['needsAttention']['overdueMilestones'] ?? [], 0, 5);
    $overdueMoreCount = max(0, count($report['needsAttention']['overdueMilestones'] ?? []) - 5);

    // Denominator counts for KPI context — a raw "3 overdue" doesn't say
    // "out of how many". Total milestones = completed + inFlight + overdue +
    // upcoming; open = the not-yet-done subset (inFlight + overdue + upcoming).
    $inFlightCount = (int) ($stats['inFlight'] ?? 0);
    $upcomingCount = (int) ($stats['upcoming'] ?? 0);
    $openMsCount = $inFlightCount + $overdueCount + $upcomingCount;
    $totalMsCount = $completedCount + $openMsCount;
    // Drill on "Goals on track" lists the ON-TRACK goals (the number the cell
    // represents). At-risk goals surface in the Needs Attention block.
    $onTrackAll = array_filter(($goalsGroup['goals'] ?? []), fn ($g) => ((array) $g)['status'] === 'status_ontrack' || (is_object($g) && ($g->status ?? '') === 'status_ontrack'));
    $onTrackAll = array_values($onTrackAll);
    $onTrackItems = array_slice($onTrackAll, 0, 5);
    $onTrackMoreCount = max(0, count($onTrackAll) - 5);

    // Hours drill = per-project effort breakdown, sorted desc. Project names
    // come from $report['summaries'] (keyed by projectId with .name field).
    $effortByProj = $report['effort']['byProject'] ?? [];
    arsort($effortByProj);
    $projNames = [];
    foreach (($report['summaries'] ?? []) as $s) {
        $s = (object) $s;
        $projNames[(int) ($s->id ?? 0)] = (string) ($s->name ?? '');
    }
    $hoursItems = [];
    foreach ($effortByProj as $pid => $h) {
        if ($h <= 0) continue;
        $hoursItems[] = ['name' => $projNames[(int) $pid] ?? ('#'.$pid), 'hours' => (float) $h];
    }
    $hoursMoreCount = max(0, count($hoursItems) - 5);
    $hoursItems = array_slice($hoursItems, 0, 5);
    $fmtDate = fn ($v) => is_object($v) ? $v->setToUserTimezone()->format('M j') : ($v ? date('M j', strtotime((string) $v)) : '');
@endphp
<div class="rd-kpi">
    {{-- Completed --}}
    <div class="rd-kcell @if ($completedCount > 0) has-detail @endif" tabindex="{{ $completedCount > 0 ? 0 : -1 }}">
        @if ($completedCount > 0)
            <span class="see-list">{{ __('stakeholder.kpi.see_list') }} <i class="fa fa-chevron-down"></i></span>
        @endif
        <div class="kv">
            {{ $completedCount }}@if ($totalMsCount > 0)<small>/{{ $totalMsCount }}</small>@endif
            @if ($completedDelta > 0)
                <span class="up" title="{{ sprintf(__('stakeholder.kpi.delta_vs_prior'), $completedDelta) }}"><i class="fa fa-arrow-up"></i> +{{ $completedDelta }}</span>
            @elseif ($completedDelta < 0)
                <span class="down" title="{{ sprintf(__('stakeholder.kpi.delta_vs_prior'), $completedDelta) }}"><i class="fa fa-arrow-down"></i> {{ $completedDelta }}</span>
            @endif
        </div>
        <div class="kl">{{ $totalMsCount > 0 ? __('stakeholder.kpi.milestones_completed') : __('stakeholder.kpi.completed_this_period') }}</div>
        @if ($completedCount > 0)
            <div class="kdrill">
                <div class="kd-hd">{{ __('stakeholder.kpi.drill.completed') }}</div>
                <ul>
                    @foreach ($completedItems as $m)
                        @php $m = (object) $m; @endphp
                        <li>
                            <span class="nm" title="{{ $m->headline ?? '' }}">{{ $m->headline ?? __('stakeholder.overview.na_untitled_milestone') }}</span>
                            <span class="mt">{{ $fmtDate($m->completedOn ?? $m->modified ?? null) }}</span>
                        </li>
                    @endforeach
                </ul>
                @if ($completedMoreCount > 0)
                    <div class="kd-more">{{ sprintf(__('stakeholder.kpi.drill.more'), $completedMoreCount) }}</div>
                @endif
            </div>
        @endif
    </div>

    {{-- Goals on track — drill lists the ON-TRACK goals (what the count is) --}}
    <div class="rd-kcell @if (count($onTrackItems) > 0) has-detail @endif" tabindex="{{ count($onTrackItems) > 0 ? 0 : -1 }}">
        @if (count($onTrackItems) > 0)
            <span class="see-list">{{ __('stakeholder.kpi.see_list') }} <i class="fa fa-chevron-down"></i></span>
        @endif
        <div class="kv">{{ $goalsOnTrack }}<small>/{{ $goalsTotal }}</small></div>
        <div class="kl">{{ __('stakeholder.kpi.goals_on_track_lc') }}</div>
        @if (count($onTrackItems) > 0)
            <div class="kdrill">
                <div class="kd-hd">{{ __('stakeholder.kpi.drill.on_track') }}</div>
                <ul>
                    @foreach ($onTrackItems as $g)
                        @php $g = (object) $g; @endphp
                        <li>
                            <span class="nm" title="{{ $g->title ?? '' }}">{{ $g->title ?? $g->description ?? __('stakeholder.goals.untitled') }}</span>
                            <span class="mt">{{ round((float) ($g->goalProgress ?? 0)) }}%</span>
                        </li>
                    @endforeach
                </ul>
                @if ($onTrackMoreCount > 0)
                    <div class="kd-more">{{ sprintf(__('stakeholder.kpi.drill.more'), $onTrackMoreCount) }}</div>
                @endif
            </div>
        @endif
    </div>

    {{-- Overdue milestones --}}
    <div class="rd-kcell @if ($overdueCount > 0) risk @endif @if ($overdueCount > 0) has-detail @endif" tabindex="{{ $overdueCount > 0 ? 0 : -1 }}">
        @if ($overdueCount > 0)
            <span class="see-list">{{ __('stakeholder.kpi.see_list') }} <i class="fa fa-chevron-down"></i></span>
        @endif
        <div class="kv">{{ $overdueCount }}@if ($openMsCount > 0)<small>/{{ $openMsCount }}</small>@endif</div>
        <div class="kl">{{ $openMsCount > 0 ? __('stakeholder.kpi.overdue_of_open') : __('stakeholder.kpi.milestones_overdue') }}</div>
        @if ($overdueCount > 0)
            <div class="kdrill">
                <div class="kd-hd">{{ __('stakeholder.kpi.drill.overdue') }}</div>
                <ul>
                    @foreach ($overdueItems as $m)
                        @php $m = (object) $m; @endphp
                        <li>
                            <span class="nm" title="{{ $m->headline ?? '' }}">{{ $m->headline ?? __('stakeholder.overview.na_untitled_milestone') }}</span>
                            <span class="mt">{{ $m->projectName ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
                @if ($overdueMoreCount > 0)
                    <div class="kd-more">{{ sprintf(__('stakeholder.kpi.drill.more'), $overdueMoreCount) }}</div>
                @endif
            </div>
        @endif
    </div>

    {{-- Hours logged — drill lists per-project breakdown, largest first --}}
    <div class="rd-kcell @if (count($hoursItems) > 0) has-detail @endif" tabindex="{{ count($hoursItems) > 0 ? 0 : -1 }}">
        @if (count($hoursItems) > 0)
            <span class="see-list">{{ __('stakeholder.kpi.see_list') }} <i class="fa fa-chevron-down"></i></span>
        @endif
        <div class="kv">{{ number_format($hoursLogged, $hoursLogged >= 100 ? 0 : 1) }}<small>h</small></div>
        <div class="kl">{{ __('stakeholder.kpi.hours_this_period') }}</div>
        @if (count($hoursItems) > 0)
            <div class="kdrill">
                <div class="kd-hd">{{ __('stakeholder.kpi.drill.hours') }}</div>
                <ul>
                    @foreach ($hoursItems as $h)
                        <li>
                            <span class="nm" title="{{ $h['name'] }}">{{ $h['name'] }}</span>
                            <span class="mt">{{ number_format($h['hours'], $h['hours'] >= 100 ? 0 : 1) }}h</span>
                        </li>
                    @endforeach
                </ul>
                @if ($hoursMoreCount > 0)
                    <div class="kd-more">{{ sprintf(__('stakeholder.kpi.drill.more'), $hoursMoreCount) }}</div>
                @endif
            </div>
        @endif
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
            <h3>{{ $peak->headline ?? '' }}</h3>
            @if ($peakBody !== '')
                <p>{{ mb_strlen($peakBody) > 260 ? mb_substr($peakBody, 0, 257).'…' : $peakBody }}</p>
            @endif
            <div class="hf">
                @if (! empty($peak->projectName))
                    <span class="badge-goal"><i class="fa fa-diagram-project"></i> {{ $peak->projectName }}</span>
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
                                    <span class="na-name">{{ $title }}</span>
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
                                    <span class="na-name">{{ $mname }}</span>
                                    @if ($projName !== '')
                                        <span class="na-meta">· {{ $projName }}</span>
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
                                    <span class="na-name">{{ $p->name ?? '' }}</span>
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

{{-- ── Status narrative — authored, verbatim, per-program ──────────
     The one place on this page where a human says WHY and what they're
     doing about it. Everything else is computed. Renders portfolio note
     first (strategy scope) or the program's own note (program scope),
     then per-program notes newest-first, cap 4 total. Silent when zero
     notes exist — never apologizes. --}}
@php
    // Assemble a normalized note list with a "portfolio" flag so the
    // portfolio note leads and per-program notes follow.
    $narrativeNotes = [];
    // strategyUpdates is keyed by projectId => array of updates (same shape as
    // programUpdates below) — take the newest note of the report subject itself.
    foreach (($strategyUpdates ?? []) as $notes) {
        $notesArr = is_array($notes) ? $notes : [$notes];
        if (empty($notesArr)) {
            continue;
        }
        $newest = $notesArr[0]; // repo returns newest first
        $narrativeNotes[] = [
            'label'     => __('stakeholder.overview.narrative_portfolio'),
            'text'      => trim(strip_tags((string) ($newest->text ?? ''))),
            'date'      => (string) ($newest->date ?? ''),
            'portfolio' => true,
            'sortKey'   => (string) ($newest->date ?? ''),
        ];
    }
    // programUpdates is keyed by projectId => array of updates. Take the
    // newest update per program (already newest-first from the repo).
    $programNameById = [];
    foreach (($programRows ?? []) as $pr) {
        $pid = (int) (is_array($pr) ? ($pr['id'] ?? 0) : ($pr->id ?? 0));
        $nm  = (string) (is_array($pr) ? ($pr['name'] ?? '') : ($pr->name ?? ''));
        if ($pid > 0) $programNameById[$pid] = $nm;
    }
    foreach (($programUpdates ?? []) as $projectId => $notes) {
        $projectId = (int) $projectId;
        $notesArr = is_array($notes) ? $notes : [];
        if (empty($notesArr)) continue;
        $newest = $notesArr[0]; // repo returns newest first
        $narrativeNotes[] = [
            'label'     => $programNameById[$projectId] ?? __('stakeholder.overview.narrative_program_fallback'),
            'text'      => trim(strip_tags((string) ($newest->text ?? ''))),
            'date'      => (string) ($newest->date ?? ''),
            'portfolio' => false,
            'sortKey'   => (string) ($newest->date ?? ''),
        ];
    }
    // Portfolio always first, then per-program by date desc, cap at 4.
    usort($narrativeNotes, function ($a, $b) {
        if ($a['portfolio'] !== $b['portfolio']) return $a['portfolio'] ? -1 : 1;
        return strcmp($b['sortKey'], $a['sortKey']);
    });
    $narrativeNotes = array_values(array_filter($narrativeNotes, fn ($n) => $n['text'] !== ''));
    $narrativeNotes = array_slice($narrativeNotes, 0, 4);

    $fmtNoteDate = function (string $iso) {
        if ($iso === '') return '';
        try { return (new \DateTimeImmutable(substr($iso, 0, 19)))->format('M j'); }
        catch (\Exception $e) { return ''; }
    };
@endphp

@if (count($narrativeNotes) > 0)
    <style>
    .rd-scope .p1-narrative{margin:14px 0;border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);background:var(--rd-panel);padding:14px 18px;}
    .rd-scope .p1-narrative .lb{font-size:10px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--rd-text-3);margin-bottom:10px;display:flex;align-items:center;gap:7px;}
    .rd-scope .p1-narrative .lb i{font-size:11px;}
    .rd-scope .p1-narrative .nn{display:flex;flex-direction:column;gap:10px;}
    .rd-scope .p1-narrative .nr{font-size:13.5px;line-height:1.55;color:var(--rd-text-2);}
    .rd-scope .p1-narrative .nr.portfolio{padding-bottom:10px;border-bottom:1px solid var(--rd-line-soft);}
    .rd-scope .p1-narrative .nr b{color:var(--rd-text-1);font-weight:600;}
    .rd-scope .p1-narrative .nr .dt{font-size:11.5px;color:var(--rd-text-4);margin-left:8px;font-style:italic;}
    </style>
    <div class="p1-narrative">
        <div class="lb"><i class="fa fa-message"></i> {{ __('stakeholder.overview.narrative_label') }}</div>
        <div class="nn">
            @foreach ($narrativeNotes as $n)
                <div class="nr @if ($n['portfolio']) portfolio @endif">
                    <b>{{ $n['label'] }}</b> — {{ $n['text'] }}
                    @if (($d = $fmtNoteDate($n['date'])) !== '')
                        <span class="dt">{{ $d }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- ── Theory of Change narrative (stage-colored) ────────────────── --}}
@if ($hasLM && ! empty($logicModel['narrative']['hasItems']))
    @php $stageTexts = $logicModel['narrative']['stageTexts'] ?? []; @endphp
    <div class="p1-toc collapsed" id="p1TocSection">
        <div class="tl">
            <span class="lbl-inner">
                {{ __('stakeholder.overview.toc_label') }}
                <span class="p1-info" tabindex="0">
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
            </span>
            <button type="button" class="toc-toggle" onclick="p1TocToggle(this)" aria-label="{{ __('stakeholder.overview.toc_toggle') }}">
                <i class="fa fa-chevron-down"></i>
            </button>
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

{{-- ── Theory-health strip — full state, not just warnings ──────── --}}
@if ($hasLM)
    @php
        $badges = $logicModel['healthBadges'] ?? [];
        // Ensure we render all 4 connector slots even when a row is missing;
        // the missing case reads as "no assessment yet" (grey), not "ok".
        $bySlot = [];
        for ($i = 1; $i <= 4; $i++) {
            $b = $badges[$i] ?? null;
            $status = $b && ! empty($b['has_data']) ? (string) ($b['health_status'] ?? '') : '';
            $bySlot[$i] = [
                'status' => $status !== '' ? $status : 'none',
                'label'  => $b['connector_label'] ?? '',
                'assumption' => trim((string) ($b['assumption_text'] ?? '')),
                'evidence'   => trim((string) ($b['evidence_notes'] ?? '')),
            ];
        }
        $counts = ['ok' => 0, 'warning' => 0, 'risk' => 0, 'none' => 0];
        $risky = [];
        foreach ($bySlot as $slot) {
            $counts[$slot['status']]++;
            if (in_array($slot['status'], ['warning', 'risk'], true)) {
                $risky[] = $slot;
            }
        }
        // Sort by severity so a `risk` link leads the detail callout over any
        // `warning` links — critical always takes priority in the board's read.
        usort($risky, fn ($a, $b) => ($b['status'] === 'risk' ? 1 : 0) <=> ($a['status'] === 'risk' ? 1 : 0));
        $solid = $counts['ok'];
        $fragile = $counts['warning'] + $counts['risk'];
        $unassessed = $counts['none'];
        $tone = $fragile > 0 ? 'risk' : ($unassessed > 0 ? 'partial' : 'solid');
    @endphp

    <div class="p1-theory">
        <div class="hd">
            <span class="l">
                {{ __('stakeholder.overview.theory_health_label') }}
                <span class="p1-info" tabindex="0">
                    <span class="ii" aria-hidden="true">i</span>
                    <span class="pop" role="tooltip">
                        {{ __('stakeholder.overview.theory_health_explain') }}
                    </span>
                </span>
            </span>
            <span class="rd @if ($fragile > 0) risk @endif">
                @if ($fragile === 0 && $unassessed === 0)
                    <b>{{ sprintf(__('stakeholder.overview.theory_all_solid'), $solid) }}</b>
                @elseif ($fragile === 0)
                    <b>{{ sprintf(__('stakeholder.overview.theory_solid_and_unassessed'), $solid, $unassessed) }}</b>
                @else
                    <b>{{ sprintf(__('stakeholder.overview.theory_summary_mixed'), $solid, $fragile) }}</b>@if ($unassessed > 0) · {{ sprintf(__('stakeholder.overview.theory_plus_unassessed'), $unassessed) }}@endif
                @endif
            </span>
        </div>

        {{-- Chain: 5 stage chips + 4 colored connectors between. Each connector
             is a line with the health icon as a badge; hover for detail. --}}
        @php
            $stageNames = [
                1 => __('box.logicmodel.inputs'),
                2 => __('box.logicmodel.activities'),
                3 => __('box.logicmodel.outputs'),
                4 => __('box.logicmodel.outcomes'),
                5 => __('box.logicmodel.impact'),
            ];
        @endphp
        <div class="segs">
            @for ($s = 1; $s <= 5; $s++)
                <span class="stage s{{ $s }}">{{ $stageNames[$s] }}</span>
                @if ($s < 5)
                    @php $slot = $bySlot[$s]; @endphp
                    <div class="conn {{ $slot['status'] }}" tabindex="0">
                        <div class="badge">
                            {{-- fa-circle-* family, matching the LM canvas's status pills
                                 (Logicmodelcanvas::STATUS_LABELS uses the same set). --}}
                            <i class="fa
                                @if ($slot['status'] === 'ok') fa-circle-check
                                @elseif ($slot['status'] === 'warning') fa-circle-exclamation
                                @elseif ($slot['status'] === 'risk') fa-triangle-exclamation
                                @else fa-circle-question
                                @endif"></i>
                        </div>
                        @if ($slot['assumption'] !== '' || $slot['evidence'] !== '')
                            <div class="tip">
                                <b>{{ $slot['label'] !== '' ? $slot['label'] : 'Link '.$s }}</b>
                                @if ($slot['assumption'] !== '')
                                    <em>{{ $slot['assumption'] }}</em>
                                @endif
                                @if ($slot['evidence'] !== '')
                                    <div style="margin-top:6px;"><b>{{ __('stakeholder.overview.theory_evidence') }}</b>{{ $slot['evidence'] }}</div>
                                @elseif ($slot['status'] !== 'ok')
                                    <div style="margin-top:6px;opacity:.85;">{{ __('stakeholder.overview.theory_no_evidence_short') }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            @endfor
        </div>

        {{-- Fragile-link detail. Only if there's something to flag. --}}
        @if ($fragile > 0)
            @php $first = $risky[0]; @endphp
            @php $others = array_slice($risky, 1); $otherCount = count($others); @endphp
            <div class="detail @if ($first['status'] === 'risk') crit @endif">
                <i class="fa @if ($first['status'] === 'risk') fa-triangle-exclamation @else fa-circle-exclamation @endif"></i>
                <div>
                    <b>{{ $first['label'] }}</b> {{ $first['status'] === 'risk' ? __('stakeholder.overview.theory_is_critical') : __('stakeholder.overview.theory_needs_work') }}
                    @if ($first['assumption'] !== '')
                        — {{ __('stakeholder.overview.theory_it_rests_on') }} <em>{{ $first['assumption'] }}</em>
                    @endif
                    @if ($first['evidence'] === '')
                        <span class="p1-info detail-info evidence-tag" tabindex="0">
                            <i class="fa fa-circle-exclamation"></i>
                            <span class="tag">{{ __('stakeholder.overview.theory_unproven') }}</span>
                            <span class="pop" role="tooltip">{{ __('stakeholder.overview.theory_unproven_explain') }}</span>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Secondary fragile links as compact "Also fragile" chip row —
                 visible (not hidden in a tooltip) so a board sees the full
                 fragile set at once. --}}
            @if ($otherCount > 0)
                <div class="also-fragile">
                    <span class="lbl">{{ __('stakeholder.overview.theory_also_fragile') }}</span>
                    @foreach ($others as $o)
                        <span class="af-chip {{ $o['status'] }}" title="{{ $o['assumption'] }}">
                            <i class="fa @if ($o['status'] === 'risk') fa-triangle-exclamation @else fa-circle-exclamation @endif"></i>
                            <span class="nm">{{ $o['label'] }}</span>
                        </span>
                    @endforeach
                </div>
            @endif
        @else
            <div class="calm-line">
                <i class="fa fa-check-circle"></i>
                {{ __('stakeholder.overview.theory_all_ok') }}
            </div>
        @endif
    </div>
@endif

<script>
/* Theory of Change collapse — click chevron to toggle the narrative. Persists
   the state in localStorage so it survives page reloads. */
(function () {
    if (window.__p1TocInit) return;
    window.__p1TocInit = true;

    // Default state is COLLAPSED (rendered server-side with .collapsed). Only
    // remove it if the user has previously explicitly expanded it.
    var KEY = 'rd.p1.toc.collapsed';
    var section = document.getElementById('p1TocSection');
    if (section && localStorage.getItem(KEY) === '0') {
        section.classList.remove('collapsed');
    }

    window.p1TocToggle = function (btn) {
        var s = btn.closest('.p1-toc');
        if (!s) return;
        var isNowCollapsed = s.classList.toggle('collapsed');
        try { localStorage.setItem(KEY, isNowCollapsed ? '1' : '0'); } catch (e) {}
    };
})();
</script>

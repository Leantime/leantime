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

<style>
/* Hours / Days unit toggle — top of Page 3. Vanilla-JS controlled. */
.rd-scope .p3-unit-toggle{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-bottom:14px;}
.rd-scope .p3-unit-toggle .p3-unit-lbl{font-size:11px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--rd-text-3);}
.rd-scope .p3-unit-toggle .p3-unit-pill{display:inline-flex;border:1px solid var(--rd-line);border-radius:100px;padding:2px;background:var(--rd-panel);}
.rd-scope .p3-unit-toggle .p3-unit-btn{background:transparent;border:0;padding:5px 14px;border-radius:100px;font-size:12px;font-weight:600;color:var(--rd-text-3);cursor:pointer;letter-spacing:.2px;transition:background .1s ease, color .1s ease;}
.rd-scope .p3-unit-toggle .p3-unit-btn.is-active{background:var(--rd-accent);color:#fff;}
.rd-scope .p3-unit-toggle .p3-unit-btn:hover:not(.is-active){color:var(--rd-text-1);}

.rd-scope .p3-sec{margin-bottom:28px;}
.rd-scope .p3-sec-hd{margin-bottom:14px;}
.rd-scope .p3-sec-hd .l{font-size:11px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:var(--rd-accent);display:block;margin-bottom:3px;}
.rd-scope .p3-sec-hd .s{font-size:13.5px;color:var(--rd-text-3);}

/* Placeholder strip (only when no ResourcesGateway is registered). */
.rd-scope .p3-res-strip{border:1px dashed var(--rd-line);border-radius:var(--rd-r-sm);padding:20px 22px;background:var(--rd-bg);display:flex;align-items:center;gap:16px;}
.rd-scope .p3-res-strip .icn{width:48px;height:48px;border-radius:12px;background:#eef4f3;color:var(--rd-accent);display:flex;align-items:center;justify-content:center;font-size:20px;flex:none;}
.rd-scope .p3-res-strip .cnt{flex:1;min-width:0;}
.rd-scope .p3-res-strip .cnt .h{font-size:15px;font-weight:600;color:var(--rd-text-1);margin-bottom:3px;}
.rd-scope .p3-res-strip .cnt .d{font-size:13.5px;color:var(--rd-text-3);line-height:1.5;}
.rd-scope .p3-res-strip .tag{font-size:10.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-accent);background:rgba(0,71,102,.08);border-radius:10px;padding:4px 10px;flex:none;}

/* Three-card resource summary — larger, roomier, higher contrast. */
.rd-scope .p3-res-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;}
.rd-scope .p3-rcard{border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);padding:20px 22px;background:var(--rd-panel);min-width:0;display:flex;flex-direction:column;}
.rd-scope .p3-rcard .rhead{display:flex;align-items:center;gap:12px;margin-bottom:14px;}
.rd-scope .p3-rcard .rhead .ricn{width:36px;height:36px;border-radius:10px;background:rgba(0,71,102,.08);color:var(--rd-accent);display:grid;place-items:center;font-size:15px;flex:none;}
.rd-scope .p3-rcard .rhead .rlbl{font-size:13.5px;font-weight:600;color:var(--rd-text-2);letter-spacing:.1px;}
.rd-scope .p3-rcard .rv{font-size:34px;font-weight:600;letter-spacing:-.5px;line-height:1.05;color:var(--rd-text-1);}
.rd-scope .p3-rcard .rv small{font-size:15px;color:var(--rd-text-3);font-weight:500;margin-left:4px;letter-spacing:0;}
.rd-scope .p3-rcard .rsub{font-size:13.5px;color:var(--rd-text-2);margin-top:8px;line-height:1.5;}
.rd-scope .p3-rcard .rsub .risk{color:var(--rd-danger);font-weight:600;}
.rd-scope .p3-rcard .rsub .muted{color:var(--rd-text-3);}
.rd-scope .p3-rcard .bar{height:12px;background:#eef1f3;border-radius:6px;margin-top:12px;overflow:hidden;}
.rd-scope .p3-rcard .bar > i{display:block;height:100%;border-radius:6px;}
.rd-scope .p3-rcard .bar.ok > i{background:var(--rd-s1);}
.rd-scope .p3-rcard .bar.spend > i{background:var(--rd-ok);}
.rd-scope .p3-rcard .bar.spend.at-risk > i{background:var(--rd-warn);}
.rd-scope .p3-rcard .bar.spend.over > i{background:var(--rd-danger);}
.rd-scope .p3-rcard .rtail{font-size:13px;color:var(--rd-text-3);margin-top:10px;display:flex;flex-wrap:wrap;gap:6px 12px;}
.rd-scope .p3-rcard .rtail .rp{display:inline-flex;align-items:center;gap:6px;}
.rd-scope .p3-rcard .rtail .dd{width:9px;height:9px;border-radius:50%;flex:none;display:inline-block;}
.rd-scope .p3-rcard .rtail .dd.ok{background:var(--rd-ok);}
.rd-scope .p3-rcard .rtail .dd.warn{background:var(--rd-warn);}
.rd-scope .p3-rcard.empty .rv{color:var(--rd-text-4);}

/* Per-project breakdown — the "where's it going?" answer. Grid table so the
   columns line up regardless of name length. Row hover lifts a bit for scan. */
.rd-scope .p3-bd{border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);overflow:hidden;background:var(--rd-panel);}
.rd-scope .p3-bd-row{display:grid;grid-template-columns:2fr 1.2fr 1.6fr 1.4fr;gap:14px;padding:14px 18px;align-items:center;}
.rd-scope .p3-bd-row + .p3-bd-row{border-top:1px solid var(--rd-line-soft);}
.rd-scope .p3-bd-row.head{background:var(--rd-bg);border-bottom:1px solid var(--rd-line);padding:12px 18px;}
.rd-scope .p3-bd-row.head .p3-bd-cell{font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-text-3);}
.rd-scope .p3-bd-cell{min-width:0;font-size:14px;color:var(--rd-text-1);}
.rd-scope .p3-bd-cell .name{font-weight:600;color:var(--rd-text-1);line-height:1.3;word-wrap:break-word;}
.rd-scope .p3-bd-cell .type{font-size:11.5px;color:var(--rd-text-3);text-transform:uppercase;letter-spacing:.4px;margin-top:2px;}
.rd-scope .p3-bd-cell .num{font-size:18px;font-weight:600;color:var(--rd-text-1);line-height:1.1;}
.rd-scope .p3-bd-cell .num small{font-size:12.5px;color:var(--rd-text-3);font-weight:500;margin-left:3px;}
.rd-scope .p3-bd-cell .sublabel{font-size:12px;color:var(--rd-text-3);margin-top:2px;}
.rd-scope .p3-bd-cell .minibar{height:8px;background:#eef1f3;border-radius:4px;overflow:hidden;margin-top:6px;}
.rd-scope .p3-bd-cell .minibar > i{display:block;height:100%;border-radius:4px;background:var(--rd-s1);}
.rd-scope .p3-bd-cell .minibar.spend > i{background:var(--rd-ok);}
.rd-scope .p3-bd-cell .minibar.spend.at-risk > i{background:var(--rd-warn);}
.rd-scope .p3-bd-cell .minibar.spend.over > i{background:var(--rd-danger);}
.rd-scope .p3-bd-cell .zero{color:var(--rd-text-4);font-style:italic;font-size:13px;}
.rd-scope .p3-bd-empty{padding:24px;color:var(--rd-text-3);font-style:italic;text-align:center;font-size:14px;}

/* Program-rollup rendering (strategy scope): each program is a <details> with
   summary as the program row and child project rows revealed on expand.
   Uses native <details>/<summary> for a11y — keyboard-accessible, no JS. */
.rd-scope .p3-bd-program{display:block;}
.rd-scope .p3-bd-program + .p3-bd-program{border-top:1px solid var(--rd-line);}
.rd-scope .p3-bd-program summary{list-style:none;cursor:pointer;display:grid;grid-template-columns:2fr 1.2fr 1.6fr 1.4fr;gap:14px;padding:14px 18px;align-items:center;transition:background .1s ease;}
.rd-scope .p3-bd-program summary::-webkit-details-marker{display:none;}
.rd-scope .p3-bd-program summary:hover{background:var(--rd-bg);}
.rd-scope .p3-bd-program summary .name-cell{display:flex;align-items:flex-start;gap:10px;}
.rd-scope .p3-bd-program summary .expand-chevron{width:20px;padding-top:2px;color:var(--rd-text-3);font-size:11px;transition:transform .15s ease;flex:none;}
.rd-scope .p3-bd-program[open] > summary .expand-chevron{transform:rotate(90deg);}
.rd-scope .p3-bd-program[open] > summary{background:var(--rd-bg);border-bottom:1px solid var(--rd-line-soft);}
.rd-scope .p3-bd-program summary .name{font-weight:600;color:var(--rd-text-1);line-height:1.3;}
.rd-scope .p3-bd-program summary .type{font-size:11.5px;color:var(--rd-text-3);text-transform:uppercase;letter-spacing:.4px;margin-top:2px;}
.rd-scope .p3-bd-row.child-row{background:var(--rd-bg);padding:12px 18px 12px 32px;}
.rd-scope .p3-bd-row.child-row + .child-row{border-top:1px solid var(--rd-line-soft);}
.rd-scope .p3-bd-row.child-row .name-cell{display:flex;align-items:flex-start;gap:10px;}
.rd-scope .p3-bd-row.child-row .child-indent{color:var(--rd-text-4);font-size:14px;flex:none;padding-top:1px;}
.rd-scope .p3-bd-row.child-row .name{font-weight:500;font-size:13.5px;color:var(--rd-text-2);}
.rd-scope .p3-bd-row.child-row .num{font-size:15px;}

/* Capacity vs. demand — per-project deep read. Each card compares three
   independent estimates (budgeted hours, effort points × conversion, people
   × weeks × allocation) and shows the sensitivity between them so the board
   can see *how much* off, not just *that* it's off. Rebalance levers land at
   the bottom of each card so the discussion has options, not just alarms. */
.rd-scope .p3-cap-stack{display:flex;flex-direction:column;gap:12px;}
.rd-scope .p3-cap{border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);background:var(--rd-panel);overflow:hidden;}
.rd-scope .p3-cap.critical{border-color:rgba(220,60,60,.35);}
.rd-scope .p3-cap.tight{border-color:rgba(245,166,35,.4);}
.rd-scope .p3-cap.balanced{border-color:var(--rd-line);}
.rd-scope .p3-cap.buffer{border-color:rgba(46,164,79,.3);}

.rd-scope .p3-cap-hd{display:flex;align-items:center;gap:14px;padding:16px 20px 12px;border-bottom:1px solid var(--rd-line-soft);}
.rd-scope .p3-cap-hd .verdict{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:100px;font-size:11.5px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;flex:none;}
.rd-scope .p3-cap-hd .verdict.critical{background:rgba(220,60,60,.10);color:var(--rd-danger);}
.rd-scope .p3-cap-hd .verdict.tight{background:rgba(245,166,35,.14);color:var(--rd-warn-tx);}
.rd-scope .p3-cap-hd .verdict.balanced{background:rgba(0,71,102,.09);color:var(--rd-accent);}
.rd-scope .p3-cap-hd .verdict.buffer{background:rgba(46,164,79,.11);color:var(--rd-ok);}
.rd-scope .p3-cap-hd .verdict.no_work,
.rd-scope .p3-cap-hd .verdict.no_capacity{background:rgba(140,140,140,.10);color:var(--rd-text-3);}
.rd-scope .p3-cap-hd .name{font-size:16px;font-weight:600;color:var(--rd-text-1);flex:1;min-width:0;line-height:1.3;}
.rd-scope .p3-cap-hd .headline-num{font-size:15px;font-weight:600;flex:none;}
.rd-scope .p3-cap-hd .headline-num.critical{color:var(--rd-danger);}
.rd-scope .p3-cap-hd .headline-num.tight{color:var(--rd-warn-tx);}
.rd-scope .p3-cap-hd .headline-num.buffer{color:var(--rd-ok);}
.rd-scope .p3-cap-hd .headline-num.balanced{color:var(--rd-text-2);}

/* Trust confidence indicator — small icon-only pill in the header. Hover for
   the full explanation. Green = both estimates agree; amber = fallback/mixed.
   Kept subtle so it doesn't compete with the verdict pill. */
.rd-scope .p3-cap-hd .trust{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:100px;font-size:10.5px;font-weight:600;letter-spacing:.3px;cursor:help;flex:none;}
.rd-scope .p3-cap-hd .trust.good{background:rgba(46,164,79,.10);color:var(--rd-ok);}
.rd-scope .p3-cap-hd .trust.warn{background:rgba(245,166,35,.14);color:var(--rd-warn-tx);}
.rd-scope .p3-cap-hd .trust i{font-size:10px;}

.rd-scope .p3-cap-body{padding:14px 20px 18px;}
.rd-scope .p3-cap-row{display:grid;grid-template-columns:90px 1fr;gap:14px;padding:10px 0;align-items:baseline;}
.rd-scope .p3-cap-row + .p3-cap-row{border-top:1px dashed var(--rd-line-soft);}
.rd-scope .p3-cap-row .lbl{font-size:10.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-text-3);padding-top:2px;}
.rd-scope .p3-cap-row .val{font-size:14px;color:var(--rd-text-1);line-height:1.55;}
.rd-scope .p3-cap-row .val .primary{font-weight:600;color:var(--rd-text-1);}
.rd-scope .p3-cap-row .val .muted{color:var(--rd-text-3);}
.rd-scope .p3-cap-row .val .divider{color:var(--rd-text-4);margin:0 6px;}
.rd-scope .p3-cap-row .val .note{display:block;font-size:12.5px;color:var(--rd-text-3);margin-top:3px;font-style:italic;}
.rd-scope .p3-cap-row .val .note.warn{color:var(--rd-warn-tx);font-style:normal;font-weight:500;}
.rd-scope .p3-cap-row .val .note.good{color:var(--rd-ok);font-style:normal;font-weight:500;}

.rd-scope .p3-cap-bar{margin-top:8px;}
.rd-scope .p3-cap-bar .track{position:relative;height:14px;background:#eef1f3;border-radius:7px;overflow:hidden;}
/* Supply segment — 0 → available. Always green ("we've got this much"). */
.rd-scope .p3-cap-bar .track .supply{position:absolute;top:0;left:0;bottom:0;background:var(--rd-ok);opacity:.9;}
/* Deficit segment — available → needed. Colored by verdict so the shortfall
   reads at the same visual weight as the verdict pill above. */
.rd-scope .p3-cap-bar .track .deficit{position:absolute;top:0;bottom:0;background:var(--rd-danger);opacity:.85;}
.rd-scope .p3-cap-bar .track.tight .deficit{background:var(--rd-warn);}
.rd-scope .p3-cap-bar .track.critical .deficit{background:var(--rd-danger);}
.rd-scope .p3-cap-bar .track .marker{position:absolute;top:-3px;bottom:-3px;width:2px;background:var(--rd-text-1);z-index:2;}
.rd-scope .p3-cap-bar .track .marker::after{content:'';position:absolute;top:-2px;left:-3px;width:8px;height:8px;background:var(--rd-text-1);border-radius:50%;}
.rd-scope .p3-cap-bar .legend{display:flex;justify-content:space-between;font-size:11.5px;color:var(--rd-text-3);margin-top:6px;}

.rd-scope .p3-cap-rebalance{margin-top:10px;padding-top:12px;border-top:1px solid var(--rd-line-soft);}
.rd-scope .p3-cap-rebalance .hd{font-size:10.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-text-3);margin-bottom:8px;}
.rd-scope .p3-cap-rebalance .opts{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;}
.rd-scope .p3-cap-rebalance .opt{border:1px solid var(--rd-line);border-radius:var(--rd-r-xs);padding:10px 12px;background:var(--rd-bg);}
.rd-scope .p3-cap-rebalance .opt .icn{color:var(--rd-accent);font-size:14px;margin-bottom:6px;}
.rd-scope .p3-cap-rebalance .opt .lever{font-size:14.5px;font-weight:600;color:var(--rd-text-1);line-height:1.3;}
.rd-scope .p3-cap-rebalance .opt .lever b{font-size:17px;color:var(--rd-danger);}
.rd-scope .p3-cap-rebalance .opt .detail{font-size:12px;color:var(--rd-text-3);margin-top:4px;line-height:1.5;}

/* Compact one-liner for balanced/buffer projects — no full card, just a row */
.rd-scope .p3-cap-compact{display:flex;align-items:center;gap:14px;padding:12px 18px;border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);background:var(--rd-panel);}
.rd-scope .p3-cap-compact .verdict{padding:4px 10px;border-radius:100px;font-size:10.5px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;flex:none;}
.rd-scope .p3-cap-compact .verdict.balanced{background:rgba(0,71,102,.09);color:var(--rd-accent);}
.rd-scope .p3-cap-compact .verdict.buffer{background:rgba(46,164,79,.11);color:var(--rd-ok);}
.rd-scope .p3-cap-compact .verdict.no_work{background:rgba(140,140,140,.10);color:var(--rd-text-3);}
.rd-scope .p3-cap-compact .name{font-size:14.5px;font-weight:600;color:var(--rd-text-1);flex:1;min-width:0;}
.rd-scope .p3-cap-compact .summary{font-size:13px;color:var(--rd-text-3);flex:none;}

/* Capacity program-rollup: program-level card wrapped in <details> so click
   expands to reveal child project cards inline. Chevron rotates on open — the
   chevron lives in the .subname of the card header (not absolute-positioned)
   so it doesn't collide with the trust pill or headline gap number. */
.rd-scope .p3-cap-program{display:block;}
.rd-scope .p3-cap-program summary{list-style:none;cursor:pointer;}
.rd-scope .p3-cap-program summary::-webkit-details-marker{display:none;}
.rd-scope .p3-cap-hd .name .subname{font-size:12px;color:var(--rd-text-3);font-weight:500;margin-top:3px;text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:6px;}
.rd-scope .p3-cap-hd .name .subname .expand-hint{font-size:10px;color:var(--rd-text-3);transition:transform .15s ease;}
.rd-scope .p3-cap-program[open] > summary .p3-cap-hd .name .subname .expand-hint{transform:rotate(90deg);}
.rd-scope .p3-cap-hd .headline-num .headline-days{font-size:12.5px;color:var(--rd-text-3);font-weight:500;margin-left:6px;letter-spacing:0;}

/* Bar marker: always-visible label above the tick so board readers don't have
   to hover to understand what the marker means. */
.rd-scope .p3-cap-bar .track{overflow:visible;position:relative;}
.rd-scope .p3-cap-bar .track .marker .marker-label{position:absolute;top:-18px;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:10.5px;font-weight:600;color:var(--rd-text-2);background:var(--rd-panel);padding:1px 6px;border-radius:3px;letter-spacing:.3px;text-transform:uppercase;}
.rd-scope .p3-cap-bar{margin-top:22px;}

/* Info icon on "pts" — subtle, clickable-ish. */
.rd-scope .p3-cap-row .val .pts-info{cursor:help;border-bottom:1px dotted var(--rd-text-4);}
.rd-scope .p3-cap-row .val .pts-info i{font-size:11px;margin-left:2px;color:var(--rd-text-3);}
.rd-scope .p3-cap-children{padding:12px 20px 20px;background:var(--rd-bg);border-left:3px solid var(--rd-line);margin-left:20px;margin-right:20px;margin-bottom:14px;border-radius:0 0 var(--rd-r-xs) var(--rd-r-xs);}
.rd-scope .p3-cap-children-hd{font-size:10.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-text-3);margin-bottom:10px;padding-top:4px;}
.rd-scope .p3-cap-children .p3-cap,
.rd-scope .p3-cap-children .p3-cap-compact{margin-bottom:8px;}
.rd-scope .p3-cap-children .p3-cap:last-child,
.rd-scope .p3-cap-children .p3-cap-compact:last-child{margin-bottom:0;}

/* Dependencies — dedicated section rendering each external commitment with
   owner + decision date + notes. Cards laid out in a 2-column grid on wide
   viewports, single column on narrow. Tentative deps get a warmer border
   for scanability; the top "urgent" callout surfaces the soonest decision. */
.rd-scope .p3-dep-urgent{display:flex;gap:14px;align-items:center;padding:14px 18px;border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);margin-bottom:14px;background:var(--rd-panel);}
.rd-scope .p3-dep-urgent.soon{background:var(--rd-warn-bg);border-color:rgba(245,166,35,.35);}
.rd-scope .p3-dep-urgent .ic{width:36px;height:36px;border-radius:10px;background:rgba(245,166,35,.15);color:var(--rd-warn-tx);display:grid;place-items:center;font-size:15px;flex:none;}
.rd-scope .p3-dep-urgent .body{flex:1;min-width:0;}
.rd-scope .p3-dep-urgent .hd{font-size:14.5px;color:var(--rd-text-1);line-height:1.4;}
.rd-scope .p3-dep-urgent .hd strong{color:var(--rd-text-1);font-weight:700;}
.rd-scope .p3-dep-urgent .meta{font-size:12.5px;color:var(--rd-text-3);margin-top:3px;}
.rd-scope .p3-dep-urgent .meta strong{color:var(--rd-text-2);font-weight:600;}

.rd-scope .p3-dep-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
.rd-scope .p3-dep{border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);background:var(--rd-panel);padding:16px 18px;display:flex;flex-direction:column;gap:10px;}
.rd-scope .p3-dep.tentative{border-color:rgba(245,166,35,.28);}
.rd-scope .p3-dep.confirmed{border-color:rgba(46,164,79,.22);}
.rd-scope .p3-dep-hd{display:flex;align-items:center;justify-content:space-between;gap:8px;}
.rd-scope .p3-dep-hd .status{display:inline-flex;align-items:center;gap:6px;font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;padding:4px 9px;border-radius:100px;flex:none;}
.rd-scope .p3-dep.tentative .p3-dep-hd .status{background:rgba(245,166,35,.14);color:var(--rd-warn-tx);}
.rd-scope .p3-dep.confirmed .p3-dep-hd .status{background:rgba(46,164,79,.11);color:var(--rd-ok);}
.rd-scope .p3-dep-hd .type-badge{font-size:10px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;padding:3px 8px;border-radius:4px;background:var(--rd-bg);color:var(--rd-text-3);flex:none;}
.rd-scope .p3-dep-name{font-size:15.5px;font-weight:600;color:var(--rd-text-1);line-height:1.35;}
.rd-scope .p3-dep-meta{display:flex;flex-direction:column;gap:5px;font-size:13px;}
.rd-scope .p3-dep-meta .row{display:flex;gap:8px;align-items:baseline;}
.rd-scope .p3-dep-meta .row .lbl{font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--rd-text-3);min-width:82px;flex:none;padding-top:2px;}
.rd-scope .p3-dep-meta .row .val{color:var(--rd-text-1);}
.rd-scope .p3-dep-meta .row .val .soft{color:var(--rd-text-3);font-size:12px;}
.rd-scope .p3-dep-meta .row.urgent .val{color:var(--rd-warn-tx);font-weight:600;}
.rd-scope .p3-dep-meta .row.urgent .val .soft{color:var(--rd-warn-tx);font-weight:500;}
.rd-scope .p3-dep-notes{font-size:13px;color:var(--rd-text-2);line-height:1.55;padding-top:2px;border-top:1px dashed var(--rd-line-soft);padding-top:10px;margin-top:2px;}
.rd-scope .p3-dep-foot{font-size:11.5px;color:var(--rd-text-4);font-style:italic;margin-top:auto;}

@media (max-width:900px){
    .rd-scope .p3-dep-grid{grid-template-columns:1fr;}
}

/* Resource gaps & risks — remaining observations that aren't project-level
   capacity math (over-allocated people, idle capacity, tentative dependencies). */
.rd-scope .p3-gaps{display:flex;flex-direction:column;gap:10px;}
.rd-scope .p3-gap{border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);background:var(--rd-panel);padding:15px 18px;display:flex;align-items:flex-start;gap:14px;}
.rd-scope .p3-gap .sev{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;font-size:15px;flex:none;}
.rd-scope .p3-gap.red .sev{background:rgba(220,60,60,.10);color:var(--rd-danger);}
.rd-scope .p3-gap.yellow .sev{background:rgba(245,166,35,.12);color:var(--rd-warn-tx);}
.rd-scope .p3-gap.blue .sev{background:rgba(0,71,102,.10);color:var(--rd-accent);}
.rd-scope .p3-gap .body{flex:1;min-width:0;}
.rd-scope .p3-gap .body .headline{font-size:14.5px;font-weight:600;color:var(--rd-text-1);line-height:1.4;}
.rd-scope .p3-gap .body .headline b{color:var(--rd-danger);font-weight:700;}
.rd-scope .p3-gap.yellow .body .headline b{color:var(--rd-warn-tx);}
.rd-scope .p3-gap .body .detail{font-size:13px;color:var(--rd-text-3);margin-top:4px;line-height:1.5;}
.rd-scope .p3-gap .body .detail em{font-style:normal;color:var(--rd-text-2);}
.rd-scope .p3-gap.ok{background:#f2faf6;border-color:#cfe8d9;}
.rd-scope .p3-gap.ok .sev{background:rgba(46,164,79,.12);color:var(--rd-ok);}
.rd-scope .p3-gap.ok .body .headline{color:#186c39;}
.rd-scope .p3-gap.ok .body .detail{color:#4a7a5b;}

/* Narrow screens — cards stack, breakdown table becomes single column stack. */
@media (max-width:900px){
    .rd-scope .p3-res-grid{grid-template-columns:1fr;}
    .rd-scope .p3-bd-row{grid-template-columns:1fr;gap:8px;padding:14px 16px;}
    .rd-scope .p3-bd-row.head{display:none;}
    .rd-scope .p3-bd-cell::before{content:attr(data-label);display:block;font-size:10.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-text-3);margin-bottom:3px;}
    .rd-scope .p3-bd-cell.name-cell::before{display:none;}
}
</style>

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
                    </div>
                    <div class="bar ok"><i style="width:{{ min(100, (int) $capacityPct) }}%;"></i></div>
                    <div class="rtail">
                        <span class="rp">{{ round($resourceSummary->totalAllocated) }} / {{ round($resourceSummary->totalCapacity) }}h {{ __('stakeholder.rc.res_hours_weekly') }}</span>
                    </div>
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
@if ($resourceSummary !== null && count($resourceSummary->projectIds) > 1)
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

{{-- ── Coverage — does each focus area have anything behind it? ─────
     The strategic question that Capacity structurally can't answer: a focus
     area with zero work has no hours to count, so it never appears in
     Capacity math. Coverage names it explicitly — goals tracked × people/
     budget resourced — so a strategy quietly carrying a focus area nobody
     staffed doesn't stay invisible.

     Never asserts a gap it can't distinguish from unauthored resources —
     the "or not yet authored — load from Logic Model Inputs" line preserves
     the two-meanings ambiguity. Same discipline as "unproven" on Page 2. --}}
@if ($hasLM && $resourceSummary !== null)
    @php
        // Focus areas = evaluated Output + Outcome items on the LM canvas.
        $lmStages = $logicModel['coverageMatrix']['stages'] ?? [];
        $lmProjectLinks = $logicModel['projectLinks'] ?? [];
        $lmLinkedGoals = $logicModel['linkedGoals'] ?? [];
        $focusItems = array_merge(
            $lmStages['outputs']['items'] ?? [],
            $lmStages['outcomes']['items'] ?? []
        );

        // Per-focus-area rollup. Resources are derived from the Gateway's
        // ResourceSummary filtered by the projectIds this focus area links
        // to (through its goal links). The report never recomputes — it just
        // scopes the same aggregates the Gateway already exposes.
        $coverageRows = [];
        foreach ($focusItems as $item) {
            $arr = (array) $item;
            $itemId = (int) ($arr['id'] ?? 0);
            $links = $lmProjectLinks[$itemId] ?? [];

            // Goals + owning projects.
            $goalIds = [];
            $projectIdsForItem = [];
            $anyGoalAtRisk = false;
            foreach ($links as $lk) {
                if (($lk['linked_entity_type'] ?? '') !== 'goal') continue;
                $gid = (int) ($lk['linked_entity_id'] ?? 0);
                $goalIds[$gid] = true;
                if (isset($lmLinkedGoals[$gid])) {
                    $pid = $lmLinkedGoals[$gid]['projectId'] ?? null;
                    if ($pid !== null && ($lmLinkedGoals[$gid]['projectType'] ?? '') !== 'strategy') {
                        $projectIdsForItem[$pid] = true;
                    }
                    if (($lmLinkedGoals[$gid]['status'] ?? '') === 'status_atrisk') $anyGoalAtRisk = true;
                }
            }
            $goalCount = count($goalIds);
            $projectIdsForItem = array_keys($projectIdsForItem);

            // Resources scoped to this focus area's projects.
            $peopleSet = [];
            $budgetedTotal = 0.0;
            foreach ($resourceSummary->people as $person) {
                foreach ($person->allocations as $pid => $hrs) {
                    if ((float) $hrs > 0 && in_array((int) $pid, $projectIdsForItem, true)) {
                        $peopleSet[$person->itemId] = 1;
                    }
                }
            }
            foreach ($resourceSummary->budget as $bl) {
                if (in_array((int) $bl->projectId, $projectIdsForItem, true)) {
                    $budgetedTotal += (float) $bl->budgeted;
                }
            }
            $peopleCount = count($peopleSet);

            $hasGoals = $goalCount > 0;
            $hasResources = $peopleCount > 0 || $budgetedTotal > 0;

            // Skip focus areas with no goals AND no resources — they're not
            // an evaluated unit. A funder can't act on a row that carries no
            // signal in either dimension.
            if (! $hasGoals && ! $hasResources) continue;

            // Verdict — two dimensions, don't collapse.
            if ($hasGoals && $hasResources) {
                $verdict = $anyGoalAtRisk ? 'thin' : 'covered';
            } elseif ($hasGoals && ! $hasResources) {
                $verdict = 'gap';
            } else {
                $verdict = 'thin';   // resources without goals — work happening off-tracker
            }

            $coverageRows[] = [
                'id'           => $itemId,
                'name'         => trim((string) ($arr['description'] ?? '')),
                'goalCount'    => $goalCount,
                'anyGoalRisk'  => $anyGoalAtRisk,
                'peopleCount'  => $peopleCount,
                'budgetTotal'  => $budgetedTotal,
                'verdict'      => $verdict,
                'iconClass'    => (($arr['box'] ?? '') === 'lm_outcomes') ? 'fa-chart-line' : 'fa-boxes-stacked',
            ];
        }

        // Sort: gap first (the fund-it-or-drop-it row is the point), then thin, then covered.
        $verdictRank = ['gap' => 0, 'thin' => 1, 'covered' => 2];
        usort($coverageRows, fn ($a, $b) => ($verdictRank[$a['verdict']] ?? 9) <=> ($verdictRank[$b['verdict']] ?? 9));

        $moneyFmtCov = function ($n) {
            $n = (float) $n;
            if ($n >= 1000000) return '$'.number_format($n / 1000000, 1).'M';
            if ($n >= 1000) return '$'.number_format($n / 1000, $n >= 10000 ? 0 : 1).'K';
            return '$'.number_format($n, 0);
        };
    @endphp

    @if (count($coverageRows) > 0)
        <style>
        .rd-scope .p3-coverage{border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);background:var(--rd-panel);overflow:hidden;}
        .rd-scope .p3-coverage .cvhd{padding:12px 18px;border-bottom:1px solid var(--rd-line);display:flex;justify-content:space-between;align-items:center;gap:12px;background:var(--rd-bg);}
        .rd-scope .p3-coverage .cvhd .legend{font-size:11px;color:var(--rd-text-3);display:flex;align-items:center;gap:12px;letter-spacing:.2px;}
        .rd-scope .p3-coverage .cvhd .legend .l{display:inline-flex;align-items:center;gap:5px;}
        .rd-scope .p3-coverage .cvhd .legend .dot{width:8px;height:8px;border-radius:50%;display:inline-block;}
        .rd-scope .p3-coverage .cvhd .legend .dot.covered{background:var(--rd-ok);}
        .rd-scope .p3-coverage .cvhd .legend .dot.thin{background:#3F72B0;}
        .rd-scope .p3-coverage .cvhd .legend .dot.gap{background:transparent;border:1.5px solid var(--rd-danger);}
        .rd-scope .p3-coverage .cvrow{display:grid;grid-template-columns:minmax(0,1.8fr) 1fr 1.3fr 100px;gap:14px;padding:14px 18px;align-items:center;}
        .rd-scope .p3-coverage .cvrow + .cvrow{border-top:1px solid var(--rd-line-soft);}
        .rd-scope .p3-coverage .cvrow.head{background:var(--rd-bg);padding:9px 18px;}
        .rd-scope .p3-coverage .cvrow.head .cvcell{font-size:10.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--rd-text-3);}
        .rd-scope .p3-coverage .cvcell{font-size:13px;color:var(--rd-text-2);min-width:0;}
        .rd-scope .p3-coverage .cvcell.name{display:flex;align-items:center;gap:10px;}
        .rd-scope .p3-coverage .cvcell.name i{font-size:12px;color:var(--rd-text-3);}
        .rd-scope .p3-coverage .cvcell.name .fa{width:14px;text-align:center;}
        .rd-scope .p3-coverage .cvcell.name b{color:var(--rd-text-1);font-weight:600;font-size:13.5px;line-height:1.35;}
        .rd-scope .p3-coverage .cvcell .warn{color:var(--rd-warn-tx);font-weight:600;font-size:11.5px;margin-left:6px;}
        .rd-scope .p3-coverage .cvcell .absent{font-style:italic;color:var(--rd-text-3);font-size:12.5px;}
        .rd-scope .p3-coverage .verdict{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;justify-self:end;white-space:nowrap;letter-spacing:.2px;text-transform:capitalize;}
        .rd-scope .p3-coverage .verdict.covered{color:var(--rd-ok);}
        .rd-scope .p3-coverage .verdict.thin{color:#3F72B0;}
        .rd-scope .p3-coverage .verdict.gap{color:var(--rd-danger);}
        .rd-scope .p3-coverage .verdict .dot{width:8px;height:8px;border-radius:50%;display:inline-block;}
        .rd-scope .p3-coverage .verdict.covered .dot{background:var(--rd-ok);}
        .rd-scope .p3-coverage .verdict.thin .dot{background:#3F72B0;}
        .rd-scope .p3-coverage .verdict.gap .dot{background:transparent;border:1.5px solid var(--rd-danger);}
        .rd-scope .p3-coverage .cvambig{padding:10px 18px 12px 18px;background:var(--rd-warn-bg);color:var(--rd-warn-tx);font-size:12px;line-height:1.5;font-style:italic;border-top:1px solid var(--rd-line-soft);}
        .rd-scope .p3-coverage .cvambig i{margin-right:6px;font-size:11px;color:#b8860b;}
        </style>

        <div class="p3-sec">
            <div class="p3-sec-hd">
                <span class="l">{{ __('stakeholder.rc.coverage.label') }}</span>
                <span class="s">{{ __('stakeholder.rc.coverage.sub') }}</span>
            </div>

            <div class="p3-coverage">
                <div class="cvhd">
                    <div style="font-size:11.5px;color:var(--rd-text-3);letter-spacing:.2px;">{{ __('stakeholder.rc.coverage.header_hint') }}</div>
                    <div class="legend">
                        <span class="l"><span class="dot covered"></span> {{ __('stakeholder.rc.coverage.v_covered') }}</span>
                        <span class="l"><span class="dot thin"></span> {{ __('stakeholder.rc.coverage.v_thin') }}</span>
                        <span class="l"><span class="dot gap"></span> {{ __('stakeholder.rc.coverage.v_gap') }}</span>
                    </div>
                </div>

                <div class="cvrow head">
                    <div class="cvcell">{{ __('stakeholder.rc.coverage.col_focus') }}</div>
                    <div class="cvcell">{{ __('stakeholder.rc.coverage.col_tracked') }}</div>
                    <div class="cvcell">{{ __('stakeholder.rc.coverage.col_resourced') }}</div>
                    <div class="cvcell">{{ __('stakeholder.rc.coverage.col_verdict') }}</div>
                </div>

                @foreach ($coverageRows as $row)
                    <div class="cvrow">
                        <div class="cvcell name">
                            <i class="fa {{ $row['iconClass'] }}"></i>
                            <b>{{ $row['name'] }}</b>
                        </div>
                        <div class="cvcell">
                            @if ($row['goalCount'] === 0)
                                <span class="absent">{{ __('stakeholder.rc.coverage.no_goals') }}</span>
                            @else
                                {{ $row['goalCount'] === 1 ? __('stakeholder.rc.coverage.n_goal_one') : sprintf(__('stakeholder.rc.coverage.n_goals'), $row['goalCount']) }}
                                @if ($row['anyGoalRisk'])
                                    <span class="warn">· {{ __('stakeholder.rc.coverage.goal_at_risk') }}</span>
                                @endif
                            @endif
                        </div>
                        <div class="cvcell">
                            @if ($row['peopleCount'] === 0 && $row['budgetTotal'] === 0.0)
                                <span class="absent">{{ __('stakeholder.rc.coverage.no_resources') }}</span>
                            @else
                                @if ($row['peopleCount'] > 0)
                                    {{ sprintf(__('stakeholder.rc.coverage.n_people'), $row['peopleCount']) }}
                                @endif
                                @if ($row['budgetTotal'] > 0)
                                    {{ $row['peopleCount'] > 0 ? ' · ' : '' }}{{ $moneyFmtCov($row['budgetTotal']) }}
                                @endif
                            @endif
                        </div>
                        <div class="cvcell">
                            <span class="verdict {{ $row['verdict'] }}">
                                <span class="dot"></span>
                                {{ $row['verdict'] === 'covered' ? __('stakeholder.rc.coverage.v_covered')
                                : ($row['verdict'] === 'thin' ? __('stakeholder.rc.coverage.v_thin')
                                : __('stakeholder.rc.coverage.v_gap')) }}
                            </span>
                        </div>
                    </div>
                @endforeach

                @if (in_array('gap', array_column($coverageRows, 'verdict'), true))
                    {{-- The ambiguity rule (preserved from the original design):
                         a gap cell has two possible meanings; never assert the
                         wrong one. Points at where the authoring happens. --}}
                    <div class="cvambig">
                        <i class="fa fa-circle-info"></i>
                        {{ __('stakeholder.rc.coverage.ambiguity') }}
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif

{{-- ── Capacity vs. demand — per project (with sensitivity + rebalance) ──
     The board-level "can this plan actually be delivered by these people in
     this time?" answer. For each project, joins three independent estimates:
     budgeted hours (planHours), effort (storypoints × conversion), and
     capacity (people × weeks × allocation). Shows the sensitivity, not just a
     flag. When the plan doesn't fit, lists the three rebalance levers with
     specific numbers. --}}
@if (! empty($capacityAnalysis))
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
                if ($days < 30)  return sprintf(__('stakeholder.rc.dep.weeks_ago'), (int) round($days / 7));
                return sprintf(__('stakeholder.rc.dep.months_ago'), (int) round($days / 30));
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
     Observations that aren't per-project capacity math: over-allocated PEOPLE
     (across all projects), idle capacity, budget-vs-authorized imbalances,
     tentative dependencies. Capacity vs demand above handles project-level
     scheduled hours; this handles everything else. --}}
@if ($resourceSummary !== null)
    @php
        $gaps = [];

        // Capacity gaps — individual over-allocation. A person's planned weekly
        // hours across every project they touch exceeding their stated capacity.
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
                        <div class="detail">{{ __('stakeholder.rc.gap.none_detail') }}</div>
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

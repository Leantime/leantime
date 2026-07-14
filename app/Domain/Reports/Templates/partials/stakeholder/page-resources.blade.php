{{--
    Stakeholder Report — Page 3 (Resources & Coverage)

    §5 page 3: Resource summary + coverage matrix. The resource summary reads
    ResourcesGateway — NOT WIRED YET (see §10.d); rendered as a "coming soon"
    strip. The coverage matrix reads $logicModel.coverageMatrix which IS wired
    (boolean linkage between LM items and programs/projects) — resource-backed
    verdicts are the pending upgrade.

    Vars in:
      $logicModel  null | {coverageMatrix: {stages, columns, cells, unalignedColumns}}
      $hasLM       bool
--}}

<style>
.rd-scope .p3-sec{margin-bottom:22px;}
.rd-scope .p3-sec-hd{margin-bottom:10px;}
.rd-scope .p3-sec-hd .l{font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-accent);display:block;margin-bottom:2px;}
.rd-scope .p3-sec-hd .s{font-size:12px;color:var(--rd-text-3);}

.rd-scope .p3-res-strip{border:1px dashed var(--rd-line);border-radius:var(--rd-r-sm);padding:16px 18px;background:var(--rd-bg);display:flex;align-items:center;gap:14px;}
.rd-scope .p3-res-strip .icn{width:38px;height:38px;border-radius:10px;background:#eef4f3;color:var(--rd-accent);display:flex;align-items:center;justify-content:center;font-size:16px;flex:none;}
.rd-scope .p3-res-strip .cnt{flex:1;min-width:0;}
.rd-scope .p3-res-strip .cnt .h{font-size:13.5px;font-weight:600;color:var(--rd-text-1);margin-bottom:2px;}
.rd-scope .p3-res-strip .cnt .d{font-size:12px;color:var(--rd-text-3);line-height:1.5;}
.rd-scope .p3-res-strip .tag{font-size:9.5px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--rd-accent);background:rgba(0,71,102,.08);border-radius:10px;padding:3px 8px;flex:none;}

/* Coverage matrix — the boolean LM-item→project linkage grid the code returns
   today. Header row + one row per stage summarizing "linked / unlinked" items. */
.rd-scope .p3-cov{border:1px solid var(--rd-line);border-radius:var(--rd-r-sm);overflow:hidden;}
.rd-scope .p3-cov-row{display:grid;grid-template-columns:1.7fr minmax(0,1fr) minmax(0,1fr) minmax(0,1fr);}
.rd-scope .p3-cov-row + .p3-cov-row{border-top:1px solid var(--rd-line-soft);}
.rd-scope .p3-cov-row.head{background:var(--rd-bg);border-bottom:1px solid var(--rd-line);}
.rd-scope .p3-cov-row.head .p3-cov-cell{font-size:11.5px;font-weight:600;color:var(--rd-text-2);}
.rd-scope .p3-cov-cell{padding:12px 14px;display:flex;align-items:center;font-size:13px;border-left:1px solid var(--rd-line-soft);color:var(--rd-text-2);}
.rd-scope .p3-cov-cell.rowlbl{border-left:none;font-size:13.5px;font-weight:500;color:var(--rd-text-1);gap:8px;}
.rd-scope .p3-cov-cell.rowlbl .di{width:8px;height:8px;border-radius:50%;flex:none;}
.rd-scope .p3-cov-cell.rowlbl.s1 .di{background:var(--rd-s1);} .rd-scope .p3-cov-cell.rowlbl.s2 .di{background:var(--rd-s2);}
.rd-scope .p3-cov-cell.rowlbl.s3 .di{background:var(--rd-s3);} .rd-scope .p3-cov-cell.rowlbl.s4 .di{background:var(--rd-s4);}
.rd-scope .p3-cov-cell.rowlbl.s5 .di{background:var(--rd-s5);}

.rd-scope .p3-verdict{font-weight:600;display:inline-flex;align-items:center;gap:6px;}
.rd-scope .p3-verdict.covered{color:var(--rd-ok);}
.rd-scope .p3-verdict.thin{color:var(--rd-warn-tx);}
.rd-scope .p3-verdict.gap{color:var(--rd-danger);}
.rd-scope .p3-verdict.pending{color:var(--rd-text-4);font-style:italic;font-weight:500;}

.rd-scope .p3-unaligned{margin-top:14px;padding:11px 15px;background:var(--rd-warn-bg);border-radius:var(--rd-r-xs);color:var(--rd-warn-tx);font-size:12.5px;line-height:1.5;display:flex;gap:9px;align-items:flex-start;}
.rd-scope .p3-unaligned i{color:#b8860b;font-size:11px;margin-top:2px;flex:none;}
.rd-scope .p3-unaligned b{color:var(--rd-text-1);font-weight:600;}
</style>

{{-- ── Resources summary — placeholder (ResourcesGateway not wired) ── --}}
<div class="p3-sec">
    <div class="p3-sec-hd">
        <span class="l">{{ __('stakeholder.rc.res_label') }}</span>
        <span class="s">{{ __('stakeholder.rc.res_sub') }}</span>
    </div>
    <div class="p3-res-strip">
        <div class="icn"><i class="fa fa-people-arrows"></i></div>
        <div class="cnt">
            <div class="h">{{ __('stakeholder.rc.res_coming_title') }}</div>
            <div class="d">{{ __('stakeholder.rc.res_coming_hint') }}</div>
        </div>
        <span class="tag">{{ __('stakeholder.p3_status_short') }}</span>
    </div>
</div>

{{-- ── Coverage matrix (boolean linkage — real data) ─────────────── --}}
<div class="p3-sec">
    <div class="p3-sec-hd">
        <span class="l">{{ __('stakeholder.rc.cov_label') }}</span>
        <span class="s">{{ __('stakeholder.rc.cov_sub') }}</span>
    </div>

    @if (! $hasLM || empty($logicModel['coverageMatrix']))
        <div class="rd-empty">{{ __('stakeholder.rc.no_matrix') }}</div>
    @else
        @php
            $cov = $logicModel['coverageMatrix'];
            $stages = $cov['stages'] ?? [];
            $cells = $cov['cells'] ?? [];
            $unaligned = $cov['unalignedColumns'] ?? [];
            $columns = $cov['columns'] ?? [];
            // Short stage keys — Marcel's stages/progress arrays are keyed by
            // 'inputs'/'activities'/... (Logicmodelcanvas::STAGES.key), not the
            // zp_canvas_items.box 'lm_*' names.
            $stageOrder = ['inputs' => 1, 'activities' => 2, 'outputs' => 3, 'outcomes' => 4, 'impact' => 5];

            // Per-stage tallies: how many items in the stage are linked to at least
            // one program/project. Anything unlinked contributes to the "unlinked" count.
            $stageStats = [];
            foreach ($stages as $key => $stage) {
                $items = $stage['items'] ?? [];
                $linked = 0;
                foreach ($items as $item) {
                    $itemId = (int) (((array) $item)['id'] ?? 0);
                    if (isset($cells[$itemId]) && count($cells[$itemId]) > 0) {
                        $linked++;
                    }
                }
                $total = count($items);
                $stageStats[$key] = ['linked' => $linked, 'total' => $total];
            }
        @endphp

        <div class="p3-cov">
            <div class="p3-cov-row head">
                <div class="p3-cov-cell">{{ __('stakeholder.rc.col_stage') }}</div>
                <div class="p3-cov-cell">{{ __('stakeholder.rc.col_items') }}</div>
                <div class="p3-cov-cell">{{ __('stakeholder.rc.col_linked') }}</div>
                <div class="p3-cov-cell">{{ __('stakeholder.rc.col_verdict') }}</div>
            </div>
            @foreach ($stageOrder as $stageKey => $stageNum)
                @php
                    $stage = $stages[$stageKey] ?? null;
                    if (! $stage) continue;
                    $st = $stageStats[$stageKey];
                    // Coverage verdict: derived from linkage today; resource backing
                    // will overlay per §10.d. Semantics: all linked → covered, some → thin,
                    // none in a populated stage → gap.
                    $verdict = 'covered';
                    $verdictLabel = __('stakeholder.rc.verdict.covered');
                    if ($st['total'] === 0) {
                        $verdict = 'pending';
                        $verdictLabel = __('stakeholder.rc.verdict.empty');
                    } elseif ($st['linked'] === 0) {
                        $verdict = 'gap';
                        $verdictLabel = __('stakeholder.rc.verdict.gap');
                    } elseif ($st['linked'] < $st['total']) {
                        $verdict = 'thin';
                        $verdictLabel = __('stakeholder.rc.verdict.thin');
                    }
                @endphp
                <div class="p3-cov-row">
                    <div class="p3-cov-cell rowlbl s{{ $stageNum }}">
                        <span class="di"></span>{{ __($stage['title']) }}
                    </div>
                    <div class="p3-cov-cell">{{ $st['total'] }}</div>
                    <div class="p3-cov-cell">{{ $st['linked'] }} / {{ $st['total'] }}</div>
                    <div class="p3-cov-cell">
                        <span class="p3-verdict {{ $verdict }}">
                            @if ($verdict === 'covered') ● @elseif ($verdict === 'thin') ◐ @elseif ($verdict === 'gap') ○ @else — @endif
                            {{ $verdictLabel }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        @if (count($unaligned) > 0)
            @php
                $unalignedNames = array_map(
                    fn ($id) => $columns[$id]['name'] ?? ('#'.$id),
                    array_slice($unaligned, 0, 5)
                );
                $more = max(0, count($unaligned) - 5);
            @endphp
            <div class="p3-unaligned">
                <i class="fa fa-diagram-project"></i>
                <div>
                    <b>{{ __('stakeholder.rc.off_strategy_label') }}:</b>
                    {{ sprintf(__('stakeholder.rc.off_strategy_hint'), count($unaligned)) }}
                    <em>{{ implode(', ', array_map(fn ($n) => $tpl->escape($n), $unalignedNames)) }}@if ($more > 0) +{{ $more }} more @endif</em>
                </div>
            </div>
        @endif
    @endif
</div>

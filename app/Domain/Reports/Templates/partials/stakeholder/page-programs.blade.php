{{--
    Stakeholder Report — Page 4 (Programs & Narrative)

    §5 page 4: Two columns — program rows with RAG + completed count on the
    left, status narrative from statusUpdates on the right. "Also this period"
    (secondary closures) at the bottom.

    Vars in:
      $scope           'strategy' | 'program'
      $programRows     object[]  (strategy scope; empty at program scope)
      $programUpdates  array<int,object[]>  (byProject at strategy scope)
--}}

<style>
.rd-scope .p4-two{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.25fr);gap:30px;}
.rd-scope .p4-lbl{font-size:10px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--rd-accent);margin-bottom:12px;}
.rd-scope .p4-prog{display:flex;align-items:center;gap:10px;padding:12px 0;border-bottom:1px solid var(--rd-line-soft);font-size:14px;}
.rd-scope .p4-prog .pd{width:9px;height:9px;border-radius:50%;flex:none;}
.rd-scope .p4-prog .pn{flex:1;font-weight:500;color:var(--rd-text-1);min-width:0;}
.rd-scope .p4-prog .pm{color:var(--rd-text-3);font-size:12.5px;}
.rd-scope .p4-prog:last-child{border-bottom:none;}
.rd-scope .p4-exec{font-size:13.5px;color:var(--rd-text-2);line-height:1.6;padding:11px 0;border-bottom:1px solid var(--rd-line-soft);}
.rd-scope .p4-exec b{color:var(--rd-text-1);font-weight:600;}
.rd-scope .p4-exec .ed{color:var(--rd-text-4);font-size:12px;margin-left:6px;}
.rd-scope .p4-exec:last-child{border-bottom:none;}
.rd-scope .p4-also{margin-top:22px;padding-top:16px;border-top:1px solid var(--rd-line);font-size:13.5px;color:var(--rd-text-2);}
.rd-scope .p4-also .al-lb{color:var(--rd-text-3);font-weight:600;text-transform:uppercase;letter-spacing:.4px;font-size:11px;}
.rd-scope .p4-empty{color:var(--rd-text-3);font-size:12.5px;font-style:italic;padding:12px 0;}
</style>

<div class="p4-two">
    {{-- ── Programs column (strategy scope only) ─────────────────── --}}
    <div>
        <div class="p4-lbl">{{ $scope === 'strategy' ? __('stakeholder.programs.label_programs') : __('stakeholder.programs.label_child_projects') }}</div>
        @if (count($programRows) === 0)
            <div class="p4-empty">{{ $scope === 'strategy' ? __('stakeholder.programs.none') : __('stakeholder.programs.none_projects') }}</div>
        @else
            @foreach ($programRows as $row)
                @php
                    $row = (array) $row;
                    // Status → dot color. programRows carries a status field from Marcel's
                    // buildProgramRows (worst-of-children rollup); values: green/yellow/red/null.
                    $status = (string) ($row['status'] ?? '');
                    $dotColor = match ($status) {
                        'green'  => 'var(--rd-ok)',
                        'yellow' => 'var(--rd-warn)',
                        'red'    => 'var(--rd-danger)',
                        default  => 'var(--rd-text-4)',
                    };
                    $statusLabel = match ($status) {
                        'green'  => __('stakeholder.programs.status_ontrack'),
                        'yellow' => __('stakeholder.programs.status_atrisk'),
                        'red'    => __('stakeholder.programs.status_off'),
                        default  => __('stakeholder.programs.status_none'),
                    };
                    $completedCt = (int) ($row['completedCount'] ?? 0);
                @endphp
                <div class="p4-prog">
                    <span class="pd" style="background:{{ $dotColor }}"></span>
                    <span class="pn">{{ $tpl->escape($row['name'] ?? '') }}</span>
                    <span class="pm">{{ $statusLabel }} · {{ sprintf(__('stakeholder.programs.done_count'), $completedCt) }}</span>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ── Status narrative column ───────────────────────────────── --}}
    <div>
        <div class="p4-lbl">{{ __('stakeholder.programs.label_narrative') }}</div>
        @php
            // programUpdates is keyed by projectId. Flatten with a name lookup from
            // programRows for the bold label per line.
            $namesByProject = [];
            foreach ($programRows as $row) {
                $row = (array) $row;
                $namesByProject[(int) ($row['id'] ?? 0)] = $row['name'] ?? '';
            }
            $flatUpdates = [];
            foreach ($programUpdates as $pid => $updates) {
                foreach ($updates as $update) {
                    $update = (object) $update;
                    $update->_projectName = $namesByProject[(int) $pid] ?? '';
                    $flatUpdates[] = $update;
                }
            }
            // Sort newest first — Marcel returns in the same order per project;
            // for cross-project flat display we re-sort by date desc.
            usort($flatUpdates, fn ($a, $b) => strcmp((string) ($b->date ?? ''), (string) ($a->date ?? '')));
            $flatUpdates = array_slice($flatUpdates, 0, 5);  // top 5 for the packet view
        @endphp
        @if (count($flatUpdates) === 0)
            <div class="p4-empty">{{ __('stakeholder.programs.no_updates') }}</div>
        @else
            @foreach ($flatUpdates as $u)
                @php
                    $date = ! empty($u->dateParsed) ? $u->dateParsed->setToUserTimezone()->format('M j') : '';
                    $text = trim(strip_tags((string) ($u->text ?? '')));
                    // Truncate long updates — board views want the executive summary.
                    if (mb_strlen($text) > 220) $text = mb_substr($text, 0, 217).'…';
                @endphp
                <div class="p4-exec">
                    @if (! empty($u->_projectName))
                        <b>{{ $tpl->escape($u->_projectName) }}</b> —
                    @endif
                    {{ $tpl->escape($text) }}
                    @if ($date !== '') <span class="ed">{{ $date }}</span> @endif
                </div>
            @endforeach
        @endif
    </div>
</div>

{{-- "Also this period" — secondary closures beyond the peak-this-period hero.
     Nomination surface for closures that could be attached to an outcome.
     Requires the nomination pass that also feeds the p1 hero; render coming-soon
     until that lands, to avoid pretending an empty state is a full one. --}}
<div class="p4-also">
    <span class="al-lb">{{ __('stakeholder.programs.also_label') }}</span>
    <span style="color:var(--rd-text-3);"> — {{ __('stakeholder.programs.also_coming') }}</span>
</div>

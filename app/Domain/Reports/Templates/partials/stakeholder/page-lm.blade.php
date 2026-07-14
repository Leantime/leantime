{{--
    Stakeholder Report — Page 2 (Logic Model read-out)

    §5 page 2: 5-stage board (Resources → Activities → Outputs → Outcomes →
    Impact) rendered as read-out cards following the task-card standard.
    Connection-health badges between stages from zp_logicmodel_health.

    Vars in:
      $logicModel  null | {canvasId, narrative, stageProgress, healthBadges, coverageMatrix}
      $hasLM       bool
--}}

@if (! $hasLM)
    <div class="rd-empty">{{ __('stakeholder.lm.no_canvas') }}</div>
@else
    @php
        // Stages come nested inside coverageMatrix (Marcel's structure). Each stage
        // is {title, icon, items}. Items are raw canvas_item rows.
        $stages = $logicModel['coverageMatrix']['stages'] ?? [];
        $progress = $logicModel['stageProgress'] ?? [];
        $healthBadges = $logicModel['healthBadges'] ?? [];

        // Stage key → visible number (1..5). NOTE: Marcel's stages array is keyed by
        // SHORT names ('inputs', 'activities', ...), not the zp_canvas_items.box names
        // ('lm_inputs', ...). Logicmodelcanvas::STAGES uses `key => 'inputs'` and
        // getItemsByStage() returns items under those short keys via BOX_TO_STAGE.
        $stageOrder = ['inputs' => 1, 'activities' => 2, 'outputs' => 3, 'outcomes' => 4, 'impact' => 5];

        // Extract owner initials from a canvas_item row (author has firstname/lastname
        // via a join in most repos; fallback to '?' if missing).
        $initials = function (array $item): string {
            $first = trim((string) ($item['firstname'] ?? ''));
            $last  = trim((string) ($item['lastname'] ?? ''));
            $ini = strtoupper(substr($first, 0, 1).substr($last, 0, 1));
            return $ini !== '' ? $ini : '?';
        };

        // Status pill mapping: canvas item statuses → mockup pill classes + labels.
        $pillFor = function (string $status): array {
            return match ($status) {
                'status_valid'   => ['cls' => 'rd-pill-ok',    'lb' => __('logicmodel.status.validated')],
                'status_review'  => ['cls' => 'rd-pill-wip',   'lb' => __('logicmodel.status.review')],
                'status_hold'    => ['cls' => 'rd-pill-flag',  'lb' => __('logicmodel.status.paused')],
                'status_invalid' => ['cls' => 'rd-pill-flag',  'lb' => __('logicmodel.status.invalid')],
                default          => ['cls' => 'rd-pill-draft', 'lb' => __('logicmodel.status.draft')],
            };
        };
    @endphp

    <style>
    .rd-scope .p2-boardbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;}
    .rd-scope .p2-boardbar .t{font-size:14px;font-weight:600;color:var(--rd-text-1);}
    .rd-scope .p2-boardbar .t .sub{font-weight:400;color:var(--rd-text-3);}
    .rd-scope .p2-board{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;align-items:start;}
    .rd-scope .p2-col{border:1px solid var(--rd-line-soft);border-radius:var(--rd-r-sm);background:#fbfcfc;min-width:0;}
    .rd-scope .p2-col.heart{background:#fff;box-shadow:var(--rd-sh-sm);border-color:transparent;}
    .rd-scope .p2-col-hd{padding:11px 10px 9px;text-align:center;position:relative;border-bottom:2px solid var(--rd-line-soft);}
    .rd-scope .p2-col.heart .p2-col-hd{border-bottom-width:3px;}
    .rd-scope .p2-col-ic{width:29px;height:29px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;margin:0 auto 6px;}
    .rd-scope .p2-col-name{font-size:14.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px;color:var(--rd-text-1);}
    .rd-scope .p2-col-count{font-size:10px;font-weight:700;color:#fff;width:17px;height:17px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;}
    .rd-scope .p2-col-sub{font-size:10.5px;color:var(--rd-text-3);margin-top:1px;}
    .rd-scope .p2-col-progress{font-size:10.5px;color:var(--rd-text-3);margin-top:4px;}

    /* Connection health dot — top-right of the target stage's header. */
    .rd-scope .p2-conn{position:absolute;top:11px;right:11px;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9px;}
    .rd-scope .p2-conn.ok{background:#eaf5ef;color:#3d9e78;border:1px solid #cfe8dc;}
    .rd-scope .p2-conn.warning{background:#fbf0d8;color:#b8860b;border:1px solid #ecdaa8;}
    .rd-scope .p2-conn.risk{background:var(--rd-danger-bg);color:var(--rd-danger);border:1px solid #f0c6d5;}

    /* Stage-colored headers + left-border cards. */
    .rd-scope .p2-col.s1 .p2-col-ic{background:var(--rd-s1-bg);color:var(--rd-s1);} .rd-scope .p2-col.s1 .p2-col-count{background:var(--rd-s1);} .rd-scope .p2-col.s1 .p2-col-hd{border-bottom-color:var(--rd-s1);} .rd-scope .p2-col.s1 .rd-cardx{border-left-color:var(--rd-s1);}
    .rd-scope .p2-col.s2 .p2-col-ic{background:var(--rd-s2-bg);color:var(--rd-s2);} .rd-scope .p2-col.s2 .p2-col-count{background:var(--rd-s2);} .rd-scope .p2-col.s2 .p2-col-hd{border-bottom-color:var(--rd-s2);} .rd-scope .p2-col.s2 .rd-cardx{border-left-color:var(--rd-s2);}
    .rd-scope .p2-col.s3 .p2-col-ic{background:var(--rd-s3-bg);color:var(--rd-s3);} .rd-scope .p2-col.s3 .p2-col-count{background:var(--rd-s3);} .rd-scope .p2-col.s3 .p2-col-hd{border-bottom-color:var(--rd-s3);} .rd-scope .p2-col.s3 .rd-cardx{border-left-color:var(--rd-s3);}
    .rd-scope .p2-col.s4 .p2-col-ic{background:var(--rd-s4);color:#fff;} .rd-scope .p2-col.s4 .p2-col-count{background:var(--rd-s4);} .rd-scope .p2-col.s4 .p2-col-hd{border-bottom-color:var(--rd-s4);} .rd-scope .p2-col.s4 .rd-cardx{border-left-color:var(--rd-s4);}
    .rd-scope .p2-col.s5 .p2-col-ic{background:var(--rd-s5-bg);color:var(--rd-s5);} .rd-scope .p2-col.s5 .p2-col-count{background:var(--rd-s5);} .rd-scope .p2-col.s5 .p2-col-hd{border-bottom-color:var(--rd-s5);} .rd-scope .p2-col.s5 .rd-cardx{border-left-color:var(--rd-s5);}

    .rd-scope .p2-col-body{padding:8px 7px;display:flex;flex-direction:column;gap:7px;}
    .rd-scope .p2-col-body.empty{padding:14px 12px;color:var(--rd-text-3);font-size:11.5px;text-align:center;font-style:italic;}
    </style>

    <div class="p2-boardbar">
        <div class="t">{{ __('stakeholder.lm.board_title') }} <span class="sub">— {{ __('stakeholder.lm.board_sub') }}</span></div>
    </div>

    <div class="p2-board">
        @foreach ($stageOrder as $stageKey => $stageNum)
            @php
                $stage = $stages[$stageKey] ?? ['title' => $stageKey, 'icon' => 'fa-square', 'items' => []];
                $items = $stage['items'];
                $isHeart = ($stageKey === 'outcomes');   // Outcomes gets the "heart" emphasis
                $prog = $progress[$stageKey] ?? null;
                // Connection badge appears on stages 2..5 (badge index = incoming edge = stageNum-1).
                $badge = $stageNum > 1 ? ($healthBadges[$stageNum - 1] ?? null) : null;
                $showBadge = $badge && ! empty($badge['has_data']);
            @endphp
            <div class="p2-col s{{ $stageNum }} @if ($isHeart) heart @endif" data-s="{{ $stageNum }}">
                <div class="p2-col-hd">
                    @if ($showBadge)
                        <div class="p2-conn {{ $badge['health_status'] ?? 'ok' }}" title="{{ $badge['connector_label'] ?? '' }}: {{ $badge['assumption_text'] ?? '' }}">
                            @if (($badge['health_status'] ?? '') === 'ok')
                                <i class="fa fa-check"></i>
                            @else
                                <i class="fa fa-triangle-exclamation"></i>
                            @endif
                        </div>
                    @endif
                    <div class="p2-col-ic"><i class="fa {{ $stage['icon'] }}"></i></div>
                    <div class="p2-col-name">{{ __($stage['title']) }} <span class="p2-col-count">{{ count($items) }}</span></div>
                    <div class="p2-col-sub">{{ __('stakeholder.lm.stage_sub.'.$stageKey) }}</div>
                    @if ($prog && ($prog['total'] ?? 0) > 0)
                        <div class="p2-col-progress">{{ (int) $prog['validated'] }}/{{ (int) $prog['total'] }} · {{ (int) $prog['percent'] }}%</div>
                    @endif
                </div>
                <div class="p2-col-body @if (count($items) === 0) empty @endif">
                    @if (count($items) === 0)
                        {{ __('stakeholder.lm.stage_empty') }}
                    @else
                        @foreach ($items as $item)
                            @php
                                $item = (array) $item;
                                $title = trim((string) ($item['description'] ?? ''));
                                $assumption = trim((string) ($item['assumptions'] ?? ''));
                                $pill = $pillFor((string) ($item['status'] ?? ''));
                            @endphp
                            <div class="rd-cardx">
                                <div class="rd-cx-top">
                                    <div class="rd-cx-t">{{ $title !== '' ? $title : __('stakeholder.lm.untitled') }}</div>
                                    <div class="rd-cx-corner">
                                        <span class="rd-pill {{ $pill['cls'] }}">{{ $pill['lb'] }}</span>
                                        <span class="rd-avatar" title="{{ trim(($item['firstname'] ?? ''.' '.($item['lastname'] ?? ''))) }}">{{ $initials($item) }}</span>
                                    </div>
                                </div>
                                @if ($assumption !== '')
                                    <div class="rd-cx-hyp">
                                        @if ($stageKey === 'outcomes') <span class="hl">{{ __('stakeholder.lm.assumption') }}:</span> @endif
                                        {{ $assumption }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

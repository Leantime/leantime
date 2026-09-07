@extends($layout)
@section('content')
    @php
        $hiddenRelatesLabels = $relatesLabels ?? [];

        // Metric preview — from the SAVED values; re-renders on save.
        $mType  = $canvasItem['metricType'] ?: 'number';  // new goals default to # (no math)
        $mStart = (float) ($canvasItem['startValue'] ?? 0);
        $mCur   = (float) ($canvasItem['currentValue'] ?? 0);
        $mGoal  = (float) ($canvasItem['endValue'] ?? 0);
        $mPct   = ($mGoal - $mStart) != 0 ? max(0, min(100, (($mCur - $mStart) / ($mGoal - $mStart)) * 100)) : 0;
        $fmtM = function ($v) use ($mType) {
            $v = (float) $v;
            if ($mType === 'percent')  return rtrim(rtrim(number_format($v, 2), '0'), '.') . '%';
            if ($mType === 'currency') return '$' . number_format($v, $v == floor($v) ? 0 : 2);
            return rtrim(rtrim(number_format($v, 2), '0'), '.');
        };
    @endphp
    <script type="text/javascript">
        window.onload = function() {
            if (!window.jQuery) {
                location.href = "{{ BASE_URL }}/goalcanvas/showCanvas?showModal={{ $canvasItem['id'] }}";
            }
        }
    </script>

    <style>
        /* ── Goal dialog v1 — tabbed (Goal / Progress / Milestones) so only one
              zone shows at a time. Presentation only: fields keep name/id/class. ── */
        /* System design tokens, not a private palette (alignment pass
           2026-08-03): ink/lines/accent/sizes derive from the theme, so the
           dialog matches the rest of the app in BOTH themes. */
        .gvDialog{width:860px;max-width:min(860px, 94vw);padding:6px 18px 14px;
            --gv-acc:var(--accent1);
            --gv-acc2:var(--accent1);
            --gv-line:var(--main-border-color);
            --gv-line-soft:color-mix(in srgb, var(--main-border-color) 55%, transparent);
            --gv-ink:var(--primary-font-color);
            --gv-ink2:color-mix(in srgb, var(--primary-font-color) 68%, transparent);
            --gv-soft:var(--secondary-background);}

        /* Two-column split (Docs-article pattern, review 2026-08-03): main
           work area left, "Details" rail right with the essentials. The rail
           hugs its content (no full-height stretch — that stranded Delete in
           dead space) and keeps a tight label-over-field rhythm. */
        .gv-cols{display:grid;grid-template-columns:minmax(0,1fr) 240px;gap:0 28px;align-items:start;}
        .gv-side{border-left:1px solid var(--gv-line-soft);padding-left:24px;display:flex;flex-direction:column;gap:16px;}
        .gv-side-head{margin:0;}
        .gv-side label{margin-bottom:5px;}
        .gv-side .gv-dates{grid-template-columns:1fr;max-width:none;gap:12px;}
        /* Beside the modal's × — right-padding clears the close button.
           position:relative so the z-index actually applies (it is inert on a
           statically positioned element) and the open dropdown clears the
           surrounding chrome. */
        .gv-actions-menu{float:right;position:relative;z-index:50;padding:10px 34px 0 0;}
        /* Discussion sits under the MAIN column (its own form — kept outside
           the goal form so the nested-form parse never orphans the Save
           buttons again). */
        .gv-discussion{margin-right:calc(240px + 28px);}
        /* Discussion stays quiet — smaller avatar so the profile color doesn't
           compete with the work area (review 2026-08-03). */
        .gv-discussion .commentImage img,.gv-discussion .commentImage .profileImage{width:30px!important;height:30px!important;}
        @media (max-width:900px){.gv-cols{grid-template-columns:1fr;}.gv-side{border-left:none;padding-left:0;}.gv-discussion{margin-right:0;}}
        /* Section headers ride the SYSTEM recipe (h4.widgettitle.title-light,
           same as the task modal) — no dialog-private eyebrow styles. */
        .gvDialog > h4.widgettitle{margin:2px 0 12px;}
        .gvDialog label{font-size:var(--font-size-s);font-weight:600;color:var(--gv-ink2);display:block;margin:0 0 6px;}
        .gv-field-lbl{font-size:var(--font-size-s);color:var(--gv-ink2);margin:0 0 6px;}
        .gv-unit{font-size:var(--font-size-xs);font-weight:700;color:var(--gv-acc);opacity:.85;}

        /* tab bar — report deck style (gradient bar + translucent group + white active pill) */
        /* Tab visuals come from the shared floating-pill standard
           (tab-group.css: .lt-tabs--floating + --onlight for this white
           modal surface); only the dialog-specific spacing stays here. */
        .gv-tabs{margin:0 0 16px;}
        .gv-tab i,.gv-tab span[class*="fa"]{font-size:12px;}
        .gv-panel{min-height:170px;}
        .gv-row{margin-bottom:18px;}

        /* Inputs and selects are NOT restyled here. This block used to override
           border/radius/padding/background/focus with !important, which is why the
           dialog's fields looked unlike every other form in the app. They now take
           the app's own forms.css styling, same as the task modal. The title uses
           the shared component's variant="headline" instead of a private rule.
           Only the inline-edit metric input below keeps a bespoke look, because it
           is a deliberately different affordance (the number IS the field). */
        .gvDialog input[name="title"]{width:100%;}
        .gvDialog select{width:100%;}

        /* Progress readout — deliberately QUIET (review 2026-08-03: the big
           teal number + gradient card pulled the eye away from the actual
           task). Ink-colored number, thin flat bar, no card, no gradient —
           the page's color budget belongs to the action (Save / the input). */
        .gv-metric-bar{padding:2px 2px 0;margin:4px 0 0;}
        .gv-mb-metric{font-size:var(--font-size-s);color:var(--gv-ink2);margin-bottom:7px;}
        .gv-metric-bar .gv-mb-top{display:flex;align-items:baseline;gap:6px;margin-bottom:8px;}
        .gv-mb-togo{margin-left:auto;font-size:var(--font-size-s);color:var(--gv-ink2);}
        .gv-mb-barrow{display:grid;grid-template-columns:minmax(0,1fr) 38px;gap:12px;align-items:start;}
        .gv-mb-pct{font-size:var(--font-size-s);color:var(--gv-ink2);text-align:right;font-variant-numeric:tabular-nums;align-self:start;line-height:12px;}
        /* Scored track — the RA planbar recipe: striped remaining segment,
           quarter score marks, and a position marker with a halo. */
        .gv-track--scored{position:relative;height:12px;border-radius:6px;overflow:visible;
            background:repeating-linear-gradient(135deg, var(--gv-line-soft) 0 5px, color-mix(in srgb, var(--main-border-color) 30%, transparent) 5px 10px);}
        .gv-track--scored .gv-fill{position:absolute;inset:0 auto 0 0;border-radius:6px 0 0 6px;opacity:1;}
        .gv-tick{position:absolute;top:2px;bottom:2px;width:1px;background:color-mix(in srgb, var(--gv-ink) 22%, transparent);}
        .gv-marker{position:absolute;top:-3px;bottom:-3px;width:2px;border-radius:2px;background:var(--gv-ink);box-shadow:0 0 0 1.5px var(--primary-background, #fff);transform:translateX(-1px);}
        /* Start / Now / Goal under the bar. Three equal columns so "Now" lands
           centred between its two anchors — the current value used to float
           above the bar with no visible label at all. */
        .gv-scale{display:grid;grid-template-columns:1fr auto 1fr;align-items:start;gap:12px;margin-top:7px;font-size:var(--font-size-xs);color:var(--gv-ink2);font-variant-numeric:tabular-nums;}
        .gv-scale-col{display:flex;flex-direction:column;gap:2px;min-width:0;}
        .gv-scale-col--now{align-items:center;text-align:center;}
        .gv-scale-col--goal{align-items:flex-end;text-align:right;}
        .gv-scale-lbl{font-size:var(--font-size-xs);text-transform:uppercase;letter-spacing:.4px;font-weight:600;opacity:.75;line-height:1.2;}
        .gv-scale-val{font-size:var(--font-size-s);color:var(--gv-ink);line-height:1.2;}
        /* The centre column holds the inline-edit input, which is taller than the
           plain end values — pull its label tight so the three baselines agree. */
        .gv-scale-col--now .gv-scale-lbl{margin-bottom:1px;}
        /* Centre the digits inside the field, not just the field inside the
           column: with the fixed-ch fallback width the number otherwise sits
           left while its label sits centred above it. */
        .gvDialog .gv-metric-bar .gv-scale-col--now input.gv-mb-input{text-align:center;}
        /* The input hugs its digits (no fill-in-the-blank dashes past the
           number) in browsers with field-sizing; others keep the fixed ch. */
        @supports (field-sizing: content){
            .gvDialog .gv-metric-bar input.gv-mb-input{field-sizing:content;width:auto!important;min-width:2.5ch!important;max-width:10ch!important;}
        }
        .gv-metric-bar .gv-mb-now{font-size:var(--font-size-xl);font-weight:700;letter-spacing:-.2px;color:var(--gv-ink);line-height:1;}
        .gv-metric-bar .gv-mb-of{font-size:var(--font-size-s);color:var(--gv-ink2);}
        .gv-metric-bar .gv-mb-of b{color:var(--gv-ink);font-weight:600;}
        .gv-track{height:6px;background:var(--gv-line-soft);border-radius:4px;overflow:hidden;}
        .gv-fill{height:100%;border-radius:4px;background:var(--gv-acc);opacity:.8;}
        .gv-values{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;max-width:480px;}

        /* Inline value edit — the RA pattern: the number IS the input.
           (Selector carries the element+class so the dialog's generic
           input[type=number] rule — same importance — can never outrank it.) */
        .gvDialog .gv-metric-bar input.gv-mb-input{font-size:var(--font-size-xl)!important;font-weight:700!important;color:var(--gv-ink)!important;border:none!important;border-bottom:1px dashed var(--gv-line)!important;border-radius:0!important;background:transparent!important;padding:0 2px 2px!important;width:5.5ch!important;height:auto!important;box-shadow:none!important;-moz-appearance:textfield;}
        .gv-mb-input::-webkit-outer-spin-button,.gv-mb-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0;}
        .gvDialog .gv-metric-bar input.gv-mb-input:hover{border-bottom-color:var(--gv-ink2)!important;}
        .gvDialog .gv-metric-bar input.gv-mb-input:focus{border-bottom:1px solid var(--gv-acc)!important;outline:none!important;box-shadow:none!important;}

        /* Milestone bars on the Progress tab — read-only rows, quiet. */
        .gv-ms-bars-section{margin-top:22px;padding-top:16px;border-top:1px solid var(--gv-line-soft);}
        .gv-ms-bars-head{margin:0 0 12px;}
        .gv-ms-bars{display:flex;flex-direction:column;gap:11px;}
        .gv-msb-row{display:grid;grid-template-columns:9px minmax(0,1fr) 130px 38px;align-items:center;gap:12px;}
        .gv-msb-dot{width:9px;height:9px;border-radius:50%;}
        .gv-msb-name{font-size:var(--font-size-s);color:var(--gv-ink2);text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .gv-msb-name:hover{color:var(--gv-acc);text-decoration:underline;text-underline-offset:3px;}
        .gv-msb-track{height:5px;background:var(--gv-line-soft);border-radius:4px;overflow:hidden;}
        .gv-msb-fill{height:100%;border-radius:4px;opacity:.75;}
        .gv-msb-pct{font-size:var(--font-size-s);color:var(--gv-ink2);text-align:right;font-variant-numeric:tabular-nums;}

        /* Milestones tab — MANAGEMENT list (RA line-item style). */
        .gv-ms-list{display:flex;flex-direction:column;}
        /* One merged row: dot | name | due | bar | % | unlink. The grid keeps the
           bar and percent columns aligned down the list (a flex row let them
           drift with the name length). */
        .gv-ms-item{display:grid;grid-template-columns:9px minmax(0,1fr) auto 130px 38px 28px;align-items:center;gap:10px;padding:9px 2px;border-bottom:1px solid var(--gv-line-soft);}
        .gv-ms-name{font-size:var(--base-font-size);color:var(--gv-ink);text-decoration:none;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .gv-ms-name:hover{color:var(--gv-acc);text-decoration:underline;text-underline-offset:3px;}
        .gv-ms-due{font-size:var(--font-size-s);color:var(--gv-ink2);white-space:nowrap;}
        /* The last row closes on the panel's own edge — a trailing rule under it
           is the "odd line above Save" and reads as a separator to nothing. */
        .gv-ms-list .gv-ms-item:last-child{border-bottom:0;}
        .gv-ms-remove{background:transparent;border:none;cursor:pointer;padding:6px 4px;opacity:.55;color:var(--gv-ink2);justify-self:end;}
        .gv-ms-remove:hover{opacity:1;color:var(--gv-acc);}
        .gv-values .gv-field-lbl{font-size:11px;}
        .gv-dates{display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:360px;}

        /* milestones panel */
        .gv-ms-head{display:flex;align-items:center;gap:10px;margin-bottom:2px;}
        .gv-ms-head .gv-ms-bars-head{margin:0;}
        /* Its own line under the heading, not inline with it. */
        .gv-ms-summary{font-size:var(--font-size-s);color:var(--gv-ink2);margin:0 0 12px;}
        .gv-ms-summary b{color:var(--gv-ink);font-weight:700;}
        .gv-ms-actions{margin-left:auto;display:flex;align-items:center;gap:14px;}
        .gv-ms-act{background:none!important;border:none!important;cursor:pointer;color:var(--gv-ink2)!important;font-size:15px;padding:0;line-height:1;opacity:.65;transition:opacity .12s,color .12s;}
        .gv-ms-act:hover{opacity:1;color:var(--gv-acc)!important;}
        .gv-ms-actions .helperTooltip{color:var(--gv-ink2)!important;opacity:.55;}


        /* discussion sub-heading inside the Goal tab */

        /* actions (always visible under the tabs) */
        /* No top rule: the panel above already ends with its own divider, so a
           second line right above Save read as a stray separator. */
        .gv-actions{display:flex;align-items:center;gap:10px;margin-top:22px;}

        @media (max-width:560px){.gv-values{grid-template-columns:1fr 1fr;}}
    </style>

    <div class="gvDialog">

        {{-- Destructive action lives in a ⋮ menu at the top right, beside the
             modal's × — the same place and markup the task modal uses. It used
             to sit at the BOTTOM of the Details rail, which is both a different
             spot from every other entity dialog and an odd resting place for the
             one irreversible action. --}}
        @if ($login::userIsAtLeast($roles::$editor) && $id != '')
            <div class="inlineDropDownContainer gv-actions-menu">
                <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown" aria-label="{{ __('label.actions') }}">
                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu">
                    <li class="nav-header">{{ $canvasTypes[$canvasItem['box']]['title'] }}</li>
                    <li>
                        <a href="{{ BASE_URL }}/goalcanvas/delCanvasItem/{{ $id }}" class="formModal delete">
                            <i class="fa fa-trash-can"></i> {{ __('links.delete') }}
                        </a>
                    </li>
                </ul>
            </div>
        @endif

        {{-- Section headers use the SYSTEM modal recipe (h4.widgettitle
             .title-light — same as Subtasks/Discussion/Schedule on the task
             modal), not a dialog-private eyebrow style. --}}
        <h4 class="widgettitle title-light"><i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] }}"></i> {{ $canvasTypes[$canvasItem['box']]['title'] }}</h4>

        <form class="formModal" method="post" action="{{ BASE_URL . "/goalcanvas/editCanvasItem/$id" }}">

            <input type="hidden" value="{{ $currentCanvas }}" name="canvasId">
            <input type="hidden" value="{{ $canvasItem['box'] }}" name="box" id="box">
            <input type="hidden" value="{{ $id }}" name="itemId" id="itemId">
            <input type="hidden" name="changeItem" value="1">

            <div class="gv-cols">
            <div class="gv-main">

            {{-- ── Tabs on top — the nav frames the whole workspace (review
                 2026-08-03). The old Goal tab dissolved: name lives below the
                 tabs (shared by both), status/dates/relates in the Details
                 rail. New goals have only Progress, so no bar at all. ── --}}
            @if ($id !== '')
                <div class="gv-tabs lt-tabs lt-tabs--floating lt-tabs--onlight" role="tablist" aria-label="{{ __('goalcanvas.tabs_label') }}">
                    <div class="gv-tab-group lt-tabs-group">
                        <button type="button" class="gv-tab lt-tab" role="tab" id="gvTab-edit" aria-controls="gvPanel-edit" aria-selected="false" data-tab="edit"><i class="fa-solid fa-pen" aria-hidden="true"></i> {{ __('links.edit') }}</button>
                        <button type="button" class="gv-tab lt-tab" role="tab" id="gvTab-progress" aria-controls="gvPanel-progress" aria-selected="false" data-tab="progress"><i class="fa-solid fa-ranking-star" aria-hidden="true"></i> {{ __('goalcanvas.tab_progress') }}</button>
                    </div>
                </div>
            @endif

            {{-- Name — one label only (the placeholder); the modal's GOAL
                 header already names the object, so no third repetition. --}}
            <div class="gv-row">
                {{-- variant="headline" is the shared component's title treatment —
                     the same one the task modal uses for its headline — replacing a
                     block of dialog-private !important overrides on input[name=title]. --}}
                <x-global::forms.text-input variant="headline" name="title" id="goalTitleInput" value="{{ $canvasItem['title'] }}" placeholder="{{ __('goalcanvas.name_goal') }}" aria-label="{{ __('goalcanvas.name_goal') }}" style="width:100%" />
            </div>

            {{-- ── Tab: Edit — the goal's DEFINITION (metric, type, start,
                 target). One-time setup, separated from the recurring
                 monitoring job (review 2026-08-03). --}}
            <div class="gv-panel" data-panel="edit" role="tabpanel" id="gvPanel-edit" aria-labelledby="gvTab-edit" tabindex="0">
                <div id="measureGoalContainer" class="gv-row">
                    <label class="control-label" for="goalDescriptionInput">{{ __('goalcanvas.metric_label') }}</label>
                    <x-global::forms.text-input name="description" id="goalDescriptionInput" value="{{ $canvasItem['description'] }}" style="width:100%" />
                </div>

                <div class="gv-values">
                    <div>
                        <label class="control-label" for="goalMetricType">{{ __('label.type') }}</label>
                        <select name="metricType" id="goalMetricType">
                            <option value="number" @if ($mType == 'number') selected @endif>{{ __('goalcanvas.type_number') }}</option>
                            <option value="percent" @if ($mType == 'percent') selected @endif>{{ __('goalcanvas.type_percent') }}</option>
                            <option value="currency" @if ($mType == 'currency') selected @endif>{{ __('goalcanvas.type_currency') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="control-label" for="goalStartValue">{{ __('goalcanvas.v_start') }} <span class="gv-unit"></span></label>
                        <x-global::forms.text-input type="number" step="0.01" name="startValue" id="goalStartValue" value="{{ $canvasItem['startValue'] }}" style="width:100%" />
                    </div>
                    <div>
                        <label class="control-label" for="goalEndValue">{{ __('goalcanvas.v_goal') }} <span class="gv-unit"></span></label>
                        <x-global::forms.text-input type="number" step="0.01" name="endValue" id="goalEndValue" value="{{ $canvasItem['endValue'] }}" style="width:100%" />
                    </div>
                </div>
            </div>

            {{-- ── Tab: Progress — MONITORING. The RA pattern: the readout's
                 number IS the input (click in, type, blur = saved via HTMX).
                 All the bars live here — the goal metric plus each linked
                 milestone's own read-only bar. The Milestones tab MANAGES the
                 links; this tab watches them. --}}
            <div class="gv-panel" data-panel="progress" role="tabpanel" id="gvPanel-progress" aria-labelledby="gvTab-progress" tabindex="0">
                @include('goalcanvas::partials.progressReadout')

                {{-- The linked milestones — ONE list (summary + bars + management).
                     There used to be a read-only bar list here and a separate
                     Milestones TAB with the management list: the same milestones
                     twice, each view holding half the information. Merged, which
                     also lets the tab bar drop to the distinction that actually
                     matters — define the goal once (Edit) vs keep it current
                     (Progress). --}}
                @if ($id !== '')
                    <div class="gv-ms-bars-section">
                        @include('goalcanvas::partials.milestonesSection')

                        @if ($login::userIsAtLeast($roles::$editor))
                            <div class="row" id="newMilestone" style="display:none;">
                                <div class="col-md-12">
                                    <x-global::forms.text-input width="50%" name="newMilestone" /><br />
                                    <input type="hidden" name="type" value="milestone" />
                                    <input type="hidden" name="goalcanvasitemid" value="{{ $id }}" />
                                    <x-global::forms.button tag="input" inputType="button" :labelText="__('buttons.save')" onclick="jQuery('#primaryCanvasSubmitButton').click()" contentRole="primary" />
                                    <x-global::forms.button tag="input" inputType="button" :labelText="__('buttons.cancel')" onclick="leantime.goalCanvasController.toggleMilestoneSelectors('hide')" contentRole="tertiary" />
                                </div>
                            </div>
                            <div class="row" id="existingMilestone" style="display:none;">
                                <div class="col-md-12">
                                    <select data-placeholder="{{ __("input.placeholders.filter_by_milestone") }}" name="existingMilestone" class="user-select">
                                        <option value=""></option>
                                        @foreach ($milestones as $milestoneRow)
                                            <option value="{{ $milestoneRow->id }}">{{ $milestoneRow->headline }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="type" value="milestone" />
                                    <input type="hidden" name="goalcanvasitemid" value="{{ $id }}" />
                                    <x-global::forms.button tag="input" inputType="button" :labelText="__('buttons.save')" onclick="jQuery('#primaryCanvasSubmitButton').click()" contentRole="primary" />
                                    <x-global::forms.button tag="input" inputType="button" :labelText="__('buttons.cancel')" onclick="leantime.goalCanvasController.toggleMilestoneSelectors('hide')" contentRole="tertiary" />
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- ── Actions (main column; Delete lives in the Details rail) ── --}}
            @if ($login::userIsAtLeast($roles::$editor))
                <div class="gv-actions">
                    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" id="primaryCanvasSubmitButton" />
                    <x-global::forms.button inputType="submit" contentRole="secondary" id="saveAndClose" value="closeModal"
                        onclick="leantime.goalCanvasController.setCloseModal();">{{ __('buttons.save_and_close') }}</x-global::forms.button>
                </div>
            @endif

            </div>{{-- /gv-main --}}

            {{-- ── Details rail — the essentials (Docs-article pattern). ── --}}
            <aside class="gv-side">
                <h4 class="widgettitle title-light gv-side-head"><i class="fa fa-circle-info" aria-hidden="true"></i> {{ __('goalcanvas.side_details') }}</h4>

                <div class="form-group">
                    <label class="control-label" for="statusCanvas">{{ __('label.status') }}</label>
                    @if (!empty($statusLabels))
                        {{-- Plain <select>, options rendered server-side. This was a
                             SlimSelect instance while the Type select next to it was a
                             native one, which is what made the dialog show differently
                             styled dropdowns side by side. SlimSelect is still used by
                             other canvas templates and the ticket filter — this change
                             is local to the goal dialog, not a dependency removal. --}}
                        <select name="status" id="statusCanvas">
                            @foreach ($statusLabels as $key => $data)
                                @if ($data['active'])
                                    <option value="{{ $key }}" @if ($canvasItem['status'] == $key) selected @endif>{{ $data['title'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="status" value="{{ $canvasItem['status'] ?? array_key_first($hiddenStatusLabels) }}" />
                    @endif
                </div>

                {{-- One label per field ("Due Dates" + "Start Date" + "End
                     Date" was triple-labeling — part of the clutter). --}}
                {{-- Keep .startDate / .endDate exactly as they are: the script block
                     below calls initDateRangePicker('.startDate', '.endDate'), which
                     binds them as a LINKED RANGE (start constrains end). Adding the
                     generic `dates` class would attach a second, independent
                     datepicker to the same fields. --}}
                <div class="gv-dates">
                    <div class="form-group">
                        <label class="control-label" for="goalStartDate">{{ __('label.start_date') }}</label>
                        <input type="text" autocomplete="off" id="goalStartDate" value="{{ format($canvasItem['startDate'])->date() }}" name="startDate" class="startDate"/>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="goalEndDate">{{ __('label.end_date') }}</label>
                        <input type="text" autocomplete="off" id="goalEndDate" value="{{ format($canvasItem['endDate'])->date() }}" name="endDate" class="endDate"/>
                    </div>
                </div>

                <div class="form-group">
                    @dispatchEvent('beforeMeasureGoalContainer', $canvasItem)
                    @if (!empty($relatesLabels))
                        <label class="control-label" for="relatesCanvas">{{ __('label.relates') }}</label>
                        <select name="relates" id="relatesCanvas">
                            @foreach ($relatesLabels as $key => $data)
                                @if ($data['active'])
                                    <option value="{{ $key }}" @if ($canvasItem['relates'] == $key) selected @endif>{{ $data['title'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="relates" value="{{ $canvasItem['relates'] ?? array_key_first($hiddenRelatesLabels) }}">
                    @endif
                </div>

            </aside>
            </div>{{-- /gv-cols --}}

        </form>

        {{-- Discussion — the comments submodule brings its OWN <form>; it must
             stay a SIBLING of the goal form: nested, the HTML parser drops the
             inner form tag and its close tag closes the OUTER form, orphaning
             every field and button after it (Save did nothing). --}}
        @if ($id !== '')
            <div class="gv-discussion">
                <hr />
                <h4 class="widgettitle title-light"><span class="fa-solid fa-comments" aria-hidden="true"></span> {{ __('subtitles.discussion') }}</h4>
                @include('comments::submodules.generalComment', ['formUrl' => '/goalcanvas/editCanvasItem/' . $id])
            </div>
        @endif

    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {

            leantime.dateController.initDateRangePicker(".startDate", ".endDate");

            // Live unit cue on Start/Now/Goal so it's clear they're numbers.
            (function () {
                var typeSel = document.querySelector('.gvDialog select[name="metricType"]');
                if (typeSel) {
                    var units = { number: '#', percent: '%', currency: '$' };
                    var apply = function () {
                        var u = units[typeSel.value] || '#';
                        document.querySelectorAll('.gvDialog .gv-unit').forEach(function (s) { s.textContent = '(' + u + ')'; });
                    };
                    apply();
                    typeSel.addEventListener('change', apply);
                }
            })();

            // Tabs — one zone at a time; remembers the last-used tab.
            (function () {
                var tabs = document.querySelectorAll('.gvDialog .gv-tab');
                var panels = document.querySelectorAll('.gvDialog .gv-panel');
                if (!tabs.length) return;
                function show(name) {
                    var found = false;
                    panels.forEach(function (p) { var m = p.getAttribute('data-panel') === name; p.hidden = !m; p.style.display = m ? '' : 'none'; if (m) found = true; });
                    tabs.forEach(function (t) {
                        var active = t.getAttribute('data-tab') === name;
                        t.classList.toggle('is-active', active);
                        t.setAttribute('aria-selected', active ? 'true' : 'false');
                        t.tabIndex = active ? 0 : -1;
                    });
                    if (found) { try { localStorage.setItem('gvActiveTab', name); } catch (e) {} }
                    return found;
                }
                tabs.forEach(function (t, i) {
                    t.addEventListener('click', function () { show(t.getAttribute('data-tab')); });
                    // Roving-tabindex arrow-key navigation (WAI-ARIA tabs pattern).
                    t.addEventListener('keydown', function (e) {
                        var next = null;
                        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { next = tabs[(i + 1) % tabs.length]; }
                        else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { next = tabs[(i - 1 + tabs.length) % tabs.length]; }
                        else if (e.key === 'Home') { next = tabs[0]; }
                        else if (e.key === 'End') { next = tabs[tabs.length - 1]; }
                        if (next) { e.preventDefault(); show(next.getAttribute('data-tab')); next.focus(); }
                    });
                });
                var saved = null; try { saved = localStorage.getItem('gvActiveTab'); } catch (e) {}
                // Default = Progress: updating the value is the recurring job
                // this dialog exists for (stale saved names fall through too).
                if (!saved || !show(saved)) { if (!show('progress')) { show(tabs[0].getAttribute('data-tab')); } }
            })();

            {{-- SlimSelect initialisers removed for THIS dialog: #statusCanvas and
                 #relatesCanvas render their options server-side as plain <select>s
                 now, matching the task modal. SlimSelect itself is still used by the
                 other canvas templates and the ticket filter. --}}

            if (window.leantime && window.leantime.tiptapController) {
                leantime.tiptapController.initSimpleEditor();
            }

            @if (!$login::userIsAtLeast($roles::$editor))
            leantime.authController.makeInputReadonly(".nyroModalCont");
            @endif

            @if ($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
            @endif

        });
    </script>
@endsection

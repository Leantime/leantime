@extends($layout)
@section('content')
    @php
        $hiddenRelatesLabels = $relatesLabels ?? [];

        // Metric preview (mirrors the goal-view mock) — rendered from the SAVED
        // values; re-renders on save. Bar only shows when a goal target exists.
        $mType  = $canvasItem['metricType'] ?? 'number';
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
                //It's not a modal
                location.href = "{{ BASE_URL }}/goalcanvas/showCanvas?showModal={{ $canvasItem['id'] }}";
            }
        }
    </script>

    <style>
        /* ── Goal dialog v1 — single-column layout in the goal-view direction.
              Presentation only: every input/select keeps its name/id/class so the
              chosen (SlimSelect) dropdowns, date pickers, HTMX and save all work. ── */
        .gvDialog{width:640px;max-width:100%;padding:6px 14px 12px;
            --gv-acc:#00647a;--gv-acc2:#0e93a8;--gv-line:#e3e9eb;--gv-line-soft:#eef2f3;--gv-ink:#18272e;--gv-ink2:#586970;--gv-soft:#f5f8f9;}
        .gvDialog > h1{font-size:12px;font-weight:700;letter-spacing:.9px;text-transform:uppercase;color:var(--gv-ink2);margin:2px 0 18px;display:flex;align-items:center;gap:9px;}
        .gvDialog > h1 i{color:var(--gv-acc);font-size:15px;}
        .gvDialog label{font-size:12px;font-weight:600;color:var(--gv-ink2);display:block;margin:0 0 6px;}
        .gv-eyebrow{font-size:11px!important;font-weight:700!important;letter-spacing:.6px!important;text-transform:uppercase!important;color:var(--gv-acc)!important;}

        /* header: objective headline + status */
        .gv-head{display:flex;gap:22px;align-items:flex-start;margin-bottom:6px;}
        .gv-head .gv-obj{flex:1;min-width:0;}
        .gv-head .gv-status{flex:none;width:190px;}
        .gvDialog input[name="title"]{font-size:22px!important;font-weight:650!important;line-height:1.25!important;color:var(--gv-ink)!important;border:none!important;border-bottom:2px solid var(--gv-line)!important;border-radius:0!important;padding:4px 2px 9px!important;background:transparent!important;box-shadow:none!important;height:auto!important;}
        .gvDialog input[name="title"]:focus{border-bottom-color:var(--gv-acc)!important;outline:none!important;box-shadow:none!important;}
        .gvDialog input[name="title"]::placeholder{color:#aab6bb;font-weight:500;}

        /* sections */
        .gv-section{margin-top:26px;}
        .gv-sec-title{font-size:11px!important;font-weight:700!important;letter-spacing:.7px!important;text-transform:uppercase!important;color:var(--gv-acc)!important;border-bottom:1px solid var(--gv-line-soft)!important;padding:0 0 9px!important;margin:0 0 15px!important;display:flex;align-items:center;gap:8px;line-height:1.2;}
        .gv-sec-title i,.gv-sec-title span[class*="fa"]{color:var(--gv-acc)!important;font-size:13px;}
        .gv-sec-title .helperTooltip{color:var(--gv-ink2)!important;opacity:.55;margin-left:auto;}

        /* inputs + selects */
        .gvDialog input[type="number"],.gvDialog input[type="text"]:not([name="title"]),.gvDialog select[name="metricType"],.gvDialog input.startDate,.gvDialog input.endDate{border:1px solid var(--gv-line)!important;border-radius:9px!important;padding:9px 12px!important;font-size:14px!important;color:var(--gv-ink)!important;background:#fff!important;box-shadow:none!important;height:auto!important;width:100%!important;}
        .gvDialog input:focus:not([name="title"]),.gvDialog select:focus{border-color:var(--gv-acc)!important;outline:none!important;box-shadow:0 0 0 3px rgba(0,100,122,.09)!important;}

        /* metric bar (the goal-view vibe) */
        .gv-metric-bar{background:var(--gv-soft);border:1px solid var(--gv-line-soft);border-radius:11px;padding:15px 17px;margin:2px 0 16px;}
        .gv-metric-bar .gv-mb-top{display:flex;align-items:baseline;gap:9px;margin-bottom:11px;}
        .gv-metric-bar .gv-mb-now{font-size:30px;font-weight:700;letter-spacing:-.6px;color:var(--gv-acc);line-height:1;}
        .gv-metric-bar .gv-mb-of{font-size:13px;color:var(--gv-ink2);}
        .gv-metric-bar .gv-mb-of b{color:var(--gv-ink);font-weight:700;}
        .gv-track{height:10px;background:#e4ebed;border-radius:6px;overflow:hidden;}
        .gv-fill{height:100%;border-radius:6px;background:linear-gradient(90deg,var(--gv-acc),var(--gv-acc2));}
        .gv-scale{display:flex;justify-content:space-between;font-size:11px;color:var(--gv-ink2);font-weight:500;margin-top:8px;}
        .gv-scale b{color:var(--gv-ink);font-weight:700;}

        /* value + date rows */
        .gv-values{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;}
        .gv-values label{font-size:11px;margin-bottom:5px;}
        .gv-reports{margin-top:14px;max-width:340px;}
        .gv-dates{display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:420px;}

        /* actions */
        .gv-actions{display:flex;align-items:center;gap:10px;margin-top:26px;padding-top:18px;border-top:1px solid var(--gv-line-soft);}
        .gv-actions .gv-delete{margin-left:auto;}

        @media (max-width:620px){.gv-head{flex-direction:column;gap:10px;}.gv-head .gv-status{width:100%;}.gv-values{grid-template-columns:1fr 1fr;}}
    </style>

    <div class="gvDialog" style="width:640px">

        <h1><i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] }}"></i>
            {{ $canvasTypes[$canvasItem['box']]['title'] }}</h1>

        <form class="formModal" method="post" action="{{ BASE_URL . "/goalcanvas/editCanvasItem/$id" }}">

            <input type="hidden" value="{{ $currentCanvas }}" name="canvasId">
            <input type="hidden" value="{{ $canvasItem['box'] }}" name="box" id="box">
            <input type="hidden" value="{{ $id }}" name="itemId" id="itemId">
            <input type="hidden" name="changeItem" value="1">

            {{-- ── Objective + status ── --}}
            <div class="gv-head">
                <div class="gv-obj">
                    <label class="gv-eyebrow">{{ __('label.what_is_your_goal') }}</label>
                    <x-global::forms.text-input name="title" value="{{ $canvasItem['title'] }}" placeholder="{{ __('label.what_is_your_goal') }}" style="width:100%" />
                </div>
                <div class="gv-status">
                    <label class="gv-eyebrow">{{ __('label.status') }}</label>
                    @if (!empty($statusLabels))
                        <select name="status" id="statusCanvas"></select>
                    @else
                        <input type="hidden" name="status" value="{{ $canvasItem['status'] ?? array_key_first($hiddenStatusLabels) }}" />
                    @endif
                </div>
            </div>

            {{-- ── Metric ── --}}
            <div class="gv-section">
                <h4 class="gv-sec-title"><i class="fa-solid fa-ranking-star"></i> {{ __('Metrics') }}</h4>

                @dispatchEvent('beforeMeasureGoalContainer', $canvasItem)
                <div id="measureGoalContainer">
                    <label>{{ __('text.what_metric_will_you_be_using') }}</label>
                    <x-global::forms.text-input name="description" value="{{ $canvasItem['description'] }}" style="width:100%" />
                </div>

                @if ($mGoal != $mStart)
                    <div class="gv-metric-bar" aria-hidden="true">
                        <div class="gv-mb-top">
                            <span class="gv-mb-now">{{ $fmtM($mCur) }}</span>
                            <span class="gv-mb-of">{{ __('label.goal_value') }}: <b>{{ $fmtM($mGoal) }}</b></span>
                        </div>
                        <div class="gv-track"><div class="gv-fill" style="width:{{ $mPct }}%"></div></div>
                        <div class="gv-scale"><span>{{ __('label.starting_value') }} <b>{{ $fmtM($mStart) }}</b></span><span>{{ __('label.goal_value') }} <b>{{ $fmtM($mGoal) }}</b></span></div>
                    </div>
                @endif

                <div class="gv-values">
                    <div>
                        <label>{{ __('label.starting_value') }}</label>
                        <x-global::forms.text-input type="number" step="0.01" name="startValue" value="{{ $canvasItem['startValue'] }}" style="width:100%" />
                    </div>
                    <div>
                        @php $currentValueIsComputed = $canvasItem['setting'] == 'linkAndReport'; @endphp
                        <label>{{ __('label.current_value') }}</label>
                        <x-global::forms.text-input type="number" step="0.01" name="currentValue" id="currentValueField"
                            value="{{ $canvasItem['currentValue'] }}"
                            :readonly="$currentValueIsComputed"
                            :data-tippy-content="$currentValueIsComputed ? __('text.current_value_calculated_from_children') : null"
                            style="width:100%" />
                    </div>
                    <div>
                        <label>{{ __('label.goal_value') }}</label>
                        <x-global::forms.text-input type="number" step="0.01" name="endValue" value="{{ $canvasItem['endValue'] }}" style="width:100%" />
                    </div>
                    <div>
                        <label>{{ __('label.type') }}</label>
                        <select name="metricType">
                            <option value="number" @if ($mType == 'number') selected @endif>{{ __('label.number') }}</option>
                            <option value="percent" @if ($mType == 'percent') selected @endif>{{ __('label.percent') }}</option>
                            <option value="currency" @if ($mType == 'currency') selected @endif>{{ __('language.currency') }}</option>
                        </select>
                    </div>
                </div>

                @if (!empty($relatesLabels))
                    <div class="gv-reports">
                        <label>{{ __('label.relates') }}</label>
                        <select name="relates" id="relatesCanvas"></select>
                    </div>
                @else
                    <input type="hidden" name="relates" value="{{ $canvasItem['relates'] ?? array_key_first($hiddenRelatesLabels) }}">
                @endif
            </div>

            {{-- ── Timeline ── --}}
            <div class="gv-section">
                <h4 class="gv-sec-title"><i class="fa-solid fa-calendar"></i> {{ __('label.dates') }}</h4>
                <div class="gv-dates">
                    <div>
                        <label>{{ __('label.start_date') }}</label>
                        <input type="text" autocomplete="off" value="{{ format($canvasItem['startDate'])->date() }}" name="startDate" class="startDate"/>
                    </div>
                    <div>
                        <label>{{ __('label.end_date') }}</label>
                        <input type="text" autocomplete="off" value="{{ format($canvasItem['endDate'])->date() }}" name="endDate" class="endDate"/>
                    </div>
                </div>
            </div>

            {{-- ── Milestones (full width) ── --}}
            @if ($id !== '')
                <div class="gv-section">
                    <h4 class="gv-sec-title"><span class="fa fa-flag-checkered" aria-hidden="true"></span> {{ __("headlines.milestones") }} <i class="fa fa-question-circle-o helperTooltip" aria-hidden="true" data-tippy-content="{{ __("tooltip.link_milestones_tooltip") }}"></i></h4>

                    {{-- Status summary (labels via goalcanvas.summary_* language keys). --}}
                    @if (($milestoneSummary['total'] ?? 0) > 0)
                        <div style="font-size:12px;opacity:.75;margin-bottom:10px;">
                            <strong>{{ $milestoneSummary['total'] }}</strong> {{ __("goalcanvas.summary_milestones") }}
                            @if ($milestoneSummary['inProgress'] > 0)&middot; {{ $milestoneSummary['inProgress'] }} {{ __("goalcanvas.summary_in_progress") }} @endif
                            @if ($milestoneSummary['notStarted'] > 0)&middot; {{ $milestoneSummary['notStarted'] }} {{ __("goalcanvas.summary_not_started") }} @endif
                            @if ($milestoneSummary['done'] > 0)&middot; {{ $milestoneSummary['done'] }} {{ __("goalcanvas.summary_done") }} @endif
                        </div>
                    @endif

                    {{-- Milestones scroll horizontally (by design): prominent scrollbar +
                         ">" button + hint when they overflow. Each chip links to its milestone. --}}
                    @if (count($goalMilestones) > 0)
                        <style>
                            .goalMsWrap{position:relative;}
                            .goalMsRow{display:flex;gap:8px;overflow-x:auto;overflow-y:hidden;padding-bottom:9px;scroll-snap-type:x proximity;-webkit-overflow-scrolling:touch;}
                            .goalMsRow::-webkit-scrollbar{height:10px;}
                            .goalMsRow::-webkit-scrollbar-thumb{background:#9aa7ad;border-radius:10px;border:2px solid transparent;background-clip:padding-box;}
                            .goalMsRow::-webkit-scrollbar-thumb:hover{background:#7d8b92;}
                            .goalMsRow::-webkit-scrollbar-track{background:var(--secondary-background,#eef1f2);border-radius:10px;}
                            .goalMsRow > .goalMsChip{scroll-snap-align:start;transition:border-color .12s;}
                            .goalMsChip:hover{border-color:var(--primary-color,#004666)!important;}
                            .goalMsChip .msLink{text-decoration:none;color:inherit;cursor:pointer;display:flex;align-items:center;min-width:0;flex:1;}
                            .goalMsChip .msLink:hover .msName{color:var(--primary-color,#004666);}
                            .goalMsNext{position:absolute;right:-3px;top:5px;width:29px;height:29px;border-radius:50%;border:1px solid var(--main-border-color,#e4e7ec);background:var(--primary-background,#fff);color:var(--primary-color,#004666);display:flex;align-items:center;justify-content:center;font-size:19px;line-height:1;cursor:pointer;box-shadow:0 2px 8px rgba(20,40,50,.16);z-index:5;}
                            .goalMsNext:hover{background:var(--secondary-background,#f2f4f7);}
                            .goalMsHint{font-size:11px;color:var(--tertiary-color,#8b9aa1);margin:-2px 0 12px;display:flex;align-items:center;gap:5px;}
                        </style>
                        <div class="goalMsWrap">
                            <div class="goalMsRow">
                                @foreach ($goalMilestones as $ms)
                                    <div class="goalMsChip" style="position:relative;flex:0 0 auto;min-width:150px;max-width:220px;height:42px;border-radius:9px;border:1px solid var(--main-border-color,#e4e7ec);background:var(--secondary-background,#f2f4f7);overflow:hidden;display:flex;align-items:center;padding:0 10px;">
                                        <span style="position:absolute;left:0;top:0;bottom:0;width:{{ (int) $ms['percentDone'] }}%;background:{{ $ms['color'] }};opacity:.18;border-right:2px solid {{ $ms['color'] }};"></span>
                                        <a href="#/tickets/editMilestone/{{ (int) $ms['id'] }}" class="msLink" style="position:relative;z-index:1;" title="{{ __('links.edit_milestone') }}: {{ $ms['headline'] }}">
                                            <span class="msName" style="flex:1;min-width:0;font-size:12.5px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $ms['headline'] }}</span>
                                            <span style="flex:none;font-size:11px;font-weight:600;opacity:.7;margin-left:6px;">{{ (int) $ms['percentDone'] }}%</span>
                                        </a>
                                        @if ($login::userIsAtLeast($roles::$editor))
                                            <button type="button"
                                                    hx-post="{{ BASE_URL }}/goalcanvas/editCanvasItem/{{ $id }}"
                                                    hx-vals='{"removeMilestone": {{ (int) $ms['id'] }}}'
                                                    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                                    hx-target="closest .goalMsChip"
                                                    hx-swap="delete"
                                                    class="delete"
                                                    style="position:relative;z-index:1;margin-left:8px;opacity:.6;background:transparent;border:none;cursor:pointer;padding:0;flex:none;"
                                                    aria-label="{{ __("links.remove") }}: {{ $ms['headline'] }}" title="{{ __("links.remove") }}"><i class="fa fa-close" aria-hidden="true"></i></button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @if (count($goalMilestones) > 2)
                                <button type="button" class="goalMsNext" onclick="this.parentElement.querySelector('.goalMsRow').scrollBy({left:210,behavior:'smooth'});" aria-label="Scroll to see more milestones" title="Scroll to see more"><i class="fa fa-angle-right" aria-hidden="true"></i></button>
                            @endif
                        </div>
                        @if (count($goalMilestones) > 2)
                            <div class="goalMsHint"><i class="fa fa-arrows-left-right" aria-hidden="true"></i> {{ sprintf(__('goalcanvas.scroll_all_milestones'), count($goalMilestones)) }}</div>
                        @endif
                    @endif

                    {{-- Add a milestone (new or existing) — appends; leaves other links intact. --}}
                    @if ($login::userIsAtLeast($roles::$editor))
                        <div class="row" id="milestoneSelectors">
                            <div class="col-md-12">
                                <a href="javascript:void(0);" onclick="leantime.goalCanvasController.toggleMilestoneSelectors('new');"><i class="fa fa-plus"></i> {{ __("links.create_link_milestone") }}</a>
                                @if (count($milestones) > 0)
                                    | <a href="javascript:void(0);" onclick="leantime.goalCanvasController.toggleMilestoneSelectors('existing');">{{ __("links.link_existing_milestone") }}</a>
                                @endif
                            </div>
                        </div>
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

            {{-- ── Actions ── --}}
            @if ($login::userIsAtLeast($roles::$editor))
                <div class="gv-actions">
                    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" id="primaryCanvasSubmitButton" />
                    <x-global::forms.button inputType="submit" contentRole="secondary" id="saveAndClose" value="closeModal"
                        onclick="leantime.goalCanvasController.setCloseModal();">{{ __('buttons.save_and_close') }}</x-global::forms.button>
                    @if ($id != '')
                        <x-global::forms.button tag="a" link="{{ BASE_URL }}/goalcanvas/delCanvasItem/{{ $id }}" class="formModal delete gv-delete" state="danger" variant="outline">
                            <i class='fa fa-trash-can'></i> {{ __('links.delete') }}
                        </x-global::forms.button>
                    @endif
                </div>
            @endif

            {{-- ── Discussion ── --}}
            @if ($id !== '')
                <div class="gv-section">
                    <input type="hidden" name="comment" value="1" />
                    <h4 class="gv-sec-title"><span class="fa fa-comments"></span> {{ __('subtitles.discussion') }}</h4>
                    @include('comments::submodules.generalComment', ['formUrl' => '/goalcanvas/editCanvasItem/' . $id])
                </div>
            @endif

        </form>

    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {

            leantime.dateController.initDateRangePicker(".startDate", ".endDate");

            @if (!empty($statusLabels))
            new SlimSelect({
                select: '#statusCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                        @foreach ($statusLabels as $key => $data)
                        @if ($data['active'])
                    {
                        innerHTML: '<i class="fas fa-fw {{ $data['icon'] }}"></i>&nbsp;{{ $data['title'] }}',
                        text: "{{ $data['title'] }}",
                        value: "{{ $key }}",
                        selected: {{ $canvasItem['status'] == $key ? 'true' : 'false' }}
                    },
                    @endif
                    @endforeach
                ]
            });
            @endif

            @if (!empty($relatesLabels))
            new SlimSelect({
                select: '#relatesCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                        @foreach ($relatesLabels as $key => $data)
                        @if ($data['active'])
                    {
                        innerHTML: '<i class="fas fa-fw {{ $data['icon'] }}"></i>&nbsp;{{ $data['title'] }}',
                        text: "{{ $data['title'] }}",
                        value: "{{ $key }}",
                        selected: {{ $canvasItem['relates'] == $key ? 'true' : 'false' }}
                    },
                    @endif
                    @endforeach
                ]
            });
            @endif

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

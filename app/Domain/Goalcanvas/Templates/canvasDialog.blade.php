@extends($layout)
@section('content')
    @php
        $hiddenRelatesLabels = $relatesLabels ?? [];
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
        /* ── Goal dialog v1 skin — restyles the look only; every input/select/
              date-picker/HTMX hook keeps its name, id and class so the form,
              chosen dropdowns and save flow are untouched. ── */
        .goalDialogV1{width:940px;max-width:100%;padding:6px 12px 10px;
            --gv-acc:#00647a;--gv-acc2:#0e93a8;--gv-line:#e3e9eb;--gv-line-soft:#eef2f3;--gv-ink:#18272e;--gv-ink2:#586970;}
        .goalDialogV1 h1{font-size:12px;font-weight:700;letter-spacing:.9px;text-transform:uppercase;color:var(--gv-ink2);margin:2px 0 22px;display:flex;align-items:center;gap:9px;}
        .goalDialogV1 h1 i{color:var(--gv-acc);font-size:15px;}
        .goalDialogV1 label{font-size:12px;font-weight:600;color:var(--gv-ink2);display:block;margin:0 0 6px;}
        /* "What is your goal?" becomes a small eyebrow above a headline field */
        .goalDialogV1 .col-md-8 > label:first-of-type{font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--gv-acc);margin-bottom:9px;}
        .goalDialogV1 input[name="title"]{font-size:23px!important;font-weight:650!important;line-height:1.25!important;color:var(--gv-ink)!important;border:none!important;border-bottom:2px solid var(--gv-line)!important;border-radius:0!important;padding:4px 2px 9px!important;background:transparent!important;box-shadow:none!important;height:auto!important;}
        .goalDialogV1 input[name="title"]:focus{border-bottom-color:var(--gv-acc)!important;outline:none!important;box-shadow:none!important;}
        .goalDialogV1 input[name="title"]::placeholder{color:#aab6bb;font-weight:500;}
        /* section headers → ruled uppercase eyebrows */
        .goalDialogV1 h4.widgettitle{font-size:11px!important;font-weight:700!important;letter-spacing:.7px!important;text-transform:uppercase!important;color:var(--gv-acc)!important;border-bottom:1px solid var(--gv-line-soft)!important;padding:0 0 9px!important;margin:30px 0 15px!important;display:flex;align-items:center;gap:8px;line-height:1.2;}
        .goalDialogV1 h4.widgettitle i,.goalDialogV1 h4.widgettitle span[class*="fa"]{color:var(--gv-acc)!important;font-size:13px;}
        .goalDialogV1 h4.widgettitle .helperTooltip{color:var(--gv-ink2)!important;opacity:.55;margin-left:auto;}
        /* soft, consistent inputs + focus ring */
        .goalDialogV1 input[type="number"],.goalDialogV1 select[name="metricType"],.goalDialogV1 input.startDate,.goalDialogV1 input.endDate,.goalDialogV1 #measureGoalContainer input[type="text"]{border:1px solid var(--gv-line)!important;border-radius:9px!important;padding:9px 12px!important;font-size:14px!important;color:var(--gv-ink)!important;background:#fff!important;box-shadow:none!important;height:auto!important;}
        .goalDialogV1 input:focus:not([name="title"]),.goalDialogV1 select:focus{border-color:var(--gv-acc)!important;outline:none!important;box-shadow:0 0 0 3px rgba(0,100,122,.09)!important;}
        /* two-column balance + a divider */
        .goalDialogV1 .col-md-8{padding-right:38px;}
        .goalDialogV1 .col-md-4{border-left:1px solid var(--gv-line-soft);padding-left:32px;}
        .goalDialogV1 #measureGoalContainer{margin-bottom:16px;}
        .goalDialogV1 input.startDate,.goalDialogV1 input.endDate{width:100%!important;max-width:230px;margin-bottom:6px;}
    </style>
    <div class="goalDialogV1" style="width:1000px">

        <h1><i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] }}"></i>
            {{ $canvasTypes[$canvasItem['box']]['title'] }}</h1>

        <form class="formModal" method="post" action="{{ BASE_URL . "/goalcanvas/editCanvasItem/$id" }}">

            <input type="hidden" value="{{ $currentCanvas }}" name="canvasId">
            <input type="hidden" value="{{ $canvasItem['box'] }}" name="box" id="box">
            <input type="hidden" value="{{ $id }}" name="itemId" id="itemId">
            <input type="hidden" name="changeItem" value="1">

            <div class="row">
                <div class="col-md-8">
                    <label>{{ __('label.what_is_your_goal') }}</label>
                    <x-global::forms.text-input name="title" value="{{ $canvasItem['title'] }}" style="width:100%" /><br>

                    @if (!empty($relatesLabels))
                        <label>{{ __('label.relates') }}</label>
                        <select name="relates" style="width: 50%" id="relatesCanvas">
                        </select><br>
                    @else
                        <input type="hidden" name="relates"
                            value="{{ $canvasItem['relates'] ?? array_key_first($hiddenRelatesLabels) }}">
                    @endif
                    <br>
                    <h4 class="widgettitle title-light" style="margin-bottom:0px;"><i class="fa-solid fa-ranking-star"></i>
                        {{ __('Metrics') }}</h4>

                    @dispatchEvent('beforeMeasureGoalContainer', $canvasItem)
                    <div id="measureGoalContainer">
                        <label>{{ __('text.what_metric_will_you_be_using') }}</label>
                        <x-global::forms.text-input name="description" value="{{ $canvasItem['description'] }}"
                            style="width:100%" /><br>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <label>{{ __('label.starting_value') }}</label>
                            <x-global::forms.text-input type="number" step="0.01" name="startValue" value="{{ $canvasItem['startValue'] }}"
                                style="width:105px" />
                        </div>
                        <div class="col-md-3">
                            @php $currentValueIsComputed = $canvasItem['setting'] == 'linkAndReport'; @endphp
                            <label>{{ __('label.current_value') }}</label>
                            <x-global::forms.text-input type="number" step="0.01" name="currentValue" id="currentValueField"
                                value="{{ $canvasItem['currentValue'] }}"
                                :readonly="$currentValueIsComputed"
                                :data-tippy-content="$currentValueIsComputed ? __('text.current_value_calculated_from_children') : null"
                                style="width:105px" />
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('label.goal_value') }}</label>
                            <x-global::forms.text-input type="number" step="0.01" name="endValue" value="{{ $canvasItem['endValue'] }}"
                                style="width:105px" />
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('label.type') }}</label>
                            <select name="metricType">
                                <option value="number" @if ($canvasItem['metricType'] == 'number') selected @endif>
                                    {{ __('label.number') }}</option>
                                <option value="percent" @if ($canvasItem['metricType'] == 'percent') selected @endif>
                                    {{ __('label.percent') }}</option>
                                <option value="currency" @if ($canvasItem['metricType'] == 'currency') selected @endif>
                                    {{ __('language.currency') }}</option>
                            </select>
                        </div>
                    </div>

                    <br>
                    @if ($login::userIsAtLeast($roles::$editor))
                        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" id="primaryCanvasSubmitButton" />
                        <x-global::forms.button inputType="submit" contentRole="secondary" id="saveAndClose" value="closeModal"
                            onclick="leantime.goalCanvasController.setCloseModal();">{{ __('buttons.save_and_close') }}</x-global::forms.button>
                    @endif

                    @if ($id !== '')
                        <br /><br /><br />
                        <input type="hidden" name="comment" value="1" />
                        <h4 class="widgettitle title-light"><span
                                class="fa fa-comments"></span>{{ __('subtitles.discussion') }}</h4>
                        @include('comments::submodules.generalComment', ['formUrl' => '/goalcanvas/editCanvasItem/' . $id])
                    @endif
                </div>

                <div class="col-md-4">
                    @if (!empty($statusLabels))
                        <label>{{ __("label.status") }}</label>
                        <select name="status" style="width: 50%" id="statusCanvas">
                        </select><br /><br />
                    @else
                        <input type="hidden" name="status" value="{{ $canvasItem['status'] ?? array_key_first($hiddenStatusLabels) }}" />
                    @endif

                    <h4 class="widgettitle title-light" style="margin-bottom:0px;"><i class="fa-solid fa-calendar"></i> {{ __('label.dates') }}</h4>

                    <label>{{ __('label.start_date') }}</label>
                    <input type="text" autocomplete="off" value="{{ format($canvasItem['startDate'])->date() }}" name="startDate" class="startDate"/>

                    <label>{{ __('label.end_date') }}</label>
                    <input type="text" autocomplete="off" value="{{ format($canvasItem['endDate'])->date() }}" name="endDate" class="endDate"/>

                    @if ($id !== '')
                        <br /><br />
                        <h4 class="widgettitle title-light"><span class="fa fa-flag-checkered" aria-hidden="true"></span> {{ __("headlines.milestones") }} <i class="fa fa-question-circle-o helperTooltip" aria-hidden="true" data-tippy-content="{{ __("tooltip.link_milestones_tooltip") }}"></i></h4>

                        {{-- Status summary (labels via goalcanvas.summary_* language keys). --}}
                        @if (($milestoneSummary['total'] ?? 0) > 0)
                            <div style="font-size:12px;opacity:.75;margin-bottom:10px;">
                                <strong>{{ $milestoneSummary['total'] }}</strong> {{ __("goalcanvas.summary_milestones") }}
                                @if ($milestoneSummary['inProgress'] > 0)&middot; {{ $milestoneSummary['inProgress'] }} {{ __("goalcanvas.summary_in_progress") }} @endif
                                @if ($milestoneSummary['notStarted'] > 0)&middot; {{ $milestoneSummary['notStarted'] }} {{ __("goalcanvas.summary_not_started") }} @endif
                                @if ($milestoneSummary['done'] > 0)&middot; {{ $milestoneSummary['done'] }} {{ __("goalcanvas.summary_done") }} @endif
                            </div>
                        @endif

                        {{-- Linked-milestone chips, sorted in-progress -> not-started -> done. The fill is the milestone's OWN color growing with its progress (deliberately not a status color). --}}
                        @if (count($goalMilestones) > 0)
                            {{-- Milestones scroll horizontally (by design). Clarity cues: a
                                 prominent always-visible scrollbar (Chrome renders a persistent
                                 bar once ::-webkit-scrollbar is styled) + a ">" scroll button and
                                 a text hint when they overflow. Each chip links to its milestone. --}}
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

                        {{-- Add a milestone (new or existing) — appends; leaves the goal's other links intact. --}}
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
                    @endif
                </div>
            </div>

            @if ($id != '')
                <x-global::forms.button tag="a" link="{{ BASE_URL }}/goalcanvas/delCanvasItem/{{ $id }}" class="formModal delete right" state="danger" variant="outline">
                    <i class='fa fa-trash-can'></i> {{ __('links.delete') }}
                </x-global::forms.button>
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

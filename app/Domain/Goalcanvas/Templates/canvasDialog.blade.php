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

    <div style="width:1000px">

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

                        {{-- Status summary. TODO(refine): move the summary labels to language keys. --}}
                        @if (($milestoneSummary['total'] ?? 0) > 0)
                            <div style="font-size:12px;opacity:.75;margin-bottom:10px;">
                                <strong>{{ $milestoneSummary['total'] }}</strong> milestones
                                @if ($milestoneSummary['inProgress'] > 0)&middot; {{ $milestoneSummary['inProgress'] }} in progress @endif
                                @if ($milestoneSummary['notStarted'] > 0)&middot; {{ $milestoneSummary['notStarted'] }} not started @endif
                                @if ($milestoneSummary['done'] > 0)&middot; {{ $milestoneSummary['done'] }} done @endif
                            </div>
                        @endif

                        {{-- Linked-milestone chips, sorted in-progress -> not-started -> done. The fill is the milestone's OWN color growing with its progress (deliberately not a status color). --}}
                        @if (count($goalMilestones) > 0)
                            <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:6px;margin-bottom:12px;">
                                @foreach ($goalMilestones as $ms)
                                    <div style="position:relative;flex:0 0 auto;min-width:150px;max-width:220px;height:42px;border-radius:9px;border:1px solid var(--tertiary-color,#e4e7ec);background:var(--secondary-background,#f2f4f7);overflow:hidden;display:flex;align-items:center;padding:0 10px;">
                                        <span style="position:absolute;left:0;top:0;bottom:0;width:{{ (int) $ms['percentDone'] }}%;background:{{ $ms['color'] }};opacity:.18;border-right:2px solid {{ $ms['color'] }};"></span>
                                        <span style="position:relative;z-index:1;flex:1;font-size:12.5px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $ms['headline'] }}</span>
                                        <span style="position:relative;z-index:1;font-size:11px;font-weight:600;opacity:.7;margin-left:6px;">{{ (int) $ms['percentDone'] }}%</span>
                                        @if ($login::userIsAtLeast($roles::$editor))
                                            <a href="{{ BASE_URL }}/goalcanvas/editCanvasItem/{{ $id }}?removeMilestone={{ $ms['id'] }}" class="goalCanvasModal delete formModal" style="position:relative;z-index:1;margin-left:8px;opacity:.6;" aria-label="{{ __("links.remove") }}: {{ $ms['headline'] }}" title="{{ __("links.remove") }}"><i class="fa fa-close" aria-hidden="true"></i></a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
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

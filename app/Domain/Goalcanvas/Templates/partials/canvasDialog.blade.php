<x-global::content.modal.modal-buttons />

<div style="width:1000px">

     <x-global::content.modal.header>
        <i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] }}"></i>
        {{ $canvasTypes[$canvasItem['box']]['title'] }}        
    </x-global::content.modal.header>

    <x-global::content.modal.form action="{{ BASE_URL }}/goalcanvas/editCanvasItem/{{ $id }}">


        <input type="hidden" value="{{ $currentCanvas }}" name="canvasId">
        <input type="hidden" value="{{ $canvasItem['box'] }}" name="box" id="box">
        <input type="hidden" value="{{ $id }}" name="itemId" id="itemId">
        <input type="hidden" name="milestoneId" value="{{ $canvasItem['milestoneId'] ?? '' }}">
        <input type="hidden" name="changeItem" value="1">

        <div class="row">
            <div class="col-md-8">
                <x-global::forms.text-input type="text" name="title" value="{{ $canvasItem['title'] }}"
                    labelText="{{ __('label.what_is_your_goal') }}" variant="title" />
                <br />

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
                    <x-global::forms.text-input type="text" name="description"
                        value="{{ $canvasItem['description'] }}"
                        labelText="{{ __('text.what_metric_will_you_be_using') }}" variant="title" />

                </div>

                <div class="row">
                    <div class="col-md-3">
                        <x-global::forms.text-input type="number" step="0.01" name="startValue"
                            value="{{ $canvasItem['startValue'] }}" labelText="{{ __('label.starting_value') }}"
                            variant="compact"
                            class="w-20" />

                    </div>
                    <div class="col-md-3">
                        <x-global::forms.text-input type="number" step="0.01" name="currentValue"
                            id="currentValueField" value="{{ $canvasItem['currentValue'] }}"
                            labelText="{{ __('label.current_value') }}" variant="compact"
                            class="w-20"
{{--                            @if ($canvasItem['setting'] == 'linkAndReport')--}}
{{--                                dataTippyContent="Current value calculated from child goals" @endif--}}
                        />

                    </div>
                    <div class="col-md-3">
                        <x-global::forms.text-input type="number" step="0.01" name="endValue"
                            value="{{ $canvasItem['endValue'] }}" labelText="{{ __('label.goal_value') }}"
                            class="w-20"
                            variant="compact" />

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
                    <x-global::forms.button type="submit" id="saveBtn">
                        {{ __('buttons.save') }}
                    </x-global::forms.button>

                    <x-global::forms.button id="saveAndClose" onclick="leantime.goalCanvasController.setCloseModal();">
                        {{ __('buttons.save_and_close') }}
                    </x-global::forms.button>
                @endif

            </div>

            <div class="col-md-4">
                @if (!empty($statusLabels))
                    <label>{{ __('label.status') }}</label>
                    <select name="status" style="width: 50%" id="statusCanvas">
                    </select><br /><br />
                @else
                    <input type="hidden" name="status"
                        value="{{ $canvasItem['status'] ?? array_key_first($hiddenStatusLabels) }}" />
                @endif

                <h4 class="widgettitle title-light" style="margin-bottom:0px;"><i class="fa-solid fa-calendar"></i>
                    {{ __('label.dates') }}</h4>

                <label>{{ __('label.start_date') }}</label>
                <input type="text" autocomplete="off" value="{{ format($canvasItem['startDate'])->date() }}"
                    name="startDate" class="startDate" />

                <label>{{ __('label.end_date') }}</label>
                <input type="text" autocomplete="off" value="{{ format($canvasItem['endDate'])->date() }}"
                    name="endDate" class="endDate" />

                @if ($id !== '')
                    <br /><br />
                    <h4 class="widgettitle title-light"><span class="fa fa-link"></span>
                        {{ __('headlines.linked_milestone') }} <i class="fa fa-question-circle-o helperTooltip"
                            data-tippy-content="{{ __('tooltip.link_milestones_tooltip') }}"></i></h4>

                    @if ($canvasItem['milestoneId'] == '')
                        <center>
                            <h4>{{ __('headlines.no_milestone_link') }}</h4>
                            <div class="row" id="milestoneSelectors">
                                @if ($login::userIsAtLeast($roles::$editor))
                                    <div class="col-md-12">
                                        <a href="javascript:void(0);"
                                            onclick="leantime.canvasController.toggleMilestoneSelectors('new');">{{ __('links.create_link_milestone') }}</a>
                                        @if (count($tpl->get('milestones')) > 0)
                                            | <a href="javascript:void(0);"
                                                onclick="leantime.canvasController.toggleMilestoneSelectors('existing');">{{ __('links.link_existing_milestone') }}</a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="row" id="newMilestone" style="display:none;">
                                <div class="col-md-12">
                                    <x-global::forms.text-input type="text" name="newMilestone" />
                                    <br />
                                    <input type="hidden" name="type" value="milestone" />
                                    <input type="hidden" name="goalcanvasitemid" value="{{ $id }}" />
                                    <x-global::forms.button content-role="primary" type="button"
                                        onclick="jQuery('#primaryCanvasSubmitButton').click()">
                                        {{ __('buttons.save') }}
                                    </x-global::forms.button>

                                    <x-global::forms.button content-role="secondary" type="button"
                                        onclick="leantime.canvasController.toggleMilestoneSelectors('hide')">
                                        {{ __('buttons.cancel') }}
                                    </x-global::forms.button>

                                </div>
                            </div>

                            <div class="row" id="existingMilestone" style="display:none;">
                                <div class="col-md-12">
                                    <select data-placeholder="{{ __('input.placeholders.filter_by_milestone') }}"
                                        name="existingMilestone" class="user-select">
                                        <option value=""></option>
                                        @foreach ($tpl->get('milestones') as $milestoneRow)
                                            <option value="{{ $milestoneRow->id }}"
                                                {{ isset($searchCriteria['milestone']) && $searchCriteria['milestone'] == $milestoneRow->id ? 'selected' : '' }}>
                                                {{ $milestoneRow->headline }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="type" value="milestone" />
                                    <input type="hidden" name="goalcanvasitemid" value="{{ $id }}" />
                                    <x-global::forms.button content-role="primary" type="button"
                                        onclick="jQuery('#primaryCanvasSubmitButton').click()">
                                        {{ __('buttons.save') }}
                                    </x-global::forms.button>

                                    <x-global::forms.button content-role="secondary" type="button"
                                        onclick="leantime.canvasController.toggleMilestoneSelectors('hide')">
                                        {{ __('buttons.cancel') }}
                                    </x-global::forms.button>

                                </div>
                            </div>
                        </center>
                    @else
                        <div hx-trigger="load" hx-indicator=".htmx-indicator"
                            hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $canvasItem['milestoneId'] }}">
                            <div class="htmx-indicator">
                                {{ __('label.loading_milestone') }}
                            </div>
                        </div>
                        <a href="{{ BASE_URL }}/goalcanvas/editCanvasItem/{{ $id }}?removeMilestone={{ $canvasItem['milestoneId'] }}"
                            class="goalCanvasModal delete formModal"><i class="fa fa-close"></i>
                            {{ __('links.remove') }}</a>
                    @endif
                @endif
            </div>
        </div>

        @if ($id != '')
            <a href="{{ BASE_URL . "/goalcanvas/delCanvasItem/$id" }}" class="formModal delete right">
                <i class='fa fa-trash-can'></i> {{ __('links.delete') }}
            </a>
        @endif

    </x-global::content.modal.form>


    @if ($id !== '')
        <br /><br /><br />
        <input type="hidden" name="comment" value="1" />
        <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{{ __('subtitles.discussion') }}</h4>
        @include('comments::includes.generalComment', [
            'formUrl' => BASE_URL . '/goalcanvas/editCanvasItem/' . $id,
        ])
    @endif

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

        leantime.editorController.initSimpleEditor();



        @if ($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
        @endif

    });
</script>

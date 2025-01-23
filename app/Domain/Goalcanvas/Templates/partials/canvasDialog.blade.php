<x-global::content.modal.modal-buttons>
    @if ($canvasItem['id'] !== '')
        <x-global::content.modal.header-button variant="delete"
            href="{{ BASE_URL . '/goalcanvas/delCanvasItem/' . $canvasItem['id'] }}" />
    @endif
</x-global::content.modal.modal-buttons>

<div class="w-full">

    <div>

        <input type="hidden" value="{{ $currentCanvas }}" name="canvasId">
        <input type="hidden" value="{{ $canvasItem['box'] }}" name="box" id="box">
        <input type="hidden" value="{{ $id }}" name="itemId" id="itemId">
        <input type="hidden" name="milestoneId" value="{{ $canvasItem['milestoneId'] ?? '' }}">
        <input type="hidden" name="changeItem" value="1">

        <div class="row">
            <div class="col-md-8">
                <x-global::forms.text-input type="text" name="title" value="{{ $canvasItem['title'] }}"
                    placeholder="{{ __('label.what_is_your_goal') }}" variant="title"
                    hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                    hx-trigger="change" hx-swap="none" />


                @if (!empty($relatesLabels))
                    <x-global::forms.select name="relates" id="relatesCanvas" labelText="{!! __('label.relates') !!}"
                        class="w-1/2"
                        hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                        hx-trigger="change" hx-swap="none">
                        {{-- options were empty --}}
                    </x-global::forms.select>
                    <br>
                @else
                    <input type="hidden" name="relates"
                        value="{{ $canvasItem['relates'] ?? array_key_first($hiddenRelatesLabels) }}">
                @endif
                <h4 class="widgettitle title-light" style="margin-bottom:0px;"><i class="fa-solid fa-ranking-star"></i>
                    {{ __('Metrics') }}</h4>

                @dispatchEvent('beforeMeasureGoalContainer', $canvasItem)
                <div id="measureGoalContainer">
                    <x-global::forms.text-input type="text" name="description"
                        value="{{ $canvasItem['description'] }}"
                        labelText="{{ __('text.what_metric_will_you_be_using') }}" class="w-full"
                        hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                        hx-trigger="change" hx-swap="none" />


                </div>

                <div class="row">
                    <div class="col-md-3">
                        <x-global::forms.text-input type="number" step="0.01" name="startValue"
                            value="{{ $canvasItem['startValue'] }}" labelText="{{ __('label.starting_value') }}"
                            class="w-5"
                            hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                            hx-trigger="change" hx-swap="none" />

                    </div>
                    <div class="col-md-3">
                        <x-global::forms.text-input type="number" step="0.01" name="currentValue"
                            id="currentValueField" value="{{ $canvasItem['currentValue'] }}"
                            labelText="{{ __('label.current_value') }}" class="w-5"
                            hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                            hx-trigger="change" hx-swap="none" />

                    </div>
                    <div class="col-md-3">
                        <x-global::forms.text-input type="number" step="0.01" name="endValue"
                            value="{{ $canvasItem['endValue'] }}" labelText="{{ __('label.goal_value') }}"
                            class="w-5"
                            hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                            hx-trigger="change" hx-swap="none" />

                    </div>
                    <div class="col-md-3">
                        <x-global::forms.select name="metricType" labelText="{!! __('label.type') !!}"
                            hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                            hx-trigger="change" hx-swap="none">
                            <x-global::forms.select.select-option value="number" :selected="$canvasItem['metricType'] == 'number'">
                                {!! __('label.number') !!}
                            </x-global::forms.select.select-option>

                            <x-global::forms.select.select-option value="percent" :selected="$canvasItem['metricType'] == 'percent'">
                                {!! __('label.percent') !!}
                            </x-global::forms.select.select-option>

                            <x-global::forms.select.select-option value="currency" :selected="$canvasItem['metricType'] == 'currency'">
                                {!! __('language.currency') !!}
                            </x-global::forms.select.select-option>
                        </x-global::forms.select>

                    </div>
                </div>

                <br>
                @if ($login::userIsAtLeast($roles::$editor))
                    <x-global::forms.button type="submit" id="saveBtn">
                        {{ __('buttons.save') }}
                    </x-global::forms.button>

                    <x-global::forms.button id="close-canvas" content-role="secondary"
                        onclick="leantime.goalCanvasController.setCloseModal();">
                        {{ __('buttons.cancel') }}
                    </x-global::forms.button>
                @endif
            </div>

            <div class="col-md-4">
                <x-goalcanvas::chips.status-select :statuses="$statusLabels" :goal="(object) $canvasItem" :showLabel="false"
                    dropdown-position="start" />

                <h4 class="widgettitle title-light" style="margin-bottom:0px;"><i class="fa-solid fa-calendar"></i>
                    {{ __('label.dates') }}</h4>

                <div class="flex flex-col gap-2">
                    <x-global::forms.text-input type="date" labelText="{{ __('label.start_date') }}"
                        value="{{ format($canvasItem['startDate'])->date('Y-m-d') }}" name="startDate"
                        class="startDate"
                        hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                        hx-trigger="change" hx-swap="none" />

                    <x-global::forms.text-input type="date" labelText="{{ __('label.end_date') }}"
                        value="{{ format($canvasItem['endDate'])->date('Y-m-d') }}" name="endDate" class="endDate"
                        hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                        hx-trigger="change" hx-swap="none" />
                </div>

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
                                    <x-global::forms.text-input type="text" name="newMilestone"
                                        hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                                        hx-trigger="change" hx-swap="none" />
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
                                    <x-global::forms.select name="existingMilestone" class="user-select"
                                        :placeholder="__('input.placeholders.filter_by_milestone')"
                                        hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $canvasItem['id'] }}"
                                        hx-trigger="change" hx-swap="none">
                                        <x-global::forms.select.select-option value="">
                                            {{-- Empty option for placeholder --}}
                                        </x-global::forms.select.select-option>

                                        @foreach ($tpl->get('milestones') as $milestoneRow)
                                            <x-global::forms.select.select-option value="{{ $milestoneRow->id }}"
                                                :selected="isset($searchCriteria['milestone']) &&
                                                    $searchCriteria['milestone'] == $milestoneRow->id">
                                                {{ $milestoneRow->headline }}
                                            </x-global::forms.select.select-option>
                                        @endforeach
                                    </x-global::forms.select>

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
                    @endif
                @endif
            </div>
        </div>

    </div>


    @if ($id !== '')
        <input type="hidden" name="comment" value="1" />
        <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{{ __('subtitles.discussion') }}</h4>
        <x-comments::list :module="'goalcanvas'" :moduleId="$id" :statusUpdates="'false'" />
    @endif

</div>

<script type="module">
    import "@mix('/js/components/datePickers.module.js')"
    jQuery(document).ready(function() {

        // datePickers.initDateRangePicker(".startDate", ".endDate");

        jQuery('#saveBtn').click(function() {
            jQuery.growl({
                message: "Goal Updated",
                style: "success"
            });
            htmx.find("#modal-wrapper #main-page-modal").close();
        });

        jQuery('#close-canvas').click(function() {
            htmx.find("#modal-wrapper #main-page-modal").close();
        });

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

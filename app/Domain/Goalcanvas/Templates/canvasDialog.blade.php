@extends($layout)
@section('content')
    @php
        $hiddenStatusLabels = $tpl->get('statusLabels');
        $statusLabels = $statusLabels ?? $hiddenStatusLabels;
        $hiddenRelatesLabels = $tpl->get('relatesLabels');
        $relatesLabels = $relatesLabels ?? $hiddenRelatesLabels;
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

        <h1><x-global::elements.icon :name="$canvasTypes[$canvasItem['box']]['icon']" />
            {{ $canvasTypes[$canvasItem['box']]['title'] }}</h1>

        <form class="formModal" method="post" action="{{ BASE_URL . "/goalcanvas/editCanvasItem/$id" }}">

            <input type="hidden" value="{{ $currentCanvas }}" name="canvasId">
            <input type="hidden" value="{{ $canvasItem['box'] }}" name="box" id="box">
            <input type="hidden" value="{{ $id }}" name="itemId" id="itemId">
            <input type="hidden" name="milestoneId" value="{{ $canvasItem['milestoneId'] ?? '' }}">
            <input type="hidden" name="changeItem" value="1">

            <div class="row">
                <div class="col-md-8">
                    <label>{{ __('label.what_is_your_goal') }}</label>
                    <x-globals::forms.input name="title" value="{{ $canvasItem['title'] }}" style="width:100%" /><br>

                    @if (!empty($relatesLabels))
                        <label>{{ __('label.relates') }}</label>
                        <x-globals::forms.select :bare="true" name="relates" style="width: 50%" id="relatesCanvas">
                        </x-globals::forms.select><br>
                    @else
                        <input type="hidden" name="relates"
                            value="{{ $canvasItem['relates'] ?? array_key_first($hiddenRelatesLabels) }}">
                    @endif
                    <br>
                    <h4 class="widgettitle title-light" style="margin-bottom:0px;"><x-global::elements.icon name="military_tech" />
                        {{ __('Metrics') }}</h4>

                    @dispatchEvent('beforeMeasureGoalContainer', $canvasItem)
                    <div id="measureGoalContainer">
                        <label>{{ __('text.what_metric_will_you_be_using') }}</label>
                        <x-globals::forms.input name="description" value="{{ $canvasItem['description'] }}"
                            style="width:100%" /><br>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <label>{{ __('label.starting_value') }}</label>
                            <x-globals::forms.input type="number" step="0.01" name="startValue" value="{{ $canvasItem['startValue'] }}"
                                style="width:105px" />
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('label.current_value') }}</label>
                            <x-globals::forms.input type="number" step="0.01" name="currentValue" id="currentValueField"
                                value="{{ $canvasItem['currentValue'] }}"
                                :readonly="$canvasItem['setting'] == 'linkAndReport'"
                                @if ($canvasItem['setting'] == 'linkAndReport') data-tippy-content="Current value calculated from child goals" @endif
                                style="width:105px" />
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('label.goal_value') }}</label>
                            <x-globals::forms.input type="number" step="0.01" name="endValue" value="{{ $canvasItem['endValue'] }}"
                                style="width:105px" />
                        </div>
                        <div class="col-md-3">
                            <label>{{ __('label.type') }}</label>
                            <x-globals::forms.select name="metricType">
                                <option value="number" @if ($canvasItem['metricType'] == 'number') selected @endif>
                                    {{ __('label.number') }}</option>
                                <option value="percent" @if ($canvasItem['metricType'] == 'percent') selected @endif>
                                    {{ __('label.percent') }}</option>
                                <option value="currency" @if ($canvasItem['metricType'] == 'currency') selected @endif>
                                    {{ __('language.currency') }}</option>
                            </x-globals::forms.select>
                        </div>
                    </div>

                    <br>
                    @if ($login::userIsAtLeast($roles::$editor))
                        <x-globals::forms.button submit type="primary" id="primaryCanvasSubmitButton">{{ __('buttons.save') }}</x-globals::forms.button>
                        <x-globals::forms.button submit type="primary" id="saveAndClose" value="closeModal" onclick="leantime.goalCanvasController.setCloseModal();">{{ __('buttons.save_and_close') }}</x-globals::forms.button>
                    @endif

                    @if ($id !== '')
                        <br /><br /><br />
                        <input type="hidden" name="comment" value="1" />
                        <h4 class="widgettitle title-light"><x-global::elements.icon name="forum" />{{ __('subtitles.discussion') }}</h4>
                        @php
                            $tpl->assign('formUrl', '/goalcanvas/editCanvasItem/' . $id . '');
                            $tpl->displaySubmodule('comments-generalComment');
                        @endphp
                    @endif
                </div>

                <div class="col-md-4">
                    @if (!empty($statusLabels))
                        <label>{{ __("label.status") }}</label>
                        <x-globals::forms.select :bare="true" name="status" style="width: 50%" id="statusCanvas">
                        </x-globals::forms.select><br /><br />
                    @else
                        <input type="hidden" name="status" value="{{ $canvasItem['status'] ?? array_key_first($hiddenStatusLabels) }}" />
                    @endif

                    <h4 class="widgettitle title-light" style="margin-bottom:0px;"><x-global::elements.icon name="calendar_today" /> {{ __('label.dates') }}</h4>

                    <label>{{ __('label.start_date') }}</label>
                    <input type="text" autocomplete="off" value="{{ format($canvasItem['startDate'])->date() }}" name="startDate" class="startDate"/>

                    <label>{{ __('label.end_date') }}</label>
                    <input type="text" autocomplete="off" value="{{ format($canvasItem['endDate'])->date() }}" name="endDate" class="endDate"/>

                    @if ($id !== '')
                        <br /><br />
                        <h4 class="widgettitle title-light"><x-global::elements.icon name="link" /> {{ __("headlines.linked_milestone") }} <x-global::elements.icon name="help_outline" class="helperTooltip" data-tippy-content="{{ __("tooltip.link_milestones_tooltip") }}" /></h4>

                        @if ($canvasItem['milestoneId'] == '')
                            <center>
                                <h4>{{ __("headlines.no_milestone_link") }}</h4>
                                <div id="milestoneSelectors">
                                    @if ($login::userIsAtLeast($roles::$editor))
                                        <a href="javascript:void(0);" onclick="leantime.goalCanvasController.toggleMilestoneSelectors('new');">{{ __("links.create_link_milestone") }}</a>
                                        @if (count($tpl->get('milestones')) > 0)
                                            | <a href="javascript:void(0);" onclick="leantime.goalCanvasController.toggleMilestoneSelectors('existing');">{{ __("links.link_existing_milestone") }}</a>
                                        @endif
                                    @endif
                                </div>
                                <div id="newMilestone" style="display:none;">
                                    <x-globals::forms.input name="newMilestone" style="width:50%" /><br />
                                    <input type="hidden" name="type" value="milestone" />
                                    <input type="hidden" name="goalcanvasitemid" value="{{ $id }}" />
                                    <x-globals::forms.button tag="button" type="primary" onclick="jQuery('#primaryCanvasSubmitButton').click()">{{ __("buttons.save") }}</x-globals::forms.button>
                                    <x-globals::forms.button tag="button" type="secondary" onclick="leantime.goalCanvasController.toggleMilestoneSelectors('hide')">{{ __("buttons.cancel") }}</x-globals::forms.button>
                                </div>

                                <div id="existingMilestone" style="display:none;">
                                    <x-globals::forms.select :bare="true" data-placeholder="{{ __("input.placeholders.filter_by_milestone") }}" name="existingMilestone" class="user-select">
                                        <option value=""></option>
                                        @foreach ($tpl->get('milestones') as $milestoneRow)
                                            <option value="{{ $milestoneRow->id }}" {{ isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id) ? 'selected' : '' }}>
                                                {{ $milestoneRow->headline }}
                                            </option>
                                        @endforeach
                                    </x-globals::forms.select>
                                    <input type="hidden" name="type" value="milestone" />
                                    <input type="hidden" name="goalcanvasitemid" value="{{ $id }}" />
                                    <x-globals::forms.button tag="button" type="primary" onclick="jQuery('#primaryCanvasSubmitButton').click()">{{ __("buttons.save") }}</x-globals::forms.button>
                                    <x-globals::forms.button tag="button" type="secondary" onclick="leantime.goalCanvasController.toggleMilestoneSelectors('hide')">{{ __("buttons.cancel") }}</x-globals::forms.button>
                                </div>
                            </center>
                        @else
                            <div hx-trigger="load"
                                 hx-indicator=".htmx-indicator"
                                 hx-target="this"
                                 hx-swap="innerHTML"
                                 hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $canvasItem['milestoneId'] }}"
                                 aria-live="polite">
                                <div class="htmx-indicator" role="status">
                                    {{ __("label.loading_milestone") }}
                                </div>
                            </div>
                            <a href="{{ BASE_URL }}/goalcanvas/editCanvasItem/{{ $id }}?removeMilestone={{ $canvasItem['milestoneId'] }}" class="goalCanvasModal delete formModal"><x-global::elements.icon name="close" /> {{ __("links.remove") }}</a>
                        @endif
                    @endif
                </div>
            </div>

            @if ($id != '')
                <a href="{{ BASE_URL . "/goalcanvas/delCanvasItem/$id" }}" class="formModal delete right">
                    <x-global::elements.icon name="delete" /> {{ __('links.delete') }}
                </a>
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
                        innerHTML: '<span class="material-symbols-outlined">{{ $data['icon'] }}</span>&nbsp;{{ $data['title'] }}',
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
                        innerHTML: '<span class="material-symbols-outlined">{{ $data['icon'] }}</span>&nbsp;{{ $data['title'] }}',
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

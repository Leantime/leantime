@php
    $canvasItem = $tpl->get('canvasItem');
    $canvasTypes = $tpl->get('canvasTypes');
    $hiddenStatusLabels = $tpl->get('statusLabels');
    $statusLabels = $statusLabels ?? $hiddenStatusLabels;
    $hiddenRelatesLabels = $tpl->get('relatesLabels');
    $relatesLabels = $relatesLabels ?? $hiddenRelatesLabels;
    $dataLabels = $tpl->get('dataLabels');

    $id = '';
    if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
        $id = $canvasItem['id'];
    }
@endphp

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            location.href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?showModal={{ $canvasItem['id'] }}";
        }
    }
</script>

<div class="" style="max-width:900px; width:100%;">

    <h4 class="widgettitle title-light" style="padding-bottom: 0"><i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] }}"></i> {{ $canvasTypes[$canvasItem['box']]['title'] }}</h4>
    <hr style="margin-top: 5px; margin-bottom: 15px;">
    {!! $tpl->displayNotification() !!}

    <form class="formModal" method="post" action="{{ BASE_URL }}/{{ $canvasName }}canvas/editCanvasItem/{{ $id }}">

        <input type="hidden" value="{{ $tpl->get('currentCanvas') }}" name="canvasId" />
        <input type="hidden" value="{{ $tpl->escape($canvasItem['box']) }}" name="box" id="box"/>
        <input type="hidden" value="{{ $id }}" name="itemId" id="itemId"/>

        <label>{{ $tpl->__('label.description') }}</label>
        <x-globals::forms.input name="description" value="{{ $tpl->escape($canvasItem['description']) }}" style="width:100%" /><br />

        @if (! empty($statusLabels))
            <label>{{ $tpl->__('label.status') }}</label>
            <x-globals::forms.select :bare="true" name="status" style="width: 50%" id="statusCanvas">
            </x-globals::forms.select><br /><br />
        @else
            <input type="hidden" name="status" value="{{ $canvasItem['status'] ?? array_key_first($hiddenStatusLabels) }}" />
        @endif

        @if (! empty($relatesLabels))
            <label>{{ $tpl->__('label.relates') }}</label>
            <x-globals::forms.select :bare="true" name="relates" style="width: 50%" id="relatesCanvas">
            </x-globals::forms.select><br />
        @else
            <input type="hidden" name="relates" value="{{ $canvasItem['relates'] ?? array_key_first($hiddenRelatesLabels) }}" />
        @endif

        @if ($dataLabels[1]['active'])
            <label>{{ $tpl->__($dataLabels[1]['title']) }}</label>
            @if (isset($dataLabels[1]['type']) && $dataLabels[1]['type'] == 'int')
                <x-globals::forms.input type="number" name="{{ $dataLabels[1]['field'] }}" value="{{ $canvasItem[$dataLabels[1]['field']] }}" /><br />
            @elseif (isset($dataLabels[1]['type']) && $dataLabels[1]['type'] == 'string')
                <x-globals::forms.input name="{{ $dataLabels[1]['field'] }}" value="{{ $canvasItem[$dataLabels[1]['field']] }}" style="width:100%" /><br />
            @else
                <textarea style="width:100%" rows="3" cols="10" name="{{ $dataLabels[1]['field'] }}" class="modalTextArea tiptapSimple">{{ $canvasItem[$dataLabels[1]['field']] }}</textarea><br />
            @endif
        @else
            <input type="hidden" name="{{ $dataLabels[1]['field'] }}" value="" />
        @endif

        @if ($dataLabels[2]['active'])
            <label>{{ $tpl->__($dataLabels[2]['title']) }}</label>
            @if (isset($dataLabels[2]['type']) && $dataLabels[2]['type'] == 'int')
                <x-globals::forms.input type="number" name="{{ $dataLabels[2]['field'] }}" value="{{ $canvasItem[$dataLabels[2]['field']] }}" /><br />
            @elseif (isset($dataLabels[2]['type']) && $dataLabels[2]['type'] == 'string')
                <x-globals::forms.input name="{{ $dataLabels[2]['field'] }}" value="{{ $canvasItem[$dataLabels[2]['field']] }}" style="width:100%" /><br />
            @else
                <textarea style="width:100%" rows="3" cols="10" name="{{ $dataLabels[2]['field'] }}" class="modalTextArea tiptapSimple">{{ $canvasItem[$dataLabels[2]['field']] }}</textarea><br />
            @endif
        @else
            <input type="hidden" name="{{ $dataLabels[2]['field'] }}" value="" />
        @endif

        @if ($dataLabels[3]['active'])
            <label>{{ $tpl->__($dataLabels[3]['title']) }}</label>
            @if (isset($dataLabels[3]['type']) && $dataLabels[3]['type'] == 'int')
                <x-globals::forms.input type="number" name="{{ $dataLabels[3]['field'] }}" value="{{ $canvasItem[$dataLabels[3]['field']] }}" /><br />
            @elseif (isset($dataLabels[3]['type']) && $dataLabels[3]['type'] == 'string')
                <x-globals::forms.input name="{{ $dataLabels[3]['field'] }}" value="{{ $canvasItem[$dataLabels[3]['field']] }}" /><br />
            @else
                <textarea style="width:100%" rows="3" cols="10" name="{{ $dataLabels[3]['field'] }}" class="modalTextArea tiptapSimple">{{ $canvasItem[$dataLabels[3]['field']] }}</textarea><br />
            @endif
        @else
            <input type="hidden" name="{{ $dataLabels[3]['field'] }}" value="" />
        @endif

        <input type="hidden" name="milestoneId" value="{{ $canvasItem['milestoneId'] }}" />
        <input type="hidden" name="changeItem" value="1" />

        @if ($id != '')
            <a href="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvasItem/{{ $id }}" class="{{ $canvasName }}CanvasModal delete right"><i class="fa fa-trash-can"></i> {{ $tpl->__('links.delete') }}</a>
        @endif

        @if ($login::userIsAtLeast($roles::$editor))
            <x-globals::forms.button submit type="primary" id="primaryCanvasSubmitButton">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
            <x-globals::forms.button tag="button" type="secondary" id="saveAndClose" onclick="leantime.{{ $canvasName }}CanvasController.setCloseModal();">{{ $tpl->__('buttons.save_and_close') }}</x-globals::forms.button>
        @endif

        @if ($id !== '')
            <br /><br />
            <h4 class="widgettitle title-light"><span class="fa fa-link"></span> {{ $tpl->__('headlines.linked_milestone') }} <i class="fa fa-question-circle-o helperTooltip" data-tippy-content="{{ $tpl->__('tooltip.link_milestones_tooltip') }}"></i></h4>

            @if ($canvasItem['milestoneId'] == '')
                <center>
                    <h4>{{ $tpl->__('headlines.no_milestone_link') }}</h4>
                    <div id="milestoneSelectors">
                        @if ($login::userIsAtLeast($roles::$editor))
                            <a href="javascript:void(0);" onclick="leantime.{{ $canvasName }}CanvasController.toggleMilestoneSelectors('new');">{{ $tpl->__('links.create_link_milestone') }}</a>
                            @if (count($tpl->get('milestones')) > 0)
                                | <a href="javascript:void(0);" onclick="leantime.{{ $canvasName }}CanvasController.toggleMilestoneSelectors('existing');">{{ $tpl->__('links.link_existing_milestone') }}</a>
                            @endif
                        @endif
                    </div>
                    <div id="newMilestone" style="display:none;">
                        <x-globals::forms.input name="newMilestone" style="width:50%" /><br />
                        <input type="hidden" name="type" value="milestone" />
                        <input type="hidden" name="{{ $canvasName }}canvasitemid" value="{{ $id }} " />
                        <x-globals::forms.button tag="button" type="primary" onclick="jQuery('#primaryCanvasSubmitButton').click()">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
                        <x-globals::forms.button tag="button" type="primary" onclick="leantime.{{ $canvasName }}CanvasController.toggleMilestoneSelectors('hide')">{{ $tpl->__('buttons.cancel') }}</x-globals::forms.button>
                    </div>
                    <div id="existingMilestone" style="display:none;">
                        <x-globals::forms.select :bare="true" data-placeholder="{{ $tpl->__('input.placeholders.filter_by_milestone') }}" name="existingMilestone" class="user-select">
                            <option value=""></option>
                            @foreach ($tpl->get('milestones') as $milestoneRow)
                                <option value="{{ $milestoneRow->id }}"
                                    @if (isset($searchCriteria['milestone']) && $searchCriteria['milestone'] == $milestoneRow->id) selected="selected" @endif
                                >{{ $milestoneRow->headline }}</option>
                            @endforeach
                        </x-globals::forms.select>
                        <input type="hidden" name="type" value="milestone" />
                        <input type="hidden" name="{{ $canvasName }}canvasitemid" value="{{ $id }} " />
                        <x-globals::forms.button tag="button" type="primary" onclick="jQuery('#primaryCanvasSubmitButton').click()">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
                        <x-globals::forms.button tag="button" type="primary" onclick="leantime.{{ $canvasName }}CanvasController.toggleMilestoneSelectors('hide')">{{ $tpl->__('buttons.cancel') }}</x-globals::forms.button>
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
                        {{ $tpl->__('label.loading_milestone') }}
                    </div>
                </div>
                <a href="{{ CURRENT_URL }}?removeMilestone={{ $canvasItem['milestoneId'] }}" class="{{ $canvasName }}CanvasModal delete formModal"><i class="fa fa-close"></i> {{ $tpl->__('links.remove') }}</a>
            @endif
        @endif

    </form>

    @if ($id !== '')
        <br />
        <input type="hidden" name="comment" value="1" />
        <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{{ $tpl->__('subtitles.discussion') }}</h4>
        @php
            $tpl->assign('formUrl', "/$canvasName" . "canvas/editCanvasItem/" . $id);
            $tpl->displaySubmodule('comments-generalComment');
        @endphp
    @endif
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        @if (! empty($statusLabels))
            new SlimSelect({
                select: '#statusCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    @foreach ($statusLabels as $key => $data)
                        @if ($data['active'])
                            { innerHTML: '<i class="fas fa-fw {{ $data['icon'] }}"></i>&nbsp;{{ $data['title'] }}',
                              text: "{{ $data['title'] }}", value: "{{ $key }}", selected: {{ $canvasItem['status'] == $key ? 'true' : 'false' }} },
                        @endif
                    @endforeach
                ]
            });
        @endif

        @if (! empty($relatesLabels))
            new SlimSelect({
                select: '#relatesCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    @foreach ($relatesLabels as $key => $data)
                        @if ($data['active'])
                            { innerHTML: '<i class="fas fa-fw {{ $data['icon'] }}"></i>&nbsp;{{ $data['title'] }}',
                              text: "{{ $data['title'] }}", value: "{{ $key }}", selected: {{ $canvasItem['relates'] == $key ? 'true' : 'false' }} },
                        @endif
                    @endforeach
                ]
            });
        @endif

        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initSimpleEditor();
        }

        @if (! $login::userIsAtLeast($roles::$editor))
            leantime.authController.makeInputReadonly("#global-modal-content");
        @endif

        @if ($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
        @endif

    })
</script>

@php
    $canvasName = $canvasName ?? '';
    $currentCanvas = $currentCanvas ?? '';
    $canvasItem = $canvasItem ?? ['id' => '', 'box' => '', 'description' => '', 'status' => '', 'relates' => '', 'milestoneId' => '', 'milestoneHeadline' => ''];
    $canvasTypes = $canvasTypes ?? [];
    $hiddenStatusLabels = $statusLabels ?? [];
    $statusLabels = $statusLabels ?? [];
    $hiddenRelatesLabels = $relatesLabels ?? [];
    $relatesLabels = $relatesLabels ?? [];
    $dataLabels = $dataLabels ?? [1 => ['active' => false, 'field' => '', 'title' => ''], 2 => ['active' => false, 'field' => '', 'title' => ''], 3 => ['active' => false, 'field' => '', 'title' => '']];
    $milestones = $milestones ?? [];
    $users = $users ?? [];
    $searchCriteria = $searchCriteria ?? [];

    $id = '';
    if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
        $id = $canvasItem['id'];
    }
@endphp

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?showModal={{ $canvasItem['id'] }}";
        }
    }
</script>

<div class="" style="width:900px;">

    <h4 class="widgettitle title-light" style="padding-bottom: 0"><i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] }}"></i> {{ $canvasTypes[$canvasItem['box']]['title'] }}</h4>
    <hr style="margin-top: 5px; margin-bottom: 15px;">
    {!! $tpl->displayNotification() !!}

    <form class="formModal" method="post" action="{{ BASE_URL }}/{{ $canvasName }}canvas/editCanvasItem/{{ $id }}">

        <input type="hidden" value="{{ $currentCanvas }}" name="canvasId" />
        <input type="hidden" value="{{ $canvasItem['box'] }}" name="box" id="box"/>
        <input type="hidden" value="{{ $id }}" name="itemId" id="itemId"/>

        <label>{!! __('label.description') !!}</label>
        <input type="text" name="description" value="{{ $canvasItem['description'] }}" style="width:100%" /><br />

        @if(! empty($statusLabels))
            <label>{!! __('label.status') !!}</label>
            <select name="status" style="width: 50%" id="statusCanvas">
            </select><br /><br />
        @else
            <input type="hidden" name="status" value="{{ $canvasItem['status'] ?? array_key_first($hiddenStatusLabels) }}" />
        @endif

        @if(! empty($relatesLabels))
            <label>{!! __('label.relates') !!}</label>
            <select name="relates" style="width: 50%" id="relatesCanvas">
            </select><br />
        @else
            <input type="hidden" name="relates" value="{{ $canvasItem['relates'] ?? array_key_first($hiddenRelatesLabels) }}" />
        @endif

        @if($dataLabels[1]['active'])
            <label>{!! __($dataLabels[1]['title']) !!}</label>
            @if(isset($dataLabels[1]['type']) && $dataLabels[1]['type'] == 'int')
                <input type="number" name="{{ $dataLabels[1]['field'] }}" value="{{ $canvasItem[$dataLabels[1]['field']] }}"/><br />
            @elseif(isset($dataLabels[1]['type']) && $dataLabels[1]['type'] == 'string')
                <input type="text" name="{{ $dataLabels[1]['field'] }}" value="{{ $canvasItem[$dataLabels[1]['field']] }}" style="width:100%"/><br />
            @else
                <textarea style="width:100%" rows="3" cols="10" name="{{ $dataLabels[1]['field'] }}" class="modalTextArea tiptapSimple">{{ $canvasItem[$dataLabels[1]['field']] }}</textarea><br />
            @endif
        @else
            <input type="hidden" name="{{ $dataLabels[1]['field'] }}" value="" />
        @endif

        @if($dataLabels[2]['active'])
            <label>{!! __($dataLabels[2]['title']) !!}</label>
            @if(isset($dataLabels[2]['type']) && $dataLabels[2]['type'] == 'int')
                <input type="number" name="{{ $dataLabels[2]['field'] }}" value="{{ $canvasItem[$dataLabels[2]['field']] }}"/><br />
            @elseif(isset($dataLabels[2]['type']) && $dataLabels[2]['type'] == 'string')
                <input type="text" name="{{ $dataLabels[2]['field'] }}" value="{{ $canvasItem[$dataLabels[2]['field']] }}" style="width:100%"/><br />
            @else
                <textarea style="width:100%" rows="3" cols="10" name="{{ $dataLabels[2]['field'] }}" class="modalTextArea tiptapSimple">{{ $canvasItem[$dataLabels[2]['field']] }}</textarea><br />
            @endif
        @else
            <input type="hidden" name="{{ $dataLabels[2]['field'] }}" value="" />
        @endif

        @if($dataLabels[3]['active'])
            <label>{!! __($dataLabels[3]['title']) !!}</label>
            @if(isset($dataLabels[3]['type']) && $dataLabels[3]['type'] == 'int')
                <input type="number" name="{{ $dataLabels[3]['field'] }}" value="{{ $canvasItem[$dataLabels[3]['field']] }}"/><br />
            @elseif(isset($dataLabels[3]['type']) && $dataLabels[3]['type'] == 'string')
                <input type="text" name="{{ $dataLabels[3]['field'] }}" value="{{ $canvasItem[$dataLabels[3]['field']] }}"/><br />
            @else
                <textarea style="width:100%" rows="3" cols="10" name="{{ $dataLabels[3]['field'] }}" class="modalTextArea tiptapSimple">{{ $canvasItem[$dataLabels[3]['field']] }}</textarea><br />
            @endif
        @else
            <input type="hidden" name="{{ $dataLabels[3]['field'] }}" value="" />
        @endif

        <input type="hidden" name="milestoneId" value="{{ $canvasItem['milestoneId'] }}" />
        <input type="hidden" name="changeItem" value="1" />

        @if($id != '')
            <a href="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvasItem/{{ $id }}" class="{{ $canvasName }}CanvasModal delete right"><i class='fa fa-trash-can'></i> {!! __('links.delete') !!}</a>
        @endif

        @if($login::userIsAtLeast($roles::$editor))
            <input type="submit" value="{{ __('buttons.save') }}" id="primaryCanvasSubmitButton"/>
            <button type="submit" class="btn btn-default" value="closeModal" id="saveAndClose" onclick="leantime.{{ $canvasName }}CanvasController.setCloseModal();">{!! __('buttons.save_and_close') !!}</button>
        @endif

        @if($id !== '')
            <br /><br />
            <h4 class="widgettitle title-light"><span class="fa fa-link"></span> {!! __('headlines.linked_milestone') !!} <i class="fa fa-question-circle-o helperTooltip" data-tippy-content="{{ __('tooltip.link_milestones_tooltip') }}"></i></h4>

            @if($canvasItem['milestoneId'] == '')
                <center>
                    <h4>{!! __('headlines.no_milestone_link') !!}</h4>

                    <div class="row" id="milestoneSelectors">
                        @if($login::userIsAtLeast($roles::$editor))
                            <div class="col-md-12">
                                <a href="javascript:void(0);" onclick="leantime.{{ $canvasName }}CanvasController.toggleMilestoneSelectors('new');">{!! __('links.create_link_milestone') !!}</a>
                                @if(count($milestones) > 0)
                                    | <a href="javascript:void(0);" onclick="leantime.{{ $canvasName }}CanvasController.toggleMilestoneSelectors('existing');">{!! __('links.link_existing_milestone') !!}</a>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="row" id="newMilestone" style="display:none;">
                        <div class="col-md-12">
                            <input type="text" width="50%" name="newMilestone"><br />
                            <input type="hidden" name="type" value="milestone" />
                            <input type="hidden" name="{{ $canvasName }}canvasitemid" value="{{ $id }} " />
                            <input type="button" value="{{ __('buttons.save') }}" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                            <input type="button" value="{{ __('buttons.cancel') }}" onclick="leantime.{{ $canvasName }}CanvasController.toggleMilestoneSelectors('hide')" class="btn btn-primary" />
                        </div>
                    </div>

                    <div class="row" id="existingMilestone" style="display:none;">
                        <div class="col-md-12">
                            <select data-placeholder="{{ __('input.placeholders.filter_by_milestone') }}" name="existingMilestone" class="user-select">
                                <option value=""></option>
                                @foreach($milestones as $milestoneRow)
                                    <option value="{{ $milestoneRow->id }}"
                                        @if(isset($searchCriteria['milestone']) && $searchCriteria['milestone'] == $milestoneRow->id) selected='selected' @endif
                                    >{{ $milestoneRow->headline }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="type" value="milestone" />
                            <input type="hidden" name="{{ $canvasName }}canvasitemid" value="{{ $id }} " />
                            <input type="button" value="{{ __('buttons.save') }}" onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary" />
                            <input type="button" value="{{ __('buttons.cancel') }}" onclick="leantime.{{ $canvasName }}CanvasController.toggleMilestoneSelectors('hide')" class="btn btn-primary" />
                        </div>
                    </div>
                </center>
            @else
                <div hx-trigger="load"
                     hx-indicator=".htmx-indicator"
                     hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $canvasItem['milestoneId'] }}">
                    <div class="htmx-indicator">
                        {!! __('label.loading_milestone') !!}
                    </div>
                </div>
                <a href="{{ CURRENT_URL }}?removeMilestone={{ $canvasItem['milestoneId'] }}" class="{{ $canvasName }}CanvasModal delete formModal"><i class="fa fa-close"></i> {!! __('links.remove') !!}</a>
            @endif
        @endif

    </form>

    @if($id !== '')
        <br />
        <input type="hidden" name="comment" value="1" />
        <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{!! __('subtitles.discussion') !!}</h4>
        @php
            $tpl->assign('formUrl', '/' . $canvasName . 'canvas/editCanvasItem/' . $id);
            $tpl->displaySubmodule('comments-generalComment');
        @endphp
    @endif
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        @if(! empty($statusLabels))
            new SlimSelect({
                select: '#statusCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    @foreach($statusLabels as $key => $data)
                        @if($data['active'])
                            { innerHTML: '<i class="fas fa-fw {{ $data['icon'] }}"></i>&nbsp;{{ $data['title'] }}',
                              text: "{{ $data['title'] }}", value: "{{ $key }}", selected: {{ $canvasItem['status'] == $key ? 'true' : 'false' }}},
                        @endif
                    @endforeach
                ]
            });
        @endif

        @if(! empty($relatesLabels))
            new SlimSelect({
                select: '#relatesCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    @foreach($relatesLabels as $key => $data)
                        @if($data['active'])
                            { innerHTML: '<i class="fas fa-fw {{ $data['icon'] }}"></i>&nbsp;{{ $data['title'] }}',
                              text: "{{ $data['title'] }}", value: "{{ $key }}", selected: {{ $canvasItem['relates'] == $key ? 'true' : 'false' }}},
                        @endif
                    @endforeach
                ]
            });
        @endif

        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initSimpleEditor();
        }

        @if(! $login::userIsAtLeast($roles::$editor))
            leantime.authController.makeInputReadonly(".nyroModalCont");
        @endif

        @if($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
        @endif

    })
</script>

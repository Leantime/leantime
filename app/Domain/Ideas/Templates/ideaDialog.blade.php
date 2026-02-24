@php
    $canvasItem = $tpl->get('canvasItem');
    $canvasTypes = $tpl->get('canvasTypes');

    $id = '';
    if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
        $id = $canvasItem['id'];
    }
@endphp

<script type="text/javascript">
    window.onload = function () {
        if (!window.jQuery) {
            location.href = "{{ BASE_URL }}/ideas/showBoards?showIdeaModal={{ $canvasItem['id'] }}";
        }
    }
</script>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/ideas/ideaDialog/{{ $id }}">

<div class="row">

    <div class="col-md-8">

        <input type="hidden" value="{{ $tpl->get('currentCanvas') }}" name="canvasId"/>
        <input type="hidden" value="{{ $tpl->escape($canvasItem['box']) }}" name="box" id="box"/>
        <input type="hidden" value="{{ $id }}" name="itemId" id="itemId"/>
        <input type="hidden" name="status" value="{{ $canvasItem['status'] }}" />
        <input type="hidden" value="{{ $id }}" name="id" autocomplete="off" readonly/>

        <input type="hidden" name="milestoneId" value="{{ $canvasItem['milestoneId'] }}"/>
        <input type="hidden" name="changeItem" value="1"/>

        <x-globals::forms.input :bare="true" type="text" name="description" class="main-title-input" style="width:99%;" value="{{ $tpl->escape($canvasItem['description']) }}"
               placeholder="{{ $tpl->__('input.placeholders.short_name') }}" /><br/>

        <x-globals::forms.input :bare="true" type="text" value="{{ $tpl->escape($canvasItem['tags']) }}" name="tags" id="tags" />

        <textarea rows="3" cols="10" name="data" class="tiptapComplex"
                  placeholder="">{!! $tpl->escapeMinimal($canvasItem['data']) !!}</textarea><br/>

        <x-globals::forms.button submit type="primary" id="primaryCanvasSubmitButton">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
        <x-globals::forms.button tag="button" type="primary" id="saveAndClose">{{ $tpl->__('buttons.save_and_close') }}</x-globals::forms.button>

        @if ($id !== '')
            <br/>
            <hr>
            <input type="hidden" name="comment" value="1"/>

            <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{{ $tpl->__('subtitles.discussion') }}</h4>
            @php
                $tpl->assign('formUrl', BASE_URL . '/ideas/ideaDialog/' . $id . '');
                $tpl->displaySubmodule('comments-generalComment');
            @endphp
        @endif

    </div>

    <div class="col-md-4">
        @if ($id !== '')
            <br/><br/>
            <h4 class="widgettitle title-light"><span
                    class="fa fa-link"></span> {{ $tpl->__('headlines.linked_milestone') }} <i class="fa fa-question-circle-o helperTooltip" data-tippy-content="{{ $tpl->__('tooltip.link_milestones_tooltip') }}"></i></h4>

            <ul class="sortableTicketList" style="width:99%">
                @if ($canvasItem['milestoneId'] == '')
                    <li class="ui-state-default center" id="milestone_0">
                        <h4>{{ $tpl->__('headlines.no_milestone_link') }}</h4>
                        {{ $tpl->__('text.use_milestone_to_track_idea') }}<br/>
                        <div id="milestoneSelectors">
                            @if ($login::userIsAtLeast($roles::$editor))
                                <a href="javascript:void(0);"
                                   onclick="leantime.ideasController.toggleMilestoneSelectors('new');">{{ $tpl->__('links.create_link_milestone') }}</a>
                                | <a href="javascript:void(0);"
                                     onclick="leantime.ideasController.toggleMilestoneSelectors('existing');">{{ $tpl->__('links.link_existing_milestone') }}</a>
                            @endif
                        </div>
                        <div id="newMilestone" style="display:none;">
                            <x-globals::forms.textarea name="newMilestone" /><br/>
                            <input type="hidden" name="type" value="milestone"/>
                            <input type="hidden" name="leancanvasitemid" value="{{ $id }} "/>
                            <x-globals::forms.button tag="button" type="primary" onclick="jQuery('#primaryCanvasSubmitButton').click()">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
                            <a href="javascript:void(0);"
                               onclick="leantime.ideasController.toggleMilestoneSelectors('hide');">
                                <i class="fas fa-times"></i> {{ $tpl->__('links.cancel') }}
                            </a>
                        </div>

                        <div id="existingMilestone" style="display:none;">
                            <x-globals::forms.select :bare="true" data-placeholder="{{ $tpl->__('input.placeholders.filter_by_milestone') }}"
                                    name="existingMilestone" class="user-select">
                                <option value="">{{ $tpl->__('text.all_milestones') }}</option>
                                @foreach ($tpl->get('milestones') as $milestoneRow)
                                    <option value="{{ $milestoneRow->id }}"
                                        @if (isset($searchCriteria['milestone']) && $searchCriteria['milestone'] == $milestoneRow->id) selected="selected" @endif
                                    >{{ $tpl->escape($milestoneRow->headline) }}</option>
                                @endforeach
                            </x-globals::forms.select>
                            <input type="hidden" name="type" value="milestone"/>
                            <input type="hidden" name="leancanvasitemid" value="{{ $id }} "/>
                            <x-globals::forms.button tag="button" type="primary" onclick="jQuery('#primaryCanvasSubmitButton').click()">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
                            <a href="javascript:void(0);"
                               onclick="leantime.ideasController.toggleMilestoneSelectors('hide');">
                                <i class="fas fa-times"></i> {{ $tpl->__('links.cancel') }}
                            </a>
                        </div>
                    </li>
                @else
                    <li class="ui-state-default" id="milestone_{{ $canvasItem['milestoneId'] }}">

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
                        <a href="{{ CURRENT_URL }}?removeMilestone={{ $canvasItem['milestoneId'] }}" class="ideaCanvasModal delete formModal"><i class="fa fa-close"></i> {{ $tpl->__('links.remove') }}</a>

                    </li>
                @endif

            </ul>

        @endif
    </div>

</div>

</form>

<div class="showDialogOnLoad">
    @if ($id != '')
        <a href="{{ BASE_URL }}/ideas/delCanvasItem/{{ $id }}" class="ideaModal delete right"><i
                    class="fa fa-trash"></i> {{ $tpl->__('links.delete') }}</a>
    @endif
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }
        leantime.ticketsController.initTagsInput();

        @if (! $login::userIsAtLeast($roles::$editor))
            leantime.authController.makeInputReadonly("#global-modal-content");
        @endif

        @if ($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
        @endif

    })
</script>

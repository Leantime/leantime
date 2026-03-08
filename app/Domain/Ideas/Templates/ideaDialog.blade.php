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

        <x-globals::forms.text-input :bare="true" type="text" name="description" class="main-title-input tw:w-full" value="{{ $tpl->escape($canvasItem['description']) }}"
               placeholder="{{ $tpl->__('input.placeholders.short_name') }}" /><br/>

        <x-globals::forms.text-input :bare="true" type="text" value="{{ $tpl->escape($canvasItem['tags']) }}" name="tags" id="tags" />

        <textarea rows="3" cols="10" name="data" class="tiptapComplex"
                  placeholder="">{!! $tpl->escapeMinimal($canvasItem['data']) !!}</textarea><br/>

        <x-globals::forms.button :submit="true" contentRole="primary" name="save" id="primaryCanvasSubmitButton">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
        <x-globals::forms.button :submit="true" contentRole="primary" name="save" value="closeModal" id="saveAndClose">{{ $tpl->__('buttons.save_and_close') }}</x-globals::forms.button>

        @if ($id !== '')
            <br/>
            <hr>
            <input type="hidden" name="comment" value="1"/>

            <x-globals::elements.section-title icon="forum">{{ $tpl->__('subtitles.discussion') }}</x-globals::elements.section-title>
            @php
                $tpl->assign('formUrl', BASE_URL . '/ideas/ideaDialog/' . $id . '');
                $tpl->displaySubmodule('comments-generalComment');
            @endphp
        @endif

    </div>

    <div class="col-md-4">
        @if ($id !== '')
            <br/><br/>
            <x-globals::elements.section-title icon="link">{{ $tpl->__('headlines.linked_milestone') }} <x-globals::elements.icon name="help_outline" class="helperTooltip" data-tippy-content="{{ $tpl->__('tooltip.link_milestones_tooltip') }}" /></x-globals::elements.section-title>

            <ul class="sortableTicketList tw:w-full">
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
                            <x-globals::forms.button tag="button" contentRole="primary" onclick="jQuery('#primaryCanvasSubmitButton').click()">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
                            <a href="javascript:void(0);"
                               onclick="leantime.ideasController.toggleMilestoneSelectors('hide');">
                                <x-globals::elements.icon name="close" /> {{ $tpl->__('links.cancel') }}
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
                            <x-globals::forms.button tag="button" contentRole="primary" onclick="jQuery('#primaryCanvasSubmitButton').click()">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
                            <a href="javascript:void(0);"
                               onclick="leantime.ideasController.toggleMilestoneSelectors('hide');">
                                <x-globals::elements.icon name="close" /> {{ $tpl->__('links.cancel') }}
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
                        <a href="{{ CURRENT_URL }}?removeMilestone={{ $canvasItem['milestoneId'] }}" class="ideaCanvasModal delete formModal"><x-globals::elements.icon name="close" /> {{ $tpl->__('links.remove') }}</a>

                    </li>
                @endif

            </ul>

        @endif
    </div>

</div>

</form>

<div class="showDialogOnLoad">
    @if ($id != '')
        <a href="{{ BASE_URL }}/ideas/delCanvasItem/{{ $id }}" class="ideaModal delete right"><x-globals::elements.icon name="delete" /> {{ $tpl->__('links.delete') }}</a>
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

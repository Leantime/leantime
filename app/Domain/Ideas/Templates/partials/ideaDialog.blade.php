<x-global::content.modal.modal-buttons />
@php
    if (isset($canvasItem->id) && $canvasItem->id != '') {
        $id = $canvasItem->id;
    }
@endphp


    <script type="text/javascript">
        window.onload = function() {
            if (!window.jQuery) {
                //It's not a modal
                location.href = "{{ BASE_URL }}/ideas/showBoards?showIdeaModal={{ $canvasItem->id }}";
            }
        }
    </script>

    <x-global::content.modal.header>
        Add Ideas
    </x-global::content.modal.header>
    
    <x-global::content.modal.form action="{{ BASE_URL }}/ideas/ideaDialog/{{ $id }}">

    
        <div class="row">
            <div class="col-md-8">
                <input type="hidden" value="{{ $currentCanvas }}" name="canvasId" />
                <input type="hidden" value="{{ $canvasItem->box }}" name="box" id="box" />
                <input type="hidden" value="{{ $id }}" name="itemId" id="itemId" />
                <input type="hidden" name="status" value="{{ $canvasItem->status }}" />
                <input type="hidden" name="milestoneId" value="{{ $canvasItem->milestoneId }}" />
                <input type="hidden" name="changeItem" value="1" />

                <x-global::forms.text-input 
                    type="text" 
                    name="description" 
                    value="{{ $canvasItem->description }}" 
                    labelText="{{ __('input.placeholders.short_name') }}" 
                    class="main-title-input" 
                />
                
                <x-global::forms.text-input 
                    type="text" 
                    name="tags" 
                    id="tags" 
                    value="{{ $canvasItem->tags }}" 
                />
                <textarea rows="3" cols="10" name="data" class="complexEditor" placeholder="">{!! htmlentities($canvasItem->data) !!}</textarea><br />

                <x-global::forms.button 
                    type="submit" 
                    id="primaryCanvasSubmitButton">
                    {!! __('buttons.save') !!}
                 </x-global::forms.button>
            
                <x-global::forms.button 
                    type="submit" 
                    class="btn btn-primary" 
                    value="closeModal"
                    contentRole="ghost"
                    id="saveAndClose">
                    {!! __('buttons.save_and_close') !!}
                </x-global::forms.button>
            

                @if ($id !== '')
                    <br />
                    <hr>
                    <input type="hidden" name="comment" value="1" />

                    <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{!! __('subtitles.discussion') !!}</h4>
                    @include("comments::includes.generalComment", ["formUrl" => BASE_URL . '/ideas/ideaDialog/' . $id])
                @endif
            </div>

            <div class="col-md-4">
                @if ($id !== '')
                    <br /><br />
                    <h4 class="widgettitle title-light">
                        <span class="fa fa-link"></span> {!! __('headlines.linked_milestone') !!}
                        <i class="fa fa-question-circle-o helperTooltip" data-tippy-content="{!! __('tooltip.link_milestones_tooltip') !!}"></i>
                    </h4>

                    <ul class="sortableTicketList" style="width:99%">
                        @if ($canvasItem->milestoneId == '')
                            <li class="ui-state-default center" id="milestone_0">
                                <h4>{!! __('headlines.no_milestone_link') !!}</h4>
                                {!! __('text.use_milestone_to_track_idea') !!}<br />
                                <div class="row" id="milestoneSelectors">
                                    @if ($login::userIsAtLeast($roles::$editor))
                                        <div class="col-md-12">
                                            <a href="javascript:void(0);"
                                                onclick="leantime.canvasController.toggleMilestoneSelectors('new');">{!! __('links.create_link_milestone') !!}</a>
                                            | <a href="javascript:void(0);"
                                                onclick="leantime.canvasController.toggleMilestoneSelectors('existing');">{!! __('links.link_existing_milestone') !!}</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="row" id="newMilestone" style="display:none;">
                                    <div class="col-md-12">
                                        <textarea name="newMilestone"></textarea><br />
                                        <input type="hidden" name="type" value="milestone" />
                                        <input type="hidden" name="leancanvasitemid" value="{{ $id }}" />
                                        <x-global::forms.button 
                                            type="button" 
                                            onclick="jQuery('#primaryCanvasSubmitButton').click()" 
                                            class="btn btn-primary">
                                            {!! __('buttons.save') !!}
                                        </x-global::forms.button>
                                    
                                        <x-global::forms.button 
                                            tag="a"
                                            href="javascript:void(0);" 
                                            contentRole="ghost"
                                            onclick="leantime.canvasController.toggleMilestoneSelectors('hide');">
                                            <i class="fas fa-times"></i> {!! __('links.cancel') !!}
                                         </x-global::forms.button>
                                    </div>
                            </div>

                            <div class="row" id="existingMilestone" style="display:none;">
                                <div class="col-md-12">
                                    <x-global::forms.select :data-placeholder="__('input.placeholders.filter_by_milestone')" name="existingMilestone"
                                        class="user-select">
                                        <x-global::forms.select.select-option value="">
                                            {!! __('text.all_milestones') !!}
                                        </x-global::forms.select.select-option>

                                        @foreach ($milestones as $milestoneRow)
                                            <x-global::forms.select.select-option :value="$milestoneRow->id" :selected="isset($searchCriteria['milestone']) &&
                                                $searchCriteria['milestone'] == $milestoneRow->id">
                                                {{ $milestoneRow->headline }}
                                            </x-global::forms.select.select-option>
                                        @endforeach
                                    </x-global::forms.select>

                                    <input type="hidden" name="type" value="milestone" />
                                    <input type="hidden" name="leancanvasitemid" value="{{ $id }}" />
                                    <x-global::forms.button type="button"
                                        onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary">
                                        {!! __('buttons.save') !!}
                                    </x-global::forms.button>

                                    <x-global::forms.button tag="a" href="javascript:void(0);"
                                        onclick="leantime.canvasController.toggleMilestoneSelectors('hide');">
                                        <i class="fas fa-times"></i> {!! __('links.cancel') !!}
                                    </x-global::forms.button>

                                </div>
                            </div>
                        </li>
                    @else
                        <li class="ui-state-default" id="milestone_{{ $canvasItem->milestoneId }}"
                            class="leanCanvasMilestone">

                            <div hx-trigger="load" hx-indicator=".htmx-indicator"
                                hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $canvasItem->milestoneId }}">
                                <div class="htmx-indicator">
                                    {!! __('label.loading_milestone') !!}
                                </div>
                            </div>
                            <a href="{{ CURRENT_URL }}?removeMilestone={{ $canvasItem->milestoneId }}"
                                class="{{ $canvasName }}CanvasModal delete formModal"><i class="fa fa-close"></i>
                                {!! __('links.remove') !!}</a>

                        </li>
                    @endif
                </ul>
            @endif
        </div>
    </div>

</x-global::content.modal.form>

<div class="showDialogOnLoad">
    @if ($id != '')
        <a href="{{ BASE_URL }}/ideas/delCanvasItem/{{ $id }}" class="ideaModal delete right"><i
                class="fa fa-trash"></i> {!! __('links.delete') !!}</a>
    @endif
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {

        leantime.editorController.initComplexEditor();
        leantime.ticketsController.initTagsInput();

        @if (!$login::userIsAtLeast($roles::$editor))
            leantime.authController.makeInputReadonly(".nyroModalCont");
        @endif

        @if ($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
        @endif
    })
</script>

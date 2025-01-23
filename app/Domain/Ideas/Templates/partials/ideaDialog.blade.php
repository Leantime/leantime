<x-global::content.modal.modal-buttons>
    @if ($id !== '')
        <x-global::content.modal.header-button variant="delete" href="{{ BASE_URL . '/ideas/delCanvasItem/' . $id }}" />
    @endif
</x-global::content.modal.modal-buttons>
@php
    use Leantime\Core\Support\EditorTypeEnum;

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

<div>


    <div class="row">
        <div class="col-md-8">
            <input type="hidden" value="{{ $currentCanvas }}" name="canvasId" />
            <input type="hidden" value="{{ $canvasItem->box }}" name="box" id="box" />
            <input type="hidden" value="{{ $id }}" name="itemId" id="itemId" />
            <input type="hidden" name="status" value="{{ $canvasItem->status }}" />
            <input type="hidden" name="milestoneId" value="{{ $canvasItem->milestoneId }}" />
            <input type="hidden" name="changeItem" value="1" />

            <x-global::forms.text-input type="text" name="description" value="{{ $canvasItem->description }}"
                placeholder="{{ __('input.placeholders.short_name') }}" variant="title"
                hx-post="{{ BASE_URL }}/hx/ideas/ideaDialog/patch/{{ $canvasItem->id }}" hx-trigger="change"
                hx-swap="none" />

            <x-global::forms.select name="tags[]" id="tags" variant="tags" maxItemCount=4>

                @if (!empty($canvasItem->tags))

                    @foreach ($canvasItem->tags as $label)
                        <x-global::forms.select.select-option value="{{ $label }}" selected="selected">

                            {!! __($label) !!}

                        </x-global::forms.select.select-option>
                    @endforeach

                @endif

            </x-global::forms.select>


            <x-global::forms.text-editor name="data" :type="EditorTypeEnum::Complex->value" :value="$canvasItem->data"
                hx-post="{{ BASE_URL }}/hx/ideas/ideaDialog/patch/{{ $canvasItem->id }}" hx-trigger="change"
                hx-swap="none" />

            <x-global::forms.button scale="xs" type="submit" id="primaryCanvasSubmitButton">
                {!! __('buttons.save') !!}
            </x-global::forms.button>

            <x-global::forms.button type="submit" class="btn btn-primary" value="closeModal" contentRole="secondary"
                scale="xs" id="cancel">
                {!! __('buttons.cancel') !!}
            </x-global::forms.button>


            @if ($id !== '')
                <br />
                <hr>
                <input type="hidden" name="comment" value="1" />

                <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{!! __('subtitles.discussion') !!}</h4>
                <x-comments::list :module="'idea'" :statusUpdates="'false'" :moduleId="$id" />
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
                                    <x-global::forms.button type="button"
                                        onclick="jQuery('#primaryCanvasSubmitButton').click()" class="btn btn-primary">
                                        {!! __('buttons.save') !!}
                                    </x-global::forms.button>

                                    <x-global::forms.button tag="button" variant="link" scale="sm"
                                        contentRole="ghost" labelText="Cancel" name="cancel" type="button"
                                        onclick="htmx.find('#modal-wrapper #main-page-modal').close();" />
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
                                        onclick="jQuery('#primaryCanvasSubmitButton').click()"
                                        class="btn btn-primary">
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
                        </li>
                    @endif
                </ul>
            @endif
        </div>
    </div>

</div>

<div class="showDialogOnLoad">
    @if ($id != '')
        <a href="{{ BASE_URL }}/ideas/delCanvasItem/{{ $id }}" class="ideaModal delete right"><i
                class="fa fa-trash"></i> {!! __('links.delete') !!}</a>
    @endif
</div>

<script type="module">
    import "@mix('/js/Domain/Ideas/Js/ideasController.js')"
    import "@mix('/js/Domain/Auth/Js/authController.js')"
    import "@mix('/js/Domain/Comments/Js/commentsController.js')"

    jQuery(document).ready(function() {

        @if (!$login::userIsAtLeast($roles::$editor))
            authController.makeInputReadonly(".nyroModalCont");
        @endif

        @if ($login::userHasRole([$roles::$commenter]))
            commentsController.enableCommenterForms();
        @endif

        jQuery('#primaryCanvasSubmitButton').click(function() {
            jQuery.growl({
                message: "Idea Updated",
                style: "success"
            });
            htmx.find("#modal-wrapper #main-page-modal").close();
        });

        jQuery('#cancel').click(function() {
            htmx.find("#modal-wrapper #main-page-modal").close();
        });
    })
</script>

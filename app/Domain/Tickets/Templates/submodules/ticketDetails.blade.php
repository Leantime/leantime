<input type="hidden" value="{{ $ticket->id }}" name="id" autocomplete="off" readonly/>

<div class="row">
    <div class="col-md-9">
        <div class="row marginBottom">
            <div class="col-md-12">

                <div class="form-group">
                    <input type="text" value="{{ $ticket->headline }}" name="headline" class="main-title-input" autocomplete="off" style="width:99%; margin-bottom:10px;" placeholder="{{ __('input.placeholders.enter_title_of_todo') }}"/>
                </div>
                <!-- Status -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]">{!! __('label.todo_status') !!}</label>
                    <div class="">
                        <select
                            id="status-select"
                            class=""
                            name="status"
                            data-placeholder="{{ isset($ticket->status) ? $statusLabels[$ticket->status]['name'] ?? '' : '' }}"
                        >
                            @foreach ($statusLabels as $key => $label)
                                <option value="{{ $key }}"
                                    @if ($ticket->status == $key) selected='selected' @endif
                                >{{ $label['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Priority -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]">{!! __('label.priority') !!}</label>
                    <div class="">
                        <select id='priority' name='priority' class="">
                            <option value="">{!! __('label.priority_not_defined') !!}</option>
                            @foreach ($priorities as $priorityKey => $priorityValue)
                                <option value="{{ $priorityKey }}"
                                    @if ($priorityKey == $ticket->priority) selected='selected' @endif
                                >{{ $priorityValue }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Effort -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]">{!! __('label.effort') !!}</label>
                    <div class="">
                        <select id='storypoints' name='storypoints' class="">
                            <option value="">{!! __('label.effort_not_defined') !!}</option>
                            @foreach ($efforts as $effortKey => $effortValue)
                                <option value="{{ $effortKey }}"
                                    @if ($effortKey == $ticket->storypoints) selected='selected' @endif
                                >{{ $effortValue }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Editor -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]">{!! __('label.editor') !!}</label>
                    <div class="">

                        <select data-placeholder="{{ __('label.filter_by_user') }}" style="width:175px;"
                                name="editorId" id="editorId" class="user-select tw-mr-sm">
                            <option value="">{!! __('label.not_assigned_to_user') !!}</option>
                            @foreach ($users as $userRow)
                                <option value="{{ $userRow['id'] }}"
                                    @if ($ticket->editorId == $userRow['id']) selected='selected' @endif
                                >{{ $userRow['firstname'] . ' ' . $userRow['lastname'] }}</option>
                            @endforeach
                        </select>&nbsp;
                    </div>
                    <div style="padding-top:6px;">
                        @if ($login::userIsAtLeast($roles::$editor))
                           <a href="javascript:void(0);" onclick="jQuery('#editorId').val({{ session('userdata.id') }}).trigger('chosen:updated');">{!! __('label.assign_to_me') !!}</a>
                        @endif
                    </div>
                </div>

                <!-- Collaborators -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]">{!! __('label.collaborators') !!}</label>
                    <div class="">
                        <select data-placeholder="{{ __('label.filter_by_user') }}"
                                style="width:175px;"
                                name="collaborators[]"
                                id="collaborators"
                                class="user-select tw-mr-sm"
                                multiple>
                            @foreach ($users as $userRow)
                                <option value="{{ $userRow['id'] }}"
                                    @if (in_array($userRow['id'], $ticket->collaborators ?? [])) selected='selected' @endif
                                >
                                    {{ $userRow['firstname'] . ' ' . $userRow['lastname'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]">{!! __('label.due_date') !!}</label>
                    <div class="">
                        <input type="text" class="dates" style="width:110px;" id="deadline" autocomplete="off"
                               value="{{ format($ticket->dateToFinish)->date() }}"
                               name="dateToFinish" placeholder="{{ __('language.dateformat') }}"/>

                        <input type="time" class="timepicker tw-mr-sm" style="width:120px;" id="dueTime" autocomplete="off"
                               value="{{ format($ticket->dateToFinish)->time24() }}"
                               name="timeToFinish"/>
                    </div>
                    <div style="padding-top:6px;">
                        @dispatchEvent('afterDates', ['ticket' => $ticket])
                    </div>
                </div>

                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]">{!! __('label.tags') !!}</label>
                    <div class="">
                        <input type="text" value="{{ $ticket->tags }}" name="tags" id="tags" />
                    </div>
                </div>
                <br />

                <div class="form-group" id="descriptionEditor">
                    <textarea name="description" id="ticketDescription"
                              class="tiptapComplex">{!! $ticket->description !== null ? htmlentities($ticket->description) : '' !!}</textarea><br/>
                </div>
                <input type="hidden" name="acceptanceCriteria" value=""/>

            </div>
        </div>

        <div class="sticky-modal-footer">
            <div class="row">
                <div class="col-md-12" style="margin-top:15px;">
                    <input type="hidden" name="saveTicket" value="1" />
                    <input type="hidden" id="saveAndCloseButton" name="saveAndCloseTicket" value="0" />

                    <input type="submit" name="saveTicket" class="saveTicketBtn" value="{{ __('buttons.save') }}"/>
                    <input type="submit" name="saveAndCloseTicket" class="btn btn-outline" onclick="jQuery('#saveAndCloseButton').val('1');" value="{{ __('buttons.save_and_close') }}"/>
                </div>
            </div>
        </div>

        @if ($ticket->id)
            <br />
            <hr />
            @dispatchEvent('beforeSubtasks', ['ticketId' => $ticket->id])
            <h4 class="widgettitle title-light"><i class="fa-solid fa-sitemap"></i> {!! __('subtitles.subtasks') !!}</h4>

            <x-global::hx
                wrapperId="ticketSubtasks"
                :for="\Leantime\Domain\Tickets\Hxcontrollers\Subtasks::class"
                :id="$ticket->id"
                trigger="load"
                indicator=".subtaskIndicator"
            />
            <div class="htmx-indicator subtaskIndicator">
                Loading Subtasks ...<br /><br />
            </div>

        <h4 class="widgettitle title-light"><span
                    class="fa-solid fa-comments"></span>{!! __('subtitles.discussion') !!}</h4>

        <div class="row-fluid">
        <form method="post" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}" class="formModal">
            <input type="hidden" name="comment" value="1" />
            @include('comments::submodules.generalComment', ['formUrl' => BASE_URL . '/tickets/showTicket/' . $ticket->id])
        </form>
        </div>
        @endif
    </div>
    <div class="col-md-3">

        <div class="row marginBottom">
            <div class="col-md-12">
                <h5 class="accordionTitle" id="accordion_link_tickets-organization" style="padding-bottom:15px; font-size:var(--font-size-l)">
                    <a href="javascript:void(0)"
                       class="accordion-toggle"
                       id="accordion_toggle_tickets-organization"
                       onclick="leantime.snippets.accordionToggle('tickets-organization');">
                            <i class="fa fa-angle-down"></i>
                            <span class="fa fa-folder-open"></span>
                            {!! __('subtitles.organization') !!}
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-organization" style="padding-left:0">

                    <!-- Type -->
                    <div class="form-group">
                        <label class="control-label">{!! __('label.todo_type') !!}</label>
                        <div class="">
                            <select id='type' name='type' class="span11">
                                @foreach ($ticketTypes as $types)
                                    <option value="{{ strtolower($types) }}"
                                        @if (strtolower($types) == strtolower($ticket->type ?? '')) selected='selected' @endif
                                    >{!! __('label.' . strtolower($types)) !!}</option>
                                @endforeach
                            </select><br/>
                        </div>
                    </div>

                    <!-- Project -->
                    <div class="form-group">
                        <label class="control-label">{!! __('label.project') !!}</label>
                        <select name="projectId" class="tw-w-full">
                            @foreach ($allAssignedprojects as $project)
                                <option value="{{ $project['id'] }}"
                                    @if ($ticket->projectId == $project['id'])
                                        selected
                                    @elseif (session('currentProject') == $project['id'])
                                        selected
                                    @endif
                                >{{ $tpl->escape($project['name']) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Milestones -->
                    <div class="form-group">
                        <label class="control-label">{!! __('label.milestone') !!}</label>
                        <div class="">
                            <div class="form-group">
                                <select  name="milestoneid"  class="span11" >
                                    <option value="">{!! __('label.not_assigned_to_milestone') !!}</option>
                                    @foreach ($milestones as $milestoneRow)
                                        <option value="{{ $milestoneRow->id }}"
                                            @if ($ticket->milestoneid == $milestoneRow->id) selected='selected' @endif
                                        >{{ $tpl->escape($milestoneRow->headline) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sprint -->
                    <div class="form-group">
                        <label class="control-label">{!! __('label.sprint') !!}</label>
                        <div class="">

                            <select id="sprint-select" class="span11" name="sprint"
                                    data-placeholder="{{ $ticket->sprint }}">
                                <option value="">{!! __('label.backlog') !!}</option>
                                @if ($sprints)
                                    @foreach ($sprints as $sprintRow)
                                        <option value="{{ $sprintRow->id }}"
                                            @if ($ticket->sprint == $sprintRow->id) selected='selected' @endif
                                        >{{ $sprintRow->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <!-- Related -->
                    <div class="form-group">
                        <label class="control-label">{!! __('label.related_to') !!}</label>
                        <div class="">
                            <div class="form-group">
                                <select  name="dependingTicketId"  class="span11" >
                                    <option value="">{!! __('label.not_related') !!}</option>
                                    @if (is_array($ticketParents))
                                        @foreach ($ticketParents as $ticketRow)
                                            <option value="{{ $ticketRow->id }}"
                                                @if ($ticket->dependingTicketId == $ticketRow->id) selected='selected' @endif
                                            >{{ $tpl->escape($ticketRow->headline) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="row marginBottom">
            <div class="col-md-12">
                <h5 class="accordionTitle" id="accordion_link_tickets-dates" style="padding-bottom:15px; font-size:var(--font-size-l)">
                    <a href="javascript:void(0)"
                       class="accordion-toggle"
                       id="accordion_toggle_tickets-dates"
                       onclick="leantime.snippets.accordionToggle('tickets-dates');">
                        <i class="fa fa-angle-down"></i>
                        <span class="fa fa-calendar"></span>
                        {!! __('subtitles.schedule') !!}
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-dates" style="padding-left:0">
                    <div class="form-group">
                        <label class=" control-label">{!! __('label.working_date_from') !!}</label>
                        <div class="">
                            <input type="text" class="editFrom" style="width:100px;" name="editFrom" autocomplete="off"
                                   value="{{ format($ticket->editFrom)->date() }}" placeholder="{{ __('language.dateformat') }}"/>
                            <input type="time" class="timepicker" style="width:120px;" id="timeFrom" autocomplete="off"
                                   value="{{ format($ticket->editFrom)->time24() }}"
                                   name="timeFrom"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label">{!! __('label.working_date_to') !!}</label>
                        <div class="">
                            <input type="text" class="editTo" style="width:100px;" name="editTo" autocomplete="off"
                                   value="{{ format($ticket->editTo)->date() }}" placeholder="{{ __('language.dateformat') }}"/>
                            <input type="time" class="timepicker" style="width:120px;" id="timeTo" autocomplete="off"
                                   value="{{ format($ticket->editTo)->time24() }}"
                                   name="timeTo"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label">{!! __('label.planned_hours') !!} / {!! __('label.estimated_hours_remaining') !!}</label>
                        <div class="">
                            <input type="text" value="{{ $ticket->planHours }}" name="planHours" style="width:45px;"/>&nbsp;/&nbsp;
                            <input type="text" value="{{ $ticket->hourRemaining }}" name="hourRemaining" style="width:45px;"/>
                            <a href="javascript:void(0)" class="infoToolTip" data-placement="left" data-toggle="tooltip" data-tippy-content="{{ __('tooltip.how_many_hours_remaining') }}">
                                &nbsp;<i class="fa fa-question-circle"></i>&nbsp;
                            </a>
                        </div>
                    </div>


                </div>

            </div>
        </div>

        @dispatchEvent('beforeEndRightColumn', ['ticket' => $ticket])
    </div>
</div>

<script>

    jQuery(document).ready(function(){
        //Set accordion states
        //All accordions start open
        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }

        Prism.highlightAll();
    });

</script>

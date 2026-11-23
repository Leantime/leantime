<input type="hidden" value="{{ $ticket->id }}" name="id" autocomplete="off" readonly/>

<div class="row">
    <div class="col-md-9">
        <div class="row marginBottom">
            <div class="col-md-12">

                <div class="form-group">
                    <x-global::forms.text-input value="{{ $ticket->headline }}" name="headline" variant="headline" autocomplete="off" style="width:99%; margin-bottom:10px;" placeholder="{{ __('input.placeholders.enter_title_of_todo') }}" />
                </div>
                {{-- Inline fields: 100px label column, indented 15px like the title above. --}}
                <x-global::forms.field layout="inline" :labelWidth="100" for="status-select" :label="__('label.todo_status')" class="tw-w-3/5 tw-pl-m">
                    <x-global::forms.select enhanced
                        id="status-select"
                        name="status"
                        data-placeholder="{{ isset($ticket->status) ? $statusLabels[$ticket->status]['name'] ?? '' : '' }}"
                    >
                        @foreach ($statusLabels as $key => $label)
                            <option value="{{ $key }}"
                                @if ($ticket->status == $key) selected='selected' @endif
                            >{{ $label['name'] }}</option>
                        @endforeach
                    </x-global::forms.select>
                </x-global::forms.field>

                <x-global::forms.field layout="inline" :labelWidth="100" for="priority" :label="__('label.priority')" class="tw-w-3/5 tw-pl-m">
                    <x-global::forms.select enhanced id='priority' name='priority'>
                        <option value="">{!! __('label.priority_not_defined') !!}</option>
                        @foreach ($priorities as $priorityKey => $priorityValue)
                            <option value="{{ $priorityKey }}"
                                @if ($priorityKey == $ticket->priority) selected='selected' @endif
                            >{{ $priorityValue }}</option>
                        @endforeach
                    </x-global::forms.select>
                </x-global::forms.field>

                <x-global::forms.field layout="inline" :labelWidth="100" for="storypoints" :label="__('label.effort')" class="tw-w-3/5 tw-pl-m">
                    <x-global::forms.select enhanced id='storypoints' name='storypoints'>
                        <option value="">{!! __('label.effort_not_defined') !!}</option>
                        @foreach ($efforts as $effortKey => $effortValue)
                            <option value="{{ $effortKey }}"
                                @if ($effortKey == $ticket->storypoints) selected='selected' @endif
                            >{{ $effortValue }}</option>
                        @endforeach
                    </x-global::forms.select>
                </x-global::forms.field>

                <x-global::forms.field layout="inline" :labelWidth="100" for="editorId" :label="__('label.editor')" class="tw-w-3/5 tw-pl-m">
                    <div class="lt-field-row">
                        <x-global::forms.select enhanced data-placeholder="{{ __('label.filter_by_user') }}"
                                name="editorId" id="editorId" class="user-select">
                            <option value="">{!! __('label.not_assigned_to_user') !!}</option>
                            @foreach ($users as $userRow)
                                <option value="{{ $userRow['id'] }}"
                                    @if ($ticket->editorId == $userRow['id']) selected='selected' @endif
                                >{{ $userRow['firstname'] . ' ' . $userRow['lastname'] }}</option>
                            @endforeach
                        </x-global::forms.select>
                        @can('tickets.edit', $ticket->projectId ?: null)
                            <a href="javascript:void(0);" onclick="leantime.selectController.setValue(document.getElementById('editorId'), '{{ (int) session('userdata.id') }}');">{!! __('label.assign_to_me') !!}</a>
                        @endcan
                    </div>
                </x-global::forms.field>

                <x-global::forms.field layout="inline" :labelWidth="100" for="collaborators" :label="__('label.collaborators')" class="tw-w-3/5 tw-pl-m">
                    {{-- An empty multi-select posts nothing; this keeps "collaborators" in the
                         request so clearing them all still clears them (omitted = preserved). --}}
                    <input type="hidden" name="collaborators[]" value="" />
                    <x-global::forms.select enhanced data-placeholder="{{ __('label.filter_by_user') }}"
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
                    </x-global::forms.select>
                </x-global::forms.field>

                <x-global::forms.field layout="inline" :labelWidth="100" for="deadline" :label="__('label.due_date')" class="tw-w-3/5 tw-pl-m">
                    <div class="lt-field-row">
                        <input type="text" class="field-width-sm dates" id="deadline" autocomplete="off"
                               value="{{ format($ticket->dateToFinish)->date() }}"
                               name="dateToFinish" placeholder="{{ __('language.dateformat') }}"/>
                        <input type="time" class="field-width-sm timepicker" id="dueTime" autocomplete="off"
                               value="{{ format($ticket->dateToFinish)->time24() }}"
                               name="timeToFinish"/>
                        @dispatchEvent('afterDates', ['ticket' => $ticket])
                    </div>
                </x-global::forms.field>

                <x-global::forms.field layout="inline" :labelWidth="100" :label="__('label.tags')" class="tw-w-3/5 tw-pl-m">
                    <x-global::forms.tags :value="$ticket->tags" />
                </x-global::forms.field>

                <div class="form-group tw-mt-m" id="descriptionEditor">
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

                    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="saveTicket" class="saveTicketBtn" />
                    <x-global::forms.button tag="input" inputType="submit" variant="outline" name="saveAndCloseTicket" onclick="jQuery('#saveAndCloseButton').val('1');" :labelText="__('buttons.save_and_close')" />
                </div>
            </div>
        </div>

        @if ($ticket->id)
            <br />
            <hr />
            @dispatchEvent('beforeSubtasks', ['ticketId' => $ticket->id])
            <x-global::elements.section-header icon="fa-solid fa-sitemap">{!! __('subtitles.subtasks') !!}</x-global::elements.section-header>

            <x-global::hx
                wrapperId="ticketSubtasks"
                :for="\Leantime\Domain\Tickets\Hxcontrollers\Subtasks::class"
                :id="$ticket->id"
                trigger="load"
                indicator=".subtaskIndicator"
            />
            <div class="htmx-indicator subtaskIndicator">
                {{ __('label.loadingSubtasks', 'Loading Subtasks ...') }}<br /><br />
            </div>

        <x-global::elements.section-header icon="fa-solid fa-comments">{!! __('subtitles.discussion') !!}</x-global::elements.section-header>

        <div class="row-fluid">
        <form method="post" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}" class="formModal">
            <input type="hidden" name="comment" value="1" />
            @include('comments::submodules.generalComment', ['formUrl' => BASE_URL . '/tickets/showTicket/' . $ticket->id, 'commentProjectId' => $ticket->projectId])
        </form>
        </div>
        @endif
    </div>
    <div class="col-md-3">

        <div class="row marginBottom">
            <div class="col-md-12">
                <x-global::elements.accordion id="tickets-organization" icon="fa fa-folder-open" :title="__('subtitles.organization')" open>

                    <x-global::forms.field for="type" :label="__('label.todo_type')">
                        <x-global::forms.select width="full" enhanced id='type' name='type'>
                            @foreach ($ticketTypes as $types)
                                <option value="{{ strtolower($types) }}"
                                    @if (strtolower($types) == strtolower($ticket->type ?? '')) selected='selected' @endif
                                >{!! __('label.' . strtolower($types)) !!}</option>
                            @endforeach
                        </x-global::forms.select>
                    </x-global::forms.field>

                    <x-global::forms.field for="ticket-project" :label="__('label.project')">
                        <x-global::forms.select width="full" enhanced name="projectId" id="ticket-project">
                            @foreach ($allAssignedprojects as $project)
                                <option value="{{ $project['id'] }}"
                                    @if ($ticket->projectId == $project['id'])
                                        selected
                                    @elseif (session('currentProject') == $project['id'])
                                        selected
                                    @endif
                                >{{ $tpl->escape($project['name']) }}</option>
                            @endforeach
                        </x-global::forms.select>
                    </x-global::forms.field>

                    <x-global::forms.field for="ticket-milestone" :label="__('label.milestone')">
                        <x-global::forms.select width="full" enhanced name="milestoneid" id="ticket-milestone">
                            <option value="">{!! __('label.not_assigned_to_milestone') !!}</option>
                            @foreach ($milestones as $milestoneRow)
                                <option value="{{ $milestoneRow->id }}"
                                    @if ($ticket->milestoneid == $milestoneRow->id) selected='selected' @endif
                                >{{ $tpl->escape($milestoneRow->headline) }}</option>
                            @endforeach
                        </x-global::forms.select>
                    </x-global::forms.field>

                    <x-global::forms.field for="sprint-select" :label="__('label.sprint')">
                        <x-global::forms.select width="full" enhanced id="sprint-select" name="sprint"
                                data-placeholder="{{ $ticket->sprint }}">
                            <option value="">{!! __('label.backlog') !!}</option>
                            @if ($sprints)
                                @foreach ($sprints as $sprintRow)
                                    <option value="{{ $sprintRow->id }}"
                                        @if ($ticket->sprint == $sprintRow->id) selected='selected' @endif
                                    >{{ $sprintRow->name }}@if (! empty($sprintRow->isInherited)) — {{ __('label.program_sprint') }} @endif</option>
                                @endforeach
                            @endif
                        </x-global::forms.select>
                    </x-global::forms.field>

                    <x-global::forms.field for="ticket-related" :label="__('label.related_to')">
                        <x-global::forms.select width="full" enhanced name="dependingTicketId" id="ticket-related">
                            <option value="">{!! __('label.not_related') !!}</option>
                            @if (is_array($ticketParents))
                                @foreach ($ticketParents as $ticketRow)
                                    <option value="{{ $ticketRow->id }}"
                                        @if ($ticket->dependingTicketId == $ticketRow->id) selected='selected' @endif
                                    >{{ $tpl->escape($ticketRow->headline) }}</option>
                                @endforeach
                            @endif
                        </x-global::forms.select>
                    </x-global::forms.field>

                </x-global::elements.accordion>

            </div>
        </div>

        <div class="row marginBottom">
            <div class="col-md-12">
                <x-global::elements.accordion id="tickets-dates" icon="fa fa-calendar" :title="__('subtitles.schedule')" open>
                    <x-global::forms.field for="ticket-edit-from" :label="__('label.working_date_from')">
                        <div class="lt-field-row">
                            <input type="text" class="field-width-sm editFrom" name="editFrom" id="ticket-edit-from" autocomplete="off"
                                   value="{{ format($ticket->editFrom)->date() }}" placeholder="{{ __('language.dateformat') }}"/>
                            <input type="time" class="field-width-sm timepicker" id="timeFrom" autocomplete="off"
                                   value="{{ format($ticket->editFrom)->time24() }}"
                                   name="timeFrom"/>
                        </div>
                    </x-global::forms.field>

                    <x-global::forms.field for="ticket-edit-to" :label="__('label.working_date_to')">
                        <div class="lt-field-row">
                            <input type="text" class="field-width-sm editTo" name="editTo" id="ticket-edit-to" autocomplete="off"
                                   value="{{ format($ticket->editTo)->date() }}" placeholder="{{ __('language.dateformat') }}"/>
                            <input type="time" class="field-width-sm timepicker" id="timeTo" autocomplete="off"
                                   value="{{ format($ticket->editTo)->time24() }}"
                                   name="timeTo"/>
                        </div>
                    </x-global::forms.field>

                    <x-global::forms.field for="ticket-plan-hours">
                        <x-slot:label>{!! __('label.planned_hours') !!} / {!! __('label.estimated_hours_remaining') !!}</x-slot:label>
                        <x-global::forms.text-input value="{{ $ticket->planHours }}" name="planHours" id="ticket-plan-hours" style="width:45px;" />&nbsp;/&nbsp;
                        <x-global::forms.text-input value="{{ $ticket->hourRemaining }}" name="hourRemaining" style="width:45px;" aria-label="{{ strip_tags(__('label.estimated_hours_remaining')) }}" />
                        <a href="javascript:void(0)" class="infoToolTip" data-placement="left" data-toggle="tooltip" data-tippy-content="{{ __('tooltip.how_many_hours_remaining') }}">
                            &nbsp;<i class="fa fa-question-circle"></i>&nbsp;
                        </a>
                    </x-global::forms.field>

                </x-global::elements.accordion>

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

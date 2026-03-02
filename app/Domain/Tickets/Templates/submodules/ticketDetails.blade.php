@php
    $ticket = $tpl->get('ticket');
    $remainingHours = $tpl->get('remainingHours');
    $statusLabels = $tpl->get('statusLabels');
    $ticketTypes = $tpl->get('ticketTypes');
@endphp

<input type="hidden" value="{{ e($ticket->id) }}" name="id" autocomplete="off" readonly/>

<div class="row-fluid">
    <div class="col-md-8">
        <div class="marginBottom">

                <div class="form-group">
                    <x-globals::forms.input :bare="true" type="text" value="{{ e($ticket->headline) }}" name="headline" class="main-title-input" autocomplete="off" style="width:99%; margin-bottom:10px;" placeholder="{{ __('input.placeholders.enter_title_of_todo') }}" />
                </div>

                <div class="form-group" id="descriptionEditor" style="overflow:hidden;">
                    <textarea name="description" id="ticketDescription"
                              class="tiptapComplex">{!! $ticket->description !== null ? htmlentities($ticket->description) : '' !!}</textarea><br/>
                </div>
                <input type="hidden" name="acceptanceCriteria" value=""/>

        </div>

        <div class="sticky-modal-footer">
            <div style="margin-top:15px;">
                <input type="hidden" name="saveTicket" value="1" />
                <input type="hidden" id="saveAndCloseButton" name="saveAndCloseTicket" value="0" />

                <x-globals::forms.button submit type="primary" name="saveTicket" class="saveTicketBtn">{{ __('buttons.save') }}</x-globals::forms.button>
                <x-globals::forms.button submit type="primary" :outline="true" name="saveAndCloseTicket" onclick="jQuery('#saveAndCloseButton').val('1');">{{ __('buttons.save_and_close') }}</x-globals::forms.button>
            </div>
        </div>

        @if($ticket->id)
            <br />
            <hr />
            @dispatchEvent('beforeSubtasks', ['ticketId' => $ticket->id])
            <h4 class="widgettitle title-light"><x-global::elements.icon name="account_tree" /> {{ __('subtitles.subtasks') }}</h4>

            <div
                id="ticketSubtasks"
                hx-get="{{ BASE_URL }}/tickets/subtasks/get?ticketId={{ $ticket->id }}"
                hx-trigger="load, subtasksUpdated from:body"
                hx-select="unset"
                hx-indicator=".subtaskIndicator"
                aria-live="polite"
            ></div>
            <div class="htmx-indicator subtaskIndicator" role="status">
                {{ __('label.loading') }} ...<br /><br />
            </div>

            <h4 class="widgettitle title-light"><x-global::elements.icon name="forum" />{{ __('subtitles.discussion') }}</h4>

            <form method="post" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}" class="formModal">
                <input type="hidden" name="comment" value="1" />
                @php
                    $tpl->assign('formUrl', '' . BASE_URL . '/tickets/showTicket/' . $ticket->id . '');
                    $tpl->displaySubmodule('comments-generalComment');
                @endphp
            </form>
        @endif
    </div>
    <div class="col-md-4">

        {{-- Details Section --}}
        <h4 class="widgettitle title-light">
            <x-global::elements.icon name="star" /> {{ __('subtitles.details') }}
        </h4>

        {{-- Status --}}
        <x-globals::forms.form-field label-text="{{ __('label.todo_status') }}" name="status">
            <x-globals::forms.select
                id="status-select"
                name="status"
                data-placeholder="{{ isset($ticket->status) ? ($statusLabels[$ticket->status]['name'] ?? '') : '' }}"
            >
                @foreach($statusLabels as $key => $label)
                    <option value="{{ $key }}"
                        {{ $ticket->status == $key ? "selected='selected'" : '' }}
                    >{{ e($label['name']) }}</option>
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        {{-- Priority --}}
        <x-globals::forms.form-field label-text="{{ __('label.priority') }}" name="priority">
            <x-globals::forms.select id="priority" name="priority">
                <option value="">{{ __('label.priority_not_defined') }}</option>
                @foreach($tpl->get('priorities') as $priorityKey => $priorityValue)
                    <option value="{{ $priorityKey }}"
                        {{ $priorityKey == $ticket->priority ? "selected='selected'" : '' }}
                    >{{ $priorityValue }}</option>
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        {{-- Effort --}}
        <x-globals::forms.form-field label-text="{{ __('label.effort') }}" name="storypoints">
            <x-globals::forms.select id="storypoints" name="storypoints">
                <option value="">{{ __('label.effort_not_defined') }}</option>
                @foreach($tpl->get('efforts') as $effortKey => $effortValue)
                    <option value="{{ $effortKey }}"
                        {{ $effortKey == $ticket->storypoints ? "selected='selected'" : '' }}
                    >{{ $effortValue }}</option>
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        {{-- Editor (Assigned to) --}}
        <x-globals::forms.form-field label-text="{{ __('label.editor') }}" name="editorId">
            <x-globals::forms.select :bare="true" data-placeholder="{{ __('label.filter_by_user') }}"
                    name="editorId" id="editorId" class="user-select">
                <option value="">{{ __('label.not_assigned_to_user') }}</option>
                @foreach($tpl->get('users') as $userRow)
                    <option value="{{ $userRow['id'] }}"
                        {{ $ticket->editorId == $userRow['id'] ? "selected='selected'" : '' }}
                    >{{ e($userRow['firstname'] . ' ' . $userRow['lastname']) }}</option>
                @endforeach
            </x-globals::forms.select>
            @if($login::userIsAtLeast($roles::$editor))
                <a href="javascript:void(0);" style="display:block; margin-top:4px;" onclick="jQuery('#editorId').val({{ session('userdata.id') }}).trigger('chosen:updated');">{{ __('label.assign_to_me') }}</a>
            @endif
        </x-globals::forms.form-field>

        {{-- Collaborators --}}
        <x-globals::forms.form-field label-text="{{ __('label.collaborators') }}" name="collaborators">
            <x-globals::forms.select :bare="true" data-placeholder="{{ __('label.filter_by_user') }}"
                    name="collaborators[]"
                    id="collaborators"
                    class="user-select"
                    multiple>
                @foreach($tpl->get('users') as $userRow)
                    <option value="{{ $userRow['id'] }}"
                        {{ in_array($userRow['id'], $ticket->collaborators ?? []) ? "selected='selected'" : '' }}
                    >{{ e($userRow['firstname'] . ' ' . $userRow['lastname']) }}</option>
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        {{-- Due Date --}}
        <x-globals::forms.form-field label-text="{{ __('label.due_date') }}" name="dateToFinish">
            <div class="tw:flex tw:gap-2 tw:items-center">
                <x-globals::forms.date name="dateToFinish" id="deadline" value="{{ format($ticket->dateToFinish)->date() }}" placeholder="{{ __('language.dateformat') }}" style="width:110px;" />
                <input type="time" class="timepicker" style="width:120px;" id="dueTime" autocomplete="off"
                       value="{{ format($ticket->dateToFinish)->time24() }}"
                       name="timeToFinish"/>
            </div>
            @dispatchEvent('afterDates', ['ticket' => $ticket])
        </x-globals::forms.form-field>

        {{-- Tags --}}
        <x-globals::forms.form-field label-text="{{ __('label.tags') }}" name="tags">
            <input type="text" value="{{ e($ticket->tags) }}" name="tags" id="tags" />
        </x-globals::forms.form-field>

        {{-- Organization Section --}}
        <h4 class="widgettitle title-light" style="margin-top:20px;">
            <x-global::elements.icon name="folder_open" /> {{ __('subtitles.organization') }}
        </h4>

        {{-- Type --}}
        <x-globals::forms.form-field label-text="{{ __('label.todo_type') }}" name="type">
            <x-globals::forms.select id="type" name="type">
                @foreach($ticketTypes as $types)
                    <option value="{{ strtolower($types) }}"
                        {{ strtolower($types) == strtolower($ticket->type ?? '') ? "selected='selected'" : '' }}
                    >{{ __('label.' . strtolower($types)) }}</option>
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        {{-- Project --}}
        <x-globals::forms.form-field label-text="{{ __('label.project') }}" name="projectId">
            <x-globals::forms.select name="projectId" class="tw:w-full">
                @foreach($allAssignedprojects as $project)
                    <option value="{{ $project['id'] }}"
                        @if($ticket->projectId == $project['id'])
                            selected
                        @elseif(session('currentProject') == $project['id'])
                            selected
                        @endif
                    >{{ e($project['name']) }}</option>
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        {{-- Milestones --}}
        <x-globals::forms.form-field label-text="{{ __('label.milestone') }}" name="milestoneid">
            <x-globals::forms.select name="milestoneid">
                <option value="">{{ __('label.not_assigned_to_milestone') }}</option>
                @foreach($tpl->get('milestones') as $milestoneRow)
                    <option value="{{ $milestoneRow->id }}"
                        {{ ($ticket->milestoneid == $milestoneRow->id) ? "selected='selected'" : '' }}
                    >{{ e($milestoneRow->headline) }}</option>
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        {{-- Sprint --}}
        <x-globals::forms.form-field label-text="{{ __('label.sprint') }}" name="sprint">
            <x-globals::forms.select id="sprint-select" name="sprint"
                    data-placeholder="{{ $ticket->sprint }}">
                <option value="">{{ __('label.backlog') }}</option>
                @if($tpl->get('sprints'))
                    @foreach($tpl->get('sprints') as $sprintRow)
                        <option value="{{ $sprintRow->id }}"
                            {{ $ticket->sprint == $sprintRow->id ? "selected='selected'" : '' }}
                        >{{ e($sprintRow->name) }}</option>
                    @endforeach
                @endif
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        {{-- Related --}}
        <x-globals::forms.form-field label-text="{{ __('label.related_to') }}" name="dependingTicketId">
            <x-globals::forms.select name="dependingTicketId">
                <option value="">{{ __('label.not_related') }}</option>
                @if(is_array($tpl->get('ticketParents')))
                    @foreach($tpl->get('ticketParents') as $ticketRow)
                        <option value="{{ $ticketRow->id }}"
                            {{ ($ticket->dependingTicketId == $ticketRow->id) ? "selected='selected'" : '' }}
                        >{{ e($ticketRow->headline) }}</option>
                    @endforeach
                @endif
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        {{-- Schedule Section --}}
        <h4 class="widgettitle title-light" style="margin-top:20px;">
            <x-global::elements.icon name="calendar_today" /> {{ __('subtitles.schedule') }}
        </h4>

        <div class="form-group">
            <label class="control-label">{{ __('label.working_date_from') }}</label>
            <div class="">
                <x-globals::forms.input :bare="true" type="text" class="editFrom" style="width:100px;" name="editFrom" autocomplete="off"
                       value="{{ format($ticket->editFrom)->date() }}" placeholder="{{ __('language.dateformat') }}"/>
                <x-globals::forms.input :bare="true" type="time" class="timepicker" style="width:120px;" id="timeFrom" autocomplete="off"
                       value="{{ format($ticket->editFrom)->time24() }}"
                       name="timeFrom"/>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label">{{ __('label.working_date_to') }}</label>
            <div class="">
                <x-globals::forms.input :bare="true" type="text" class="editTo" style="width:100px;" name="editTo" autocomplete="off"
                       value="{{ format($ticket->editTo)->date() }}" placeholder="{{ __('language.dateformat') }}"/>
                <x-globals::forms.input :bare="true" type="time" class="timepicker" style="width:120px;" id="timeTo" autocomplete="off"
                       value="{{ format($ticket->editTo)->time24() }}"
                       name="timeTo"/>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label">{{ __('label.planned_hours') }} / {{ __('label.estimated_hours_remaining') }}</label>
            <div class="">
                <x-globals::forms.input :bare="true" value="{{ e($ticket->planHours) }}" name="planHours" style="width:45px;" />&nbsp;/&nbsp;
                <x-globals::forms.input :bare="true" value="{{ e($ticket->hourRemaining) }}" name="hourRemaining" style="width:45px;" />
                <a href="javascript:void(0)" class="infoToolTip" data-placement="left" data-toggle="tooltip" data-tippy-content="{{ __('tooltip.how_many_hours_remaining') }}">
                    &nbsp;<x-global::elements.icon name="help" />&nbsp;
                </a>
            </div>
        </div>

        @dispatchEvent('beforeEndRightColumn', ['ticket' => $ticket])
    </div>
</div>

<script>
    jQuery(document).ready(function(){
        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }
    });

    Prism.highlightAll();
</script>

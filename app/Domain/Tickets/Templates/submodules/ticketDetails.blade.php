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
                    <x-globals::forms.text-input :bare="true" type="text" value="{{ e($ticket->headline) }}" name="headline" class="main-title-input" autocomplete="off" style="width:99%; margin-bottom:10px;" placeholder="{{ __('input.placeholders.enter_title_of_todo') }}" />
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
                hx-trigger="intersect once, subtasksUpdated from:body"
                hx-target="this"
                hx-select="unset"
                hx-swap="innerHTML"
                hx-indicator=".subtaskIndicator"
                aria-live="polite"
            ></div>
            <div class="htmx-indicator subtaskIndicator" role="status">
                {{ __('label.loading') }} ...<br /><br />
            </div>

        @endif
    </div>
    <div class="col-md-4">

        {{-- Details Section --}}
        <h4 class="widgettitle title-light">
            <x-global::elements.icon name="star" /> {{ __('subtitles.details') }}
        </h4>

        {{-- Status chip — patches live via HTMX on selection --}}
        <div class="form-group">
            <x-tickets::chips.status-select
                :ticket="$ticket"
                :statuses="$statusLabels"
                :show-label="true"
            />
        </div>

        {{-- Priority chip --}}
        <div class="form-group">
            <x-tickets::chips.priority-select
                :ticket="$ticket"
                :priorities="$tpl->get('priorities')"
                :show-label="true"
            />
        </div>

        {{-- Effort chip --}}
        <div class="form-group">
            <x-tickets::chips.effort-select
                :ticket="$ticket"
                :efforts="$tpl->get('efforts')"
                :show-label="true"
            />
        </div>

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
                        {{ in_array($userRow['id'], (array)($ticket->collaborators ?? [])) ? "selected='selected'" : '' }}
                    >{{ e($userRow['firstname'] . ' ' . $userRow['lastname']) }}</option>
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        {{-- Due Date --}}
        <x-globals::forms.form-field label-text="{{ __('label.due_date') }}" name="dateToFinish">
            <div class="tw:flex tw:gap-2 tw:items-center">
                <x-globals::forms.date name="dateToFinish" id="deadline" value="{{ format($ticket->dateToFinish)->date() }}" placeholder="{{ __('language.dateformat') }}" style="flex:1; min-width:0;" />
                <input type="time" class="timepicker" style="flex:1; min-width:0;" id="dueTime" autocomplete="off"
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

        {{-- Type chip --}}
        <div class="form-group">
            <x-tickets::chips.type-select
                :ticket="$ticket"
                :ticket-types="$ticketTypes"
                :show-label="true"
            />
        </div>

        {{-- Project (keep as plain select — project changes need full-page context) --}}
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

        {{-- Milestone chip --}}
        <div class="form-group">
            <x-tickets::chips.milestone-select
                :ticket="$ticket"
                :milestones="$tpl->get('milestones')"
                :show-label="true"
            />
        </div>

        {{-- Sprint chip --}}
        <div class="form-group">
            <x-tickets::chips.sprint-select
                :ticket="$ticket"
                :sprints="$tpl->get('sprints') ?: []"
                :show-label="true"
            />
        </div>

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
            <div class="tw:flex tw:gap-2 tw:items-center">
                <x-globals::forms.text-input :bare="true" type="text" class="editFrom" style="flex:1; min-width:0;" name="editFrom" autocomplete="off"
                       value="{{ format($ticket->editFrom)->date() }}" placeholder="{{ __('language.dateformat') }}"/>
                <x-globals::forms.text-input :bare="true" type="time" class="timepicker" style="flex:1; min-width:0;" id="timeFrom" autocomplete="off"
                       value="{{ format($ticket->editFrom)->time24() }}"
                       name="timeFrom"/>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label">{{ __('label.working_date_to') }}</label>
            <div class="tw:flex tw:gap-2 tw:items-center">
                <x-globals::forms.text-input :bare="true" type="text" class="editTo" style="flex:1; min-width:0;" name="editTo" autocomplete="off"
                       value="{{ format($ticket->editTo)->date() }}" placeholder="{{ __('language.dateformat') }}"/>
                <x-globals::forms.text-input :bare="true" type="time" class="timepicker" style="flex:1; min-width:0;" id="timeTo" autocomplete="off"
                       value="{{ format($ticket->editTo)->time24() }}"
                       name="timeTo"/>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label">{{ __('label.planned_hours') }} / {{ __('label.estimated_hours_remaining') }}</label>
            <div class="">
                <x-globals::forms.text-input :bare="true" value="{{ e($ticket->planHours) }}" name="planHours" style="width:45px;" />&nbsp;/&nbsp;
                <x-globals::forms.text-input :bare="true" value="{{ e($ticket->hourRemaining) }}" name="hourRemaining" style="width:45px;" />
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

    if (typeof Prism !== 'undefined') { Prism.highlightAll(); }
</script>

@php
    $ticket = $tpl->get('ticket');
    $remainingHours = $tpl->get('remainingHours');
    $statusLabels = $tpl->get('statusLabels');
    $ticketTypes = $tpl->get('ticketTypes');
@endphp

<div>

        <div class="marginBottom">

                {{-- Type --}}
                <div class="form-group">
                    <label class="control-label">{{ __('label.todo_type') }}</label>
                    <div class="">
                        <x-globals::forms.select id="type" name="type">
                            @foreach($ticketTypes as $types)
                                <option value="{{ strtolower($types) }}"
                                    {{ strtolower($types) == strtolower($ticket->type ?? '') ? "selected='selected'" : '' }}
                                >{{ __('label.' . strtolower($types)) }}</option>
                            @endforeach
                        </x-globals::forms.select><br/>
                    </div>
                </div>

        </div>

        <div class="marginBottom">
                <h5 class="accordionTitle" id="accordion_link_tickets-organization" style="padding-bottom:15px; font-size:var(--font-size-l)">
                    <a href="javascript:void(0)"
                       class="accordion-toggle"
                       id="accordion_toggle_tickets-organization"
                       onclick="leantime.snippets.accordionToggle('tickets-organization');">
                            <i class="fa fa-angle-down"></i>
                            <span class="fa fa-folder-open"></span>
                            {{ __('subtitles.organization') }}
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-organization" style="padding-left:0">

                    {{-- Project --}}
                    <div class="form-group">
                        <label class="control-label">{{ __('label.project') }}</label>
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
                    </div>

                    {{-- Milestones --}}
                    <div class="form-group">
                        <label class="control-label">{{ __('label.milestone') }}</label>
                        <div class="">
                            <div class="form-group">
                                <x-globals::forms.select name="milestoneid">
                                    <option value="">{{ __('label.not_assigned_to_milestone') }}</option>
                                    @foreach($tpl->get('milestones') as $milestoneRow)
                                        <option value="{{ $milestoneRow->id }}"
                                            {{ ($ticket->milestoneid == $milestoneRow->id) ? "selected='selected'" : '' }}
                                        >{{ e($milestoneRow->headline) }}</option>
                                    @endforeach
                                </x-globals::forms.select>
                            </div>
                        </div>
                    </div>

                    {{-- Sprint --}}
                    <div class="form-group">
                        <label class="control-label">{{ __('label.sprint') }}</label>
                        <div class="">
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
                        </div>
                    </div>

                    {{-- Related --}}
                    <div class="form-group">
                        <label class="control-label">{{ __('label.related_to') }}</label>
                        <div class="">
                            <div class="form-group">
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
                            </div>
                        </div>
                    </div>
                </div>

        </div>

        <div class="marginBottom">
                <h5 class="accordionTitle" id="accordion_link_tickets-dates" style="padding-bottom:15px; font-size:var(--font-size-l)">
                    <a href="javascript:void(0)"
                       class="accordion-toggle"
                       id="accordion_toggle_tickets-dates"
                       onclick="leantime.snippets.accordionToggle('tickets-dates');">
                        <i class="fa fa-angle-down"></i>
                        <span class="fa fa-calendar"></span>
                        {{ __('subtitles.dates') }}
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-dates" style="padding-left:0">
                    <div class="form-group">
                        <label class="control-label">{{ __('label.working_date_from') }}</label>
                        <div class="">
                            <x-globals::forms.input :bare="true" type="text" class="editFrom" style="width:100px;" name="editFrom" autocomplete="off"
                                   value="{{ format($ticket->editFrom)->date() }}" placeholder="{{ __('language.dateformat') }}" />
                            <x-globals::forms.input :bare="true" type="time" class="timepicker" style="width:120px;" id="timeFrom" autocomplete="off"
                                   value="{{ format($ticket->editFrom)->time24() }}"
                                   name="timeFrom" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label">{{ __('label.working_date_to') }}</label>
                        <div class="">
                            <x-globals::forms.input :bare="true" type="text" class="editTo" style="width:100px;" name="editTo" autocomplete="off"
                                   value="{{ format($ticket->editTo)->date() }}" placeholder="{{ __('language.dateformat') }}" />
                            <x-globals::forms.input :bare="true" type="time" class="timepicker" style="width:120px;" id="timeTo" autocomplete="off"
                                   value="{{ format($ticket->editTo)->time24() }}"
                                   name="timeTo" />
                        </div>
                    </div>

                </div>

        </div>

        <div class="marginBottom">

                <h5 class="accordionTitle" id="accordion_link_tickets-timetracking" style="padding-bottom:15px; font-size:var(--font-size-l)">
                    <a href="javascript:void(0)"
                       class="accordion-toggle"
                       id="accordion_toggle_tickets-timetracking"
                       onclick="leantime.snippets.accordionToggle('tickets-timetracking');">
                        <i class="fa fa-angle-down"></i>
                        <span class="fa-regular fa-clock"></span>
                        {{ __('subtitle.time_tracking') }}
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-timetracking" style="padding-left:0">

                    <div class="form-group">
                        <label class="control-label">{{ __('label.planned_hours') }}</label>
                        <div class="">
                            <x-globals::forms.input :bare="true" type="text" value="{{ e($ticket->planHours) }}" name="planHours" style="width:90px;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label">{{ __('label.estimated_hours_remaining') }}</label>
                        <div class="">
                            <x-globals::forms.input :bare="true" type="text" value="{{ e($ticket->hourRemaining) }}" name="hourRemaining" style="width:90px;" />
                            <a href="javascript:void(0)" class="infoToolTip" data-placement="left" data-toggle="tooltip" data-tippy-content="{{ __('tooltip.how_many_hours_remaining') }}">
                                &nbsp;<i class="fa fa-question-circle"></i>&nbsp;
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label">{{ __('label.booked_hours') }}</label>
                        <div class="">
                            <x-globals::forms.input :bare="true" type="text" :disabled="true"
                                   name="bookedHours" value="{{ $tpl->get('timesheetsAllHours') }}" style="width:90px;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label">{{ __('label.actual_hours_remaining') }}</label>
                        <div class="">
                            <x-globals::forms.input :bare="true" type="text" :disabled="true" name="actualHoursRemaining" value="{{ $remainingHours }}" style="width:90px;" />
                        </div>
                    </div>

                </div>
        </div>

        @dispatchEvent('beforeEndRightColumn', ['ticket' => $ticket])
</div>

<script>
    jQuery(document).ready(function(){
        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }
    });

    jQuery(".viewDescription").click(function(e){
        if(!jQuery(e.target).is("a")) {
            e.stopPropagation();
            jQuery(this).hide();
            jQuery('#descriptionEditor').show('fast');
        }
    });

    // Initialize recurring task dropdown
    jQuery(document).ready(function($) {
        $('.recurring-toggle').click(function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $dropdown = $('#recurringTaskForm');
            if (!$dropdown.hasClass('loaded')) {
                $dropdown.load('{{ BASE_URL }}/hx/recurringTasks/form?entityId={{ $ticket->id }}&module=tickets', function() {
                    $dropdown.addClass('loaded');
                });
            }

            $dropdown.toggleClass('show');
        });

        $(document).click(function(e) {
            if (!$(e.target).closest('.recurring-dropdown').length) {
                $('.recurring-dropdown').removeClass('show');
            }
        });
    });

    Prism.highlightAll();
</script>

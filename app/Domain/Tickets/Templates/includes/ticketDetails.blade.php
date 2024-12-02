<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
$remainingHours = $tpl->get('remainingHours');
$statusLabels  = $tpl->get('statusLabels');
$ticketTypes = $tpl->get('ticketTypes');

?>
<input type="hidden" value="<?php $tpl->e($ticket->id); ?>" name="id" autocomplete="off" readonly/>

<div class="row">
    <div class="col-md-9">
        <div class="row marginBottom">
            <div class="col-md-12">

                <div class="form-group">
                    <input type="text" value="<?php $tpl->e($ticket->headline); ?>" name="headline" class="main-title-input " autocomplete="off" style="width:99%;" placeholder="<?=$tpl->__('input.placeholders.enter_title_of_todo')?>"/>
                </div>

                <div class="form-group">

                </div>

                <div class="viewDescription mce-content-body">
                    <div class="pl-[9px]">
                        <?php echo $tpl->escapeMinimal($ticket->description); ?>
                    </div>
                </div>
                <div class="form-group" id="descriptionEditor" style="display:none;">
                    <textarea name="description" id="ticketDescription"
                              class="complexEditor"><?php echo $ticket->description !== null ? htmlentities($ticket->description) : ''; ?></textarea><br/>
                </div>
                <input type="hidden" name="acceptanceCriteria" value=""/>

            </div>
        </div>
        <div class="row">
            <div class="col-md-12" style="margin-top:15px;">
                <input type="hidden" name="saveTicket" value="1" />
                <input type="hidden" id="saveAndCloseButton" name="saveAndCloseTicket" value="0" />


                <input type="submit" name="saveTicket" value="{{ __("buttons.save") }}"/>
                <input type="submit" name="saveAndCloseTicket" onclick="jQuery('#saveAndCloseButton').val('1');" value="{{ __("buttons.save_and_close") }}"/>
            </div>
        </div>

        <?php if ($ticket->id) {?>
            <br />
            <hr />
            <?php $tpl->dispatchTplEvent("beforeSubtasks", ["ticketId" => $ticket->id]); ?>
            <h4 class="widgettitle title-light"><i class="fa-solid fa-sitemap"></i> {{ __("subtitles.subtasks") }}</h4>



        <h4 class="widgettitle title-light"><span
                    class="fa-solid fa-comments"></span>{{ __("subtitles.discussion") }}</h4>

        <div class="row-fluid">
        <form method="post" action="{{ BASE_URL }}/tickets/showTicket/<?php echo $ticket->id; ?>" class="formModal">
            <input type="hidden" name="comment" value="1" />
            @include("comments::includes.generalComment", ["formUrl" => BASE_URL . "/tickets/showTicket/" . $ticket->id])
        </form>
        </div>
        <?php } ?>
    </div>
    <div class="col-md-3">

        <div class="row marginBottom">
            <div class="col-md-12">

                <div class="form-group">
                    <label class="control-label"><?=$tpl->__('label.project') ?></label>
                    <x-global::forms.select name="projectId" class="w-full" labelText="{!! __('label.project') !!}">
                        @foreach($allAssignedprojects as $project)
                            <x-global::forms.select.select-option 
                                value="{{ $project['id'] }}"
                                :selected="$ticket->projectId == $project['id'] || session('currentProject') == $project['id']">
                                {!! $project['name'] !!}
                            </x-global::forms.select.select-option>
                        @endforeach
                    </x-global::forms.select>                    
                </div>

                <div class="form-group">
                    <label class="control-label">{{ __("label.related_to") }}</label>
                    <div class="">
                        <div class="form-group">
                            <x-global::forms.select name="dependingTicketId" class="span11">
                                <x-global::forms.select.select-option value="">
                                    {!! __('label.not_related') !!}
                                </x-global::forms.select.select-option>
                            
                                @if (is_array($ticketParents = $tpl->get('ticketParents')))
                                    @foreach ($ticketParents as $ticketRow)
                                        <x-global::forms.select.select-option 
                                            value="{{ $ticketRow->id }}"
                                            :selected="$ticket->dependingTicketId == $ticketRow->id">
                                            {!! $tpl->escape($ticketRow->headline) !!}
                                        </x-global::forms.select.select-option>
                                    @endforeach
                                @endif
                            </x-global::forms.select>
                            
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="">
                        <x-global::forms.select 
                        id="status-select" 
                        class="span11" 
                        name="status" 
                        labelText="{!! __('label.todo_status') !!}"
                        :placeholder="isset($ticket->status) ? ($statusLabels[$ticket->status]['name'] ?? '') : ''"
                    >
                        @foreach ($statusLabels as $key => $label)
                            <x-global::forms.select.select-option 
                                value="{{ $key }}" 
                                :selected="$ticket->status == $key">
                                {!! $tpl->escape($label['name']) !!}
                            </x-global::forms.select.select-option>
                        @endforeach
                    </x-global::forms.select>
                    
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label">{{ __("label.todo_type") }}</label>
                    <div class="">
                        <x-global::forms.select id="type" name="type" class="span11">
    @foreach ($ticketTypes as $types)
        <x-global::forms.select.select-option 
            value="{{ strtolower($types) }}" 
            :selected="strtolower($types) == strtolower($ticket->type ?? '')">
            {!! __('label.' . strtolower($types)) !!}
        </x-global::forms.select.select-option>
    @endforeach
</x-global::forms.select>
<br/>

                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label">{{ __("label.priority") }}</label>
                    <div class="">
                        <x-global::forms.select id="priority" name="priority" class="span11">
                            <x-global::forms.select.select-option value="">
                                {!! __('label.priority_not_defined') !!}
                            </x-global::forms.select.select-option>
                        
                            @foreach ($tpl->get('priorities') as $priorityKey => $priorityValue)
                                <x-global::forms.select.select-option 
                                    value="{{ $priorityKey }}" 
                                    :selected="$priorityKey == $ticket->priority">
                                    {!! $priorityValue !!}
                                </x-global::forms.select.select-option>
                            @endforeach
                        </x-global::forms.select>
                        
                    </div>
                </div>
                <div class="form-group">
                    <x-global::forms.select 
                    id="storypoints" 
                    name="storypoints" 
                    class="span11" 
                    labelText="{!! __('label.effort') !!}"
                >
                    <x-global::forms.select.select-option value="">
                        {!! __('label.effort_not_defined') !!}
                    </x-global::forms.select.select-option>
                
                    @foreach ($tpl->get('efforts') as $effortKey => $effortValue)
                        <x-global::forms.select.select-option 
                            value="{{ $effortKey }}" 
                            :selected="$effortKey == $ticket->storypoints">
                            {!! $effortValue !!}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
                
                </div>
            </div>
        </div>
        <div class="row marginBottom">
            <div class="col-md-12">
                <h5 class="accordionTitle" id="accordion_link_tickets-organization" style="padding-bottom:15px; font-size:var(--font-size-l)">
                    <a href="javascript:void(0)"
                       class="accordion-toggle"
                       id="accordion_toggle_tickets-organization"
                       onclick="leantime.snippets.accordionToggle('tickets-organization');">
                            <i class="fa fa-angle-down"></i>
                            <span class="fa fa-folder-open"></span>
                            {{ __("subtitles.organization") }}
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-organization" style="padding-left:0">

                    <div class="form-group">
                        <x-global::forms.select 
                        name="milestoneid" 
                        class="span11" 
                        labelText="{!! __('label.milestone') !!}"
                    >
                        <x-global::forms.select.select-option value="">
                            {!! __('label.not_assigned_to_milestone') !!}
                        </x-global::forms.select.select-option>
                    
                        @foreach ($tpl->get('milestones') as $milestoneRow)
                            <x-global::forms.select.select-option 
                                value="{{ $milestoneRow->id }}" 
                                :selected="$ticket->milestoneid == $milestoneRow->id">
                                {!! $tpl->escape($milestoneRow->headline) !!}
                            </x-global::forms.select.select-option>
                        @endforeach
                    </x-global::forms.select>
                    
                    </div>

                    <div class="form-group">
                        <x-global::forms.select 
                        id="sprint-select" 
                        name="sprint" 
                        class="span11" 
                        labelText="{!! __('label.sprint') !!}" 
                        :placeholder="$ticket->sprint"
                    >
                        <x-global::forms.select.select-option value="">
                            {!! __('label.backlog') !!}
                        </x-global::forms.select.select-option>
                    
                        @if ($tpl->get('sprints'))
                            @foreach ($tpl->get('sprints') as $sprintRow)
                                <x-global::forms.select.select-option 
                                    value="{{ $sprintRow->id }}" 
                                    :selected="$ticket->sprint == $sprintRow->id">
                                    {!! $tpl->escape($sprintRow->name) !!}
                                </x-global::forms.select.select-option>
                            @endforeach
                        @endif
                    </x-global::forms.select>
                    
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="row marginBottom">
            <div class="col-md-12">
                <h5 class="accordionTitle" id="accordion_link_tickets-people" style="padding-bottom:15px; font-size:var(--font-size-l)">
                    <a href="javascript:void(0)"
                       class="accordion-toggle"
                       id="accordion_toggle_tickets-people"
                       onclick="leantime.snippets.accordionToggle('tickets-people');">
                        <i class="fa fa-angle-down"></i>
                        <span class="fa fa-group"></span>
                        {{ __("subtitle.people") }}
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-people" style="padding-left:0">

                    <div class="form-group">
                        <label class="control-label">{{ __("label.author") }}</label>
                        <div class="">
                            <input type="text" disabled="disabled" style="width:175px;"
                                   value="<?php $tpl->e($ticket->userFirstname); ?> <?php $tpl->e($ticket->userLastname); ?>"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <x-global::forms.select 
                        name="editorId" 
                        id="editorId" 
                        class="user-select span11" 
                        :placeholder="__('label.filter_by_user')" 
                        labelText="{!! __('label.editor') !!}" 
                        style="width:175px;"
                    >
                        <x-global::forms.select.select-option value="">
                            {!! __('label.not_assigned_to_user') !!}
                        </x-global::forms.select.select-option>
                    
                        @foreach ($tpl->get('users') as $userRow)
                            <x-global::forms.select.select-option 
                                value="{{ $userRow['id'] }}" 
                                :selected="$ticket->editorId == $userRow['id']">
                                {!! $tpl->escape($userRow['firstname'] . ' ' . $userRow['lastname']) !!}
                            </x-global::forms.select.select-option>
                        @endforeach
                    </x-global::forms.select>
                    
                    <br />
                    
                    @if ($login::userIsAtLeast($roles::$editor))
                        <small style="margin-top:-5px; display:block">
                            <a href="javascript:void(0);" onclick="jQuery('#editorId').val({{ session('userdata.id') }}).trigger('chosen:updated');">
                                {!! __('label.assign_to_me') !!}
                            </a>
                        </small>
                    @endif
                    
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
                        {{ __("subtitles.dates") }}
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_tickets-dates" style="padding-left:0">

                    <div class="form-group">
                        <label class="control-label">{{ __("label.ticket_date") }}</label>
                        <div class="">

                            <input type="text" class="dates" style="width:200px;" id="submittedDate" disabled="disabled"
                                   value="<?=format($ticket->date)->date(); ?>" name="date"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label">{{ __("label.due_date") }}</label>
                        <div class="">
                            <input type="text" class="dates" style="width:100px;" id="deadline" autocomplete="off"
                                   value="<?=format($ticket->dateToFinish)->date(); ?>"
                                   name="dateToFinish" placeholder="<?=$tpl->__('language.dateformat') ?>"/>

                            <input type="time" class="timepicker" style="width:120px;" id="dueTime" autocomplete="off"
                                   value="<?=format($ticket->dateToFinish)->time24(); ?>"
                                   name="timeToFinish"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label">{{ __("label.working_date_from") }}</label>
                        <div class="">
                            <input type="text" class="editFrom" style="width:100px;" name="editFrom" autocomplete="off"
                                   value="<?=format($ticket->editFrom)->date(); ?>" placeholder="<?=$tpl->__('language.dateformat') ?>"/>
                            <input type="time" class="timepicker" style="width:120px;" id="timeFrom" autocomplete="off"
                                   value="<?=format($ticket->editFrom)->time24(); ?>"
                                   name="timeFrom"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label">{{ __("label.working_date_to") }}</label>
                        <div class="">
                            <input type="text" class="editTo" style="width:100px;" name="editTo" autocomplete="off"
                                   value="<?=format($ticket->editTo)->date() ?>" placeholder="<?=$tpl->__('language.dateformat') ?>"/>
                            <input type="time" class="timepicker" style="width:120px;" id="timeTo" autocomplete="off"
                                   value="<?=format($ticket->editTo)->time24() ?>"
                                   name="timeTo"/>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <div class="row marginBottom">
            <div class="col-md-12">

                <h5 class="accordionTitle" id="accordion_link_tickets-timetracking" style="padding-bottom:15px; font-size:var(--font-size-l)">
                    <a href="javascript:void(0)"
                       class="accordion-toggle"
                       id="accordion_toggle_tickets-timetracking"
                       onclick="leantime.snippets.accordionToggle('tickets-timetracking');">
                        <i class="fa fa-angle-down"></i>
                        <span class="fa-regular fa-clock"></span>
                        {{ __("subtitle.time_tracking") }}
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-timetracking" style="padding-left:0">

                    <div class="form-group">
                        <label class=" control-label">{{ __("label.planned_hours") }}</label>
                        <div class="">
                            <input type="text" value="<?php $tpl->e($ticket->planHours); ?>" name="planHours" style="width:90px;"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label">{{ __("label.estimated_hours_remaining") }}</label>
                        <div class="">
                            <input type="text" value="<?php $tpl->e($ticket->hourRemaining); ?>" name="hourRemaining" style="width:90px;"/>
                            <a href="javascript:void(0)" class="infoToolTip" data-placement="left" data-toggle="tooltip" data-tippy-content="{{ __("tooltip.how_many_hours_remaining") }}">
                                &nbsp;<i class="fa fa-question-circle"></i>&nbsp;
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label">{{ __("label.booked_hours") }}</label>
                        <div class="">
                            <input type="text" disabled="disabled"
                                   value="<?php echo $tpl->get('timesheetsAllHours'); ?>" style="width:90px;"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label">{{ __("label.actual_hours_remaining") }}</label>
                        <div class="">
                            <input type="text" disabled="disabled" value="<?php echo $remainingHours; ?>" style="width:90px;"/>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <?php $tpl->dispatchTplEvent('beforeEndRightColumn', ['ticket' => $ticket]); ?>
    </div>
</div>

<script>

    jQuery(document).ready(function(){
        //Set accordion states
        //All accordions start open
        leantime.editorController.initComplexEditor();
        tinymce.activeEditor.hide()
    });

    leantime.editorController.initComplexEditor();

    jQuery(".viewDescription").click(function(e){

        if(!jQuery(e.target).is("a")) {
            e.stopPropagation();
            jQuery(this).hide();
            jQuery('#descriptionEditor').show('fast',
                function() {
                    tinymce.activeEditor.show();
                }
            );
        }
    });

    Prism.highlightAll();

</script>


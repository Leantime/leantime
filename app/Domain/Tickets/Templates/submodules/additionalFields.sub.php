<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
$remainingHours = $tpl->get('remainingHours');
$statusLabels = $tpl->get('statusLabels');
$ticketTypes = $tpl->get('ticketTypes');

?>

<div class="row">

    <div class="col-md-12">

        <div class="row marginBottom">
            <div class="col-md-12">




                <!-- Type -->
                <div class="form-group">
                    <label class="control-label"><?php echo $tpl->__('label.todo_type'); ?></label>
                    <div class="">
                        <select id='type' name='type' class="span11">
                            <?php foreach ($ticketTypes as $types) {
                                echo "<option value='".strtolower($types)."' ";
                                if (strtolower($types) == strtolower($ticket->type ?? '')) {
                                    echo "selected='selected'";
                                }

                                echo '>'.$tpl->__('label.'.strtolower($types)).'</option>';
                            } ?>
                        </select><br/>
                    </div>
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
                            <?php echo $tpl->__('subtitles.organization'); ?>
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-organization" style="padding-left:0">

                    <!-- Project -->
                    <div class="form-group">
                        <label class="control-label"><?= $tpl->__('label.project') ?></label>
                        <select name="projectId" class="tw:w-full">
                            <?php foreach ($allAssignedprojects as $project) { ?>
                                <option value="<?= $project['id'] ?>"
                                    <?php
                                    if ($ticket->projectId == $project['id']) {
                                        echo 'selected';
                                    } elseif (session('currentProject') == $project['id']) {
                                        echo 'selected';
                                    }
                                ?>
                                ><?= $tpl->escape($project['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Milestones -->
                    <div class="form-group">
                        <label class="control-label"><?php echo $tpl->__('label.milestone'); ?></label>
                        <div class="">
                            <div class="form-group">
                                <select  name="milestoneid"  class="span11" >
                                    <option value=""><?php echo $tpl->__('label.not_assigned_to_milestone'); ?></option>
                                    <?php foreach ($tpl->get('milestones') as $milestoneRow) {     ?>
                                        <?php echo "<option value='".$milestoneRow->id."'";

                                        if (($ticket->milestoneid == $milestoneRow->id)) {
                                            echo " selected='selected' ";
                                        }

                                        echo '>'.$tpl->escape($milestoneRow->headline).'</option>'; ?>

                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sprint -->
                    <div class="form-group">
                        <label class="control-label"><?php echo $tpl->__('label.sprint'); ?></label>
                        <div class="">

                            <select id="sprint-select" class="span11" name="sprint"
                                    data-placeholder="<?php echo $ticket->sprint ?>">
                                <option value=""><?php echo $tpl->__('label.backlog'); ?></option>
                                <?php
                                if ($tpl->get('sprints')) {
                                    foreach ($tpl->get('sprints') as $sprintRow) { ?>
                                        <option value="<?php echo $sprintRow->id; ?>"
                                            <?php if ($ticket->sprint == $sprintRow->id) {
                                                echo "selected='selected'";
                                            } ?>
                                        ><?php $tpl->e($sprintRow->name); ?></option>
                                    <?php }
                                    } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Related -->
                    <div class="form-group">
                        <label class="control-label"><?php echo $tpl->__('label.related_to'); ?></label>
                        <div class="">
                            <div class="form-group">
                                <select  name="dependingTicketId"  class="span11" >
                                    <option value=""><?php echo $tpl->__('label.not_related'); ?></option>
                                    <?php
                                    if (is_array($tpl->get('ticketParents'))) {
                                        foreach ($tpl->get('ticketParents') as $ticketRow) {
                                            ?>
                                            <?php echo "<option value='".$ticketRow->id."'";

                                            if (($ticket->dependingTicketId == $ticketRow->id)) {
                                                echo " selected='selected' ";
                                            }

                                            echo '>'.$tpl->escape($ticketRow->headline).'</option>'; ?>

                                            <?php
                                        }
                                    }?>
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
                        <?php echo $tpl->__('subtitles.dates'); ?>
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-dates" style="padding-left:0">
                    <div class="form-group">
                        <label class=" control-label"><?php echo $tpl->__('label.working_date_from'); ?></label>
                        <div class="">
                            <input type="text" class="editFrom" style="width:100px;" name="editFrom" autocomplete="off"
                                   value="<?= format($ticket->editFrom)->date(); ?>" placeholder="<?= $tpl->__('language.dateformat') ?>"/>
                            <input type="time" class="timepicker" style="width:120px;" id="timeFrom" autocomplete="off"
                                   value="<?= format($ticket->editFrom)->time24(); ?>"
                                   name="timeFrom"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label"><?php echo $tpl->__('label.working_date_to'); ?></label>
                        <div class="">
                            <input type="text" class="editTo" style="width:100px;" name="editTo" autocomplete="off"
                                   value="<?= format($ticket->editTo)->date() ?>" placeholder="<?= $tpl->__('language.dateformat') ?>"/>
                            <input type="time" class="timepicker" style="width:120px;" id="timeTo" autocomplete="off"
                                   value="<?= format($ticket->editTo)->time24() ?>"
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
                        <?php echo $tpl->__('subtitle.time_tracking'); ?>
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-timetracking" style="padding-left:0">

                    <div class="form-group">
                        <label class=" control-label"><?php echo $tpl->__('label.planned_hours'); ?></label>
                        <div class="">
                            <input type="text" value="<?php $tpl->e($ticket->planHours); ?>" name="planHours" style="width:90px;"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label"><?php echo $tpl->__('label.estimated_hours_remaining'); ?></label>
                        <div class="">
                            <input type="text" value="<?php $tpl->e($ticket->hourRemaining); ?>" name="hourRemaining" style="width:90px;"/>
                            <a href="javascript:void(0)" class="infoToolTip" data-placement="left" data-toggle="tooltip" data-tippy-content="<?php echo $tpl->__('tooltip.how_many_hours_remaining'); ?>">
                                &nbsp;<i class="fa fa-question-circle"></i>&nbsp;
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label"><?php echo $tpl->__('label.booked_hours'); ?></label>
                        <div class="">
                            <input type="text" disabled="disabled"
                                   value="<?php echo $tpl->get('timesheetsAllHours'); ?>" style="width:90px;"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class=" control-label"><?php echo $tpl->__('label.actual_hours_remaining'); ?></label>
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

    // Initialize recurring task dropdown
    jQuery(document).ready(function($) {
        $('.recurring-toggle').click(function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $dropdown = $('#recurringTaskForm');
            if (!$dropdown.hasClass('loaded')) {
                $dropdown.load('<?= BASE_URL ?>/hx/recurringTasks/form?entityId=<?= $ticket->id ?>&module=tickets', function() {
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

<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$ticket = $tpl->get('ticket');
$remainingHours = $tpl->get('remainingHours');
$statusLabels = $tpl->get('statusLabels');
$ticketTypes = $tpl->get('ticketTypes');
$planWeekStart = $tpl->get('planWeekStart');
$planWeekEnd = $tpl->get('planWeekEnd');

?>
<input type="hidden" value="<?php $tpl->e($ticket->id); ?>" name="id" autocomplete="off" readonly />

<div class="row">
    <div class="col-md-9">
        <div class="row marginBottom">
            <div class="col-md-12">

                <div class="form-group">
                    <input type="text" value="<?php $tpl->e($ticket->headline); ?>" name="headline" class="main-title-input" autocomplete="off" style="width:99%; margin-bottom:10px;" placeholder="<?= $tpl->__('input.placeholders.enter_title_of_todo') ?>" />
                </div>

                <!-- Status -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.todo_status'); ?></label>
                    <div class="">
                        <select
                            id="status-select"
                            class=""
                            name="status"
                            data-placeholder="<?php echo isset($ticket->status) ? $statusLabels[$ticket->status]['name'] ?? '' : ''; ?>">
                            <?php foreach ($statusLabels as $key => $label) { ?>
                                <option value="<?php echo $key; ?>"
                                    <?php if ($ticket->status == $key) {
                                        echo "selected='selected'";
                                    } ?>><?php echo $tpl->escape($label['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Priority -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.priority'); ?></label>
                    <div class="">
                        <select id='priority' name='priority' class="">
                            <option value=""><?php echo $tpl->__('label.priority_not_defined'); ?></option>
                            <?php foreach ($tpl->get('priorities') as $priorityKey => $priorityValue) {
                                echo "<option value='".$priorityKey."' ";
                                if ($priorityKey == $ticket->priority) {
                                    echo "selected='selected'";
                                }
                                echo '>'.$priorityValue.'</option>';
                            } ?>
                        </select>
                    </div>
                </div>

                <!-- Effort -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.effort'); ?></label>
                    <div class="">
                        <select id='storypoints' name='storypoints' class="">
                            <option value=""><?php echo $tpl->__('label.effort_not_defined'); ?></option>
                            <?php foreach ($tpl->get('efforts') as $effortKey => $effortValue) {
                                echo "<option value='".$effortKey."' ";
                                if ($effortKey == $ticket->storypoints) {
                                    echo "selected='selected'";
                                }
                                echo '>'.$effortValue.'</option>';
                            } ?>
                        </select>
                    </div>
                </div>

                <!-- Assigned To (locked — pre-filled from weekly plan context) -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.editor'); ?></label>
                    <div class="tw-flex tw-items-center tw-gap-xs">
                        <input type="hidden" name="editorId" id="editorId"
                            value="<?php $tpl->e($tpl->get('lockedEmployeeId')); ?>" />
                        <span class="tw-inline-flex tw-items-center tw-gap-xs tw-px-s tw-py-xs tw-rounded tw-text-sm"
                            style="background:var(--secondary-background);
                                     border:1px solid var(--main-border-color);
                                     color:var(--primary-font-color);">
                            <i class="fa fa-lock" style="color:var(--grey); font-size:0.8em;"></i>
                            <?php $tpl->e($tpl->get('lockedEmployeeName') ?: $tpl->__('label.not_assigned_to_user')); ?>
                        </span>
                    </div>
                </div>

                <!-- Collaborators -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.collaborators'); ?></label>
                    <div class="">
                        <select data-placeholder="<?php echo $tpl->__('label.filter_by_user'); ?>"
                            style="width:175px;"
                            name="collaborators[]"
                            id="collaborators"
                            class="user-select tw-mr-sm"
                            multiple>
                            <?php foreach ($tpl->get('users') as $userRow) { ?>
                                <option value="<?php echo $userRow['id']; ?>"
                                    <?php if (in_array($userRow['id'], $ticket->collaborators ?? [])) {
                                        echo "selected='selected'";
                                    } ?>>
                                    <?php echo $tpl->escape($userRow['firstname'].' '.$userRow['lastname']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.due_date'); ?></label>
                    <div class="">
                        <input type="text" class="dates" style="width:110px;" id="deadline" autocomplete="off"
                            value="<?= format($ticket->dateToFinish)->date(); ?>"
                            name="dateToFinish" placeholder="<?= $tpl->__('language.dateformat') ?>" />

                        <input type="time" class="timepicker tw-mr-sm" style="width:120px;" id="dueTime" autocomplete="off"
                            value="<?= format($ticket->dateToFinish)->time24(); ?>"
                            name="timeToFinish" />

                        <?php if (! empty($planWeekStart) && ! empty($planWeekEnd)) { ?>
                            <small style="display:block; color:var(--grey); margin-top:4px;">
                                <?= sprintf($tpl->__('text.todo_fixed_to_week'), format($planWeekStart)->date(), format($planWeekEnd)->date()); ?>
                            </small>
                        <?php } ?>
                    </div>
                    <div style="padding-top:6px;">
                        <?php $tpl->dispatchTplEvent('afterDates', ['ticket' => $ticket]); ?>
                    </div>
                </div>

                <!-- Tags -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]"><?php echo $tpl->__('label.tags'); ?></label>
                    <div class="">
                        <input type="text" value="<?php $tpl->e($ticket->tags); ?>" name="tags" id="tags" />
                    </div>
                </div>

                <!-- Reference File Upload (optional — for TL to attach visual/document reference) -->
                <div class="form-group tw-flex tw-w-3/5">
                    <label class="control-label tw-mx-m tw-w-[100px]">
                        <?php echo $tpl->__('label.select_file'); ?>
                        <small style="display:block; font-weight:normal; color:var(--grey);">Optional</small>
                    </label>
                    <div class="">
                        <div class="fileupload fileupload-new" data-provides="fileupload">
                            <input type="hidden" />
                            <div class="input-append">
                                <div class="uneditable-input span3">
                                    <i class="fa-file fileupload-exists"></i>
                                    <span class="fileupload-preview"></span>
                                </div>
                                <span class="btn btn-file">
                                    <span class="fileupload-new"><?php echo $tpl->__('buttons.select_file'); ?></span>
                                    <span class="fileupload-exists"><?php echo $tpl->__('buttons.change'); ?></span>
                                    <input type="file"
                                        name="referenceFile"
                                        accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.png,.jpg,.jpeg,.gif,.webp" />
                                </span>
                                <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">
                                    <?php echo $tpl->__('buttons.remove'); ?>
                                </a>
                            </div>
                        </div>
                        <small style="color:var(--grey); display:block; margin-top:4px;">
                            Attach an image or document as reference for this task.
                        </small>
                    </div>
                </div>

                <br />

                <div class="form-group" id="descriptionEditor">
                    <textarea name="description" id="ticketDescription"
                        class="tiptapComplex"><?php echo $ticket->description !== null ? htmlentities($ticket->description) : ''; ?></textarea><br />
                </div>
                <input type="hidden" name="acceptanceCriteria" value="" />

            </div>
        </div>

        <div class="sticky-modal-footer">
            <div class="row">
                <div class="col-md-12" style="margin-top:15px;">
                    <input type="hidden" name="saveTicket" value="1" />
                    <input type="hidden" id="saveAndCloseButton" name="saveAndCloseTicket" value="0" />

                    <input type="submit" name="saveTicket" class="saveTicketBtn" value="<?php echo $tpl->__('buttons.save'); ?>" />
                    <input type="submit" name="saveAndCloseTicket" class="btn btn-outline" onclick="jQuery('#saveAndCloseButton').val('1');" value="<?php echo $tpl->__('buttons.save_and_close'); ?>" />
                </div>
            </div>
        </div>

        <?php if ($ticket->id) { ?>
            <br />
            <hr />
            <?php $tpl->dispatchTplEvent('beforeSubtasks', ['ticketId' => $ticket->id]); ?>
            <h4 class="widgettitle title-light"><i class="fa-solid fa-sitemap"></i> <?php echo $tpl->__('subtitles.subtasks'); ?></h4>

            <div
                id="ticketSubtasks"
                hx-get="<?= BASE_URL ?>/tickets/subtasks/get?ticketId=<?= $ticket->id ?>"
                hx-trigger="load, subtasksUpdated from:body"
                hx-indicator=".subtaskIndicator"></div>
            <div class="htmx-indicator subtaskIndicator">
                Loading Subtasks ...<br /><br />
            </div>

            <h4 class="widgettitle title-light"><span class="fa-solid fa-comments"></span><?php echo $tpl->__('subtitles.discussion'); ?></h4>

            <div class="row-fluid">
                <form method="post" action="<?= BASE_URL ?>/tickets/showTicket/<?php echo $ticket->id; ?>" class="formModal">
                    <input type="hidden" name="comment" value="1" />
                    <?php
                    $tpl->assign('formUrl', ''.BASE_URL.'/tickets/showTicket/'.$ticket->id.'');
            $tpl->displaySubmodule('comments-generalComment');
            ?>
                </form>
            </div>
        <?php } ?>
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
                        <?php echo $tpl->__('subtitles.organization'); ?>
                    </a>
                </h5>
                <div class="simpleAccordionContainer" id="accordion_content-tickets-organization" style="padding-left:0">

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
                            </select><br />
                        </div>
                    </div>

                    <!-- Project -->
                    <div class="form-group">
                        <label class="control-label"><?= $tpl->__('label.project') ?></label>
                        <select name="projectId" class="tw-w-full">
                            <?php foreach ($allAssignedprojects as $project) { ?>
                                <option value="<?= $project['id'] ?>"
                                    <?php
                                    if ($ticket->projectId == $project['id']) {
                                        echo 'selected';
                                    } elseif (session('currentProject') == $project['id']) {
                                        echo 'selected';
                                    }
                                ?>><?= $tpl->escape($project['name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Milestones -->
                    <div class="form-group">
                        <label class="control-label"><?php echo $tpl->__('label.milestone'); ?></label>
                        <div class="">
                            <div class="form-group">
                                <select name="milestoneid" class="span11">
                                    <option value=""><?php echo $tpl->__('label.not_assigned_to_milestone'); ?></option>
                                    <?php foreach ($tpl->get('milestones') as $milestoneRow) { ?>
                                        <?php echo "<option value='".$milestoneRow->id."'";
                                        if ($ticket->milestoneid == $milestoneRow->id) {
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
                                            } ?>><?php $tpl->e($sprintRow->name); ?></option>
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
                                <select name="dependingTicketId" class="span11">
                                    <option value=""><?php echo $tpl->__('label.not_related'); ?></option>
                                    <?php
                                        if (is_array($tpl->get('ticketParents'))) {
                                            foreach ($tpl->get('ticketParents') as $ticketRow) { ?>
                                            <?php echo "<option value='".$ticketRow->id."'";
                                                if (($ticket->dependingTicketId == $ticketRow->id)) {
                                                    echo " selected='selected' ";
                                                }
                                                echo '>'.$tpl->escape($ticketRow->headline).'</option>'; ?>
                                    <?php }
                                            } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <?php $tpl->dispatchTplEvent('beforeEndRightColumn', ['ticket' => $ticket]); ?>
    </div>
</div>

<script>
    jQuery(document).ready(function() {
        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }

        Prism.highlightAll();
    });
</script>

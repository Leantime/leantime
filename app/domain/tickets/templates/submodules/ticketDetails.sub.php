<?php

    $ticket = $this->get('ticket');
    $remainingHours = $this->get('remainingHours');
    $statusLabels  = $this->get('statusLabels');
    $ticketTypes = $this->get('ticketTypes');

?>
<input type="hidden" value="<?php $this->e($ticket->id); ?>" name="id" autocomplete="off" readonly/>

<div class="row-fluid">
    <div class="span8">
        <div class="row-fluid marginBottom">
            <div class="span12">

                <div class="form-group">
                        <input type="text" value="<?php $this->e($ticket->headline); ?>" name="headline" class="main-title-input" autocomplete="off" style="width:99%;" placeholder="<?=$this->__('input.placeholders.enter_title_of_todo')?>"/>
                </div>
                <div class="form-group">
                    <input type="text" value="<?php $this->e($ticket->tags); ?>" name="tags" id="tags" />
                </div>
                <div class="form-group">
                    <textarea name="description" rows="10" cols="80" id="ticketDescription"
                              class="complexEditor"><?php echo $ticket->description ?></textarea><br/>
                </div>
                <input type="hidden" name="acceptanceCriteria" value=""/>

            </div>
        </div>
        <div class="row-fluid">
            <?php if (isset($ticket->id) && $ticket->id != '') : ?>
                <div class="pull-right padding-top">
                    <a href="<?=BASE_URL . "/tickets/delTicket/" . $ticket->id . ""?>" class="delete"><i class="fa fa-trash"></i> <?=$this->__('links.delete_todo')?></a>
                </div>
            <?php endif; ?>
            <input type="hidden" name="saveTicket" value="1" />
            <input type="hidden" id="saveAndCloseButton" name="saveAndCloseTicket" value="0" />
            <input type="submit" name="saveTicket" value="<?php echo $this->__('buttons.save'); ?>"/>
            <input type="submit" name="saveAndCloseTicket" onclick="jQuery('#saveAndCloseButton').val('1');" value="<?php echo $this->__('buttons.save_and_close'); ?>"/>

        </div>
    </div>
    <div class="span4">
        <div class="row-fluid marginBottom">
            <div class="span12">

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.todo_status'); ?></label>
                    <div class="span6">
                        <select id="status-select" class="span11" name="status"
                                data-placeholder="<?php echo $statusLabels[$ticket->status]["name"]; ?>">

                            <?php  foreach ($statusLabels as $key => $label) {?>
                                <option value="<?php echo $key; ?>"
                                    <?php if ($ticket->status == $key) {
                                        echo "selected='selected'";
                                    } ?>
                                ><?php echo $this->escape($label["name"]); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.todo_type'); ?></label>
                    <div class="span6">
                        <select id='type' name='type' class="span11">
                            <?php foreach ($ticketTypes as $types) {
                                echo "<option value='" . strtolower($types) . "' ";
                                if (strtolower($types) == strtolower($ticket->type)) {
                                    echo "selected='selected'";
                                }

                                echo ">" . $this->__("label." . strtolower($types)) . "</option>";
                            } ?>
                        </select><br/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.priority'); ?></label>
                    <div class="span6">
                        <select id='priority' name='priority' class="span11">
                            <option value=""><?php echo $this->__('label.priority_not_defined'); ?></option>
                            <?php foreach ($this->get('priorities') as $priorityKey => $priorityValue) {
                                echo "<option value='" . $priorityKey . "' ";
                                if ($priorityKey == $ticket->priority) {
                                    echo "selected='selected'";
                                }
                                echo ">" . $priorityValue . "</option>";
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.effort'); ?></label>
                    <div class="span6">
                        <select id='storypoints' name='storypoints' class="span11">
                            <option value=""><?php echo $this->__('label.effort_not_defined'); ?></option>
                            <?php foreach ($this->get('efforts') as $effortKey => $effortValue) {
                                echo "<option value='" . $effortKey . "' ";
                                if ($effortKey == $ticket->storypoints) {
                                    echo "selected='selected'";
                                }
                                echo ">" . $effortValue . "</option>";
                            } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row-fluid marginBottom">
            <div class="span12">

                <h4 class="widgettitle title-light"><span
                            class="fa fa-folder-open"></span><?php echo $this->__('subtitles.organization'); ?></h4>

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.milestone'); ?></label>
                    <div class="span6">
                        <div class="form-group">
                            <select  name="dependingTicketId"  class="span11" >
                                <option value=""><?php echo $this->__('label.not_assigned_to_milestone'); ?></option>
                                <?php foreach ($this->get('milestones') as $milestoneRow) {     ?>
                                    <?php echo"<option value='" . $milestoneRow->id . "'";

                                    if (($ticket->dependingTicketId == $milestoneRow->id)) {
                                        echo" selected='selected' ";
                                    }

                                    echo">" . $this->escape($milestoneRow->headline) . "</option>"; ?>

                                <?php }     ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.sprint'); ?></label>
                    <div class="span6">

                        <select id="sprint-select" class="span11" name="sprint"
                                data-placeholder="<?php echo $ticket->sprint ?>">
                            <option value=""><?php echo $this->__('label.not_assigned_to_sprint'); ?></option>
                            <?php
                            if ($this->get('sprints')) {
                                foreach ($this->get('sprints') as $sprintRow) { ?>
                                    <option value="<?php echo $sprintRow->id; ?>"
                                        <?php if ($ticket->sprint == $sprintRow->id) {
                                            echo "selected='selected'";
                                        } ?>
                                    ><?php $this->e($sprintRow->name); ?></option>
                                <?php }
                            } ?>
                        </select>
                    </div>
                </div>

            </div>
        </div>
        <div class="row-fluid marginBottom">
            <div class="span12">

                <h4 class="widgettitle title-light"><span
                            class="fa fa-group"></span><?php echo $this->__('subtitle.people'); ?></h4>

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.author'); ?></label>
                    <div class="span6">
                        <input type="text" disabled="disabled" style="width:175px;"
                               value="<?php $this->e($ticket->userFirstname); ?> <?php $this->e($ticket->userLastname); ?>"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.editor'); ?></label>
                    <div class="span6">

                        <select data-placeholder="<?php echo $this->__('label.filter_by_user'); ?>" style="width:175px;"
                                name="editorId" class="user-select span11">
                            <option value=""><?php echo $this->__('label.not_assigned_to_user'); ?></option>
                            <?php foreach ($this->get('users') as $userRow) { ?>
                                <?php echo "<option value='" . $userRow["id"] . "'";

                                if ($ticket->editorId == $userRow["id"]) {
                                    echo " selected='selected' ";
                                }

                                echo ">" . $this->escape($userRow["firstname"] . " " . $userRow["lastname"]) . "</option>"; ?>

                            <?php } ?>
                        </select>
                        <?php if ($login::userIsAtLeast($roles::$editor)) {  ?>
                            <a href="javascript:void(0);" onclick="jQuery('select[name=editorId]').val('<?php echo $_SESSION['userdata']['id']; ?>')"><?php echo $this->__('label.assign_to_me'); ?></a>
                        <?php } ?>
                    </div>
                </div>


    </div>
</div>
        <div class="row-fluid marginBottom">
            <div class="span12">
                <h4 class="widgettitle title-light"><span
                            class="fa fa-calendar"></span><?php echo $this->__('subtitles.dates'); ?></h4>
                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.ticket_date'); ?></label>
                    <div class="span6">

                        <input type="text" class="dates" style="width:90px;" id="submittedDate" disabled="disabled"
                               value="<?php echo $ticket->date; ?>" name="date"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.due_date'); ?></label>
                    <div class="span6">
                        <input type="text" class="dates" style="width:90px;" id="deadline" autocomplete="off"
                               value="<?php echo $ticket->dateToFinish; ?>"
                               name="dateToFinish"/>
                        -
                        <input type="time" class="timepicker" style="width:120px;" id="dueTime" autocomplete="off"
                               value="<?php echo $ticket->timeToFinish; ?>"
                               name="timeToFinish"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.working_date_from_to'); ?></label>
                    <div class="span6">
                        <input type="text" class="dates" style="width:90px; float:left;" name="editFrom" autocomplete="off"
                               value="<?php echo $ticket->editFrom; ?>"/> -
                        <input type="text" class="dates" style="width:90px;" name="editTo" autocomplete="off"
                               value="<?php echo $ticket->editTo; ?>"/>
                    </div>
                </div>

            </div>
        </div>
        <div class="row-fluid marginBottom">
            <div class="span12">
                <h4 class="widgettitle title-light"><span
                            class="fa-regular fa-clock"></span><?php echo $this->__('subtitle.time_tracking'); ?></h4>
                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.planned_hours'); ?></label>
                    <div class="span6">
                        <input type="text" value="<?php $this->e($ticket->planHours); ?>" name="planHours" style="width:90px;"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.estimated_hours_remaining'); ?></label>
                    <div class="span6">
                        <input type="text" value="<?php $this->e($ticket->hourRemaining); ?>" name="hourRemaining" style="width:90px;"/>
                        <a href="javascript:void(0)" class="infoToolTip" data-placement="left" data-toggle="tooltip" data-original-title="<?php echo $this->__('tooltip.how_many_hours_remaining'); ?>">
                            &nbsp;<i class="fa fa-question-circle"></i>&nbsp;</a>
                    </div>
                </div>

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.booked_hours'); ?></label>
                    <div class="span6">
                        <input type="text" disabled="disabled"
                               value="<?php echo $this->get('timesheetsAllHours'); ?>" style="width:90px;"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="span4 control-label"><?php echo $this->__('label.actual_hours_remaining'); ?></label>
                    <div class="span6">
                        <input type="text" disabled="disabled" value="<?php echo $remainingHours; ?>" style="width:90px;"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    leantime.generalController.initComplexEditor();
</script>


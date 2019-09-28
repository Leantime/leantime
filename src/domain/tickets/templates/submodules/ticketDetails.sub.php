<?php
$tickets = $this->get('objTicket');
$objTickets = $tickets;
$helper = $this->get('helper');
$ticket = $this->get('ticket');
$statePlain = $this->get('statePlain');
$remainingHours = $ticket['planHours'] - $this->get('timesheetsAllHours');

$type = $this->get('type');
?>

<script>
    jQuery(document).ready(function () {
        // Tags Input
        jQuery('#tags').tagsInput({
            'height': '40px',
            'width': '210px',
            'placeholderColor': '#cccccc'
        });

        jQuery(".user-select, .status-select, .project-select").chosen();


        jQuery(".dates").datepicker({
            dateFormat: 'mm/dd/yy',
            dayNames: [<?php echo '' . $lang['DAYNAMES'] . '' ?>],
            dayNamesMin: [<?php echo '' . $lang['DAYNAMES_MIN'] . '' ?>],
            monthNames: [<?php echo '' . $lang['MONTHS'] . '' ?>]
        });


        var minDate = jQuery("#submittedDate").datepicker("getDate");
        jQuery("#deadline").datepicker("option", "minDate", minDate);

    });
</script>
<form class="formModal" action="" method="post">
    <div class="row-fluid">

        <div class="span7">
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light"><span class="iconfa iconfa-leaf"></span><?php echo $language->lang_echo('GENERAL'); ?></h4>
                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('HEADLINE'); ?>*</label>
                        <div class="span6">
                            <input type="text" value="<?php $this->e($ticket['headline']); ?>" name="headline" autocomplete="off"  style="width:220px;"/>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('Milestone', false); ?></label>
                        <div class="span6">
                            <div class="form-group">
                                <select  name="dependingTicketId"  class="span11" >
                                    <option value="">Not assigned to Milestones</option>
                                    <?php foreach($this->get('milestones') as $milestoneRow){     ?>

                                        <?php echo"<option value='".$milestoneRow->id."'";

                                        if(($ticket['dependingTicketId'] == $milestoneRow->id)) { echo" selected='selected' ";
                                        }

                                        echo">".htmlentities($milestoneRow->headline)."</option>"; ?>

                                    <?php }     ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('TYPE'); ?></label>
                        <div class="span6">
                            <select id='type' name='type' class="span11">
                                <?php foreach ($type as $types) {
                                    echo "<option value='" . $types . "' ";
                                    if ($types === $ticket['type']) {
                                        echo "selected='selected'";
                                    }
                                    echo ">" . $types . "</option>";
                                } ?>
                            </select><br/>

                        </div>
                    </div>
                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('STATUS'); ?></label>
                        <div class="span6">
                            <select id="status-select" class="span11" name="status"
                                    data-placeholder="<?php echo $language->lang_echo($tickets->getStatusPlain($ticket['status'])); ?>">
                                <?php foreach ($tickets->statePlain as $key => $row2) { ?>
                                    <option value="<?php echo $key; ?>"
                                        <?php if ($ticket['status'] == $key) {
                                            echo "selected='selected'";
                                        } ?>
                                    ><?php echo  $tickets->stateLabels[$tickets->getStatusPlain($key)]; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('SPRINT'); ?></label>
                        <div class="span6">

                            <select id="sprint-select" class="span11" name="sprint"
                                    data-placeholder="<?php echo $ticket['sprint'] ?>">
                                <option value="-1">Not assigned to a sprint</option>
                                <?php foreach ($this->get('sprints') as $sprintRow) { ?>
                                    <option value="<?php echo $sprintRow->id; ?>"
                                        <?php if ($ticket['sprint'] == $sprintRow->id) {
                                            echo "selected='selected'";
                                        } ?>
                                    ><?php $this->e($sprintRow->name); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('Effort', false); ?></label>
                        <div class="span6">
                            <select id='storypoints' name='storypoints' class="span11">
                                <option value="">Effort not clear</option>
                                <?php foreach ($this->get('efforts') as $effortKey=>$effortValue) {
                                    echo "<option value='" . $effortKey . "' ";
                                    if ($effortKey == $ticket['storypoints']) {
                                        echo "selected='selected'";
                                    }
                                    echo ">" . $effortValue . "</option>";
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('TAGS'); ?></label>
                        <div class="span6">
                            <input type="text" value="<?php $this->e($ticket['tags']); ?>" name="tags" id="tags"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light"><span
                                class="iconfa iconfa-asterisk"></span><?php echo $language->lang_echo('DESCRIPTION'); ?>
                    </h4>

                    <textarea name="description" rows="10" cols="80" id="ticketDescription"
                              class="tinymce"><?php echo $ticket['description'] ?></textarea><br/>
                    <input type="hidden" name="acceptanceCriteria" value=""/>

                </div>
            </div>
        </div>
        <div class="span5">
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light"><span
                                class="iconfa iconfa-group"></span><?php echo $language->lang_echo('PEOPLE'); ?></h4>

                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('AUTHOR'); ?></label>
                        <div class="span6">
                            <input type="text" disabled="disabled"
                                   value="<?php $this->e($ticket['userFirstname']); ?> <?php $this->e($ticket['userLastname']); ?>"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('EDITOR'); ?></label>
                        <div class="span6">

                            <select data-placeholder="<?php echo $language->lang_echo('FILTER_BY_USER'); ?>"
                                    name="editorId" class="user-select span11">
                                <option value=""></option>
                                <?php foreach ($this->get('users') as $userRow) { ?>

                                    <?php echo "<option value='" . $userRow["id"] . "'";

                                    if ($ticket['editorId'] == $userRow["id"]) { echo " selected='selected' ";
                                    }

                                    echo ">" . $userRow["firstname"] . " " . $userRow["lastname"] . "</option>"; ?>

                                <?php } ?>
                            </select>
                        </div>
                    </div>


                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light"><span
                                class="iconfa iconfa-calendar"></span><?php echo $language->lang_echo('DATES'); ?></h4>
                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('DATE_OF_TICKET'); ?></label>
                        <div class="span6">
                            <input type="text" class="dates" id="submittedDate" disabled="disabled"
                                   value="<?php echo $helper->timestamp2date($ticket['date'], 2); ?>" name="date"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('DATE_TO_FINISH'); ?></label>
                        <div class="span6">
                            <input type="text" class="dates" id="deadline"
                                   value="<?php echo $helper->timestamp2date($ticket['dateToFinish'], 2); ?>"
                                   name="dateToFinish"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('DATE_FROM') . $lang['DATE_TO']; ?></label>
                        <div class="span6">

                            <input type="text" class="dates" style="width:90px; float:left;" name="editFrom"
                                   value="<?php echo $helper->timestamp2date($ticket['editFrom'], 2); ?>"/> -
                            <input type="text" class="dates" style="width:90px;" name="editTo"
                                   value="<?php echo $helper->timestamp2date($ticket['editTo'], 2); ?>"/>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <h4 class="widgettitle title-light"><span
                                class="iconfa iconfa-time"></span><?php echo $language->lang_echo('TIME'); ?></h4>
                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('PLAN_HOURS'); ?></label>
                        <div class="span6">
                            <input type="text" value="<?php $this->e($ticket['planHours']); ?>" name="planHours"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="span4 control-label">Estimate Hours Remaining</label>
                        <div class="span6">
                            <input type="text" value="<?php $this->e($ticket['hourRemaining']); ?>" name="hourRemaining"/>
                            <a href="javascript:void(0)" class="infoToolTip" data-placement="left" data-toggle="tooltip" data-original-title="How many hours do you think are left on this To-Do">
                                &nbsp;<i class="fa fa-question-circle"></i>&nbsp;</a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('BOOKED_HOURS'); ?></label>
                        <div class="span6">
                            <input type="text" disabled="disabled"
                                   value="<?php echo $this->get('timesheetsAllHours'); ?>"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="span4 control-label"><?php echo $language->lang_echo('HOURS_REMAINING'); ?></label>
                        <div class="span6">
                            <input type="text" disabled="disabled" value="<?php echo $remainingHours; ?>"/>
                        </div>
                    </div>



                </div>
            </div>

        </div>

    </div>
    <div class="row-fluid">
        <?php if (isset($ticket['id']) && $ticket['id'] != '') : ?>
            <div class="pull-right padding-top">
                <?php echo $this->displayLink('tickets.delTicket', '<i class="fa fa-trash"></i> Delete To-Do', array('id' => $ticket['id']), array('class' => 'delete')) ?>
            </div>
        <?php endif; ?>

        <input type="submit" name="saveTicket" value="Save"/>
        <input type="submit" name="saveAndCloseTicket" value="Save &amp; Close"/>

    </div>
</form>

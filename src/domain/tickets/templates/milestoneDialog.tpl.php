<?php
$currentMilestone = $this->get('milestone');
$milestones = $this->get('milestones');


?>

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?=BASE_URL ?>/tickets/roadmap&showMilestoneModal=<?php echo $currentMilestone->id; ?>";

        }
    }
</script>

<div class="showDialogOnLoad" style="display:none;">

    <h4 class="widgettitle title-light"><i class="fa fa-rocket"></i> Milestone</h4>

    <?php echo $this->displayNotification(); ?>

    <form class="formModal" method="post" action="<?=BASE_URL ?>/tickets/editMilestone/<?php echo $currentMilestone->id ?>" style="min-width: 250px;">

        <label>Milestone Name</label>
        <input type="text" name="headline" value="<?php echo $currentMilestone->headline?>" placeholder="Milestone Name"/><br />

        <label>Dependency</label>
        <select name="dependentMilestone"  class="span11">
            <option value="">No dependency</option>
            <?php foreach ($this->get('milestones') as $milestoneRow) {
                if($milestoneRow->id !== $currentMilestone->id) {
                    echo "<option value='" . $milestoneRow->id . "'";

                    if ($currentMilestone->dependingTicketId == $milestoneRow->id) { echo " selected='selected' ";
                    }

                    echo ">" . $milestoneRow->headline . " </option>";

                }
            }
            ?>

        </select>

        <label>Owner</label>
        <select data-placeholder="<?php echo $language->lang_echo('FILTER_BY_USER'); ?>"
                name="editorId" class="user-select span11">
            <option value="">Not assigned</option>
            <?php foreach ($this->get('users') as $userRow) { ?>

                <?php echo "<option value='" . $userRow["id"] . "'";

                if ($currentMilestone->editorId == $userRow["id"]) { echo " selected='selected' ";
                }

                echo ">" . $userRow["firstname"] . " " . $userRow["lastname"] . "</option>"; ?>

            <?php } ?>
        </select>

        <label>Color</label>
        <input type="text" name="tags" value="<?php echo $currentMilestone->tags?>" placeholder="Pick a color" class="simpleColorPicker"/><br />

        <label>Planned Start Day</label>
        <input type="text" name="editFrom" value="<?php echo $currentMilestone->editFrom?>" placeholder="mm/dd/yyyy" id="milestoneEditFrom" /><br />

        <label>Planned Complete Day</label>
        <input type="text" name="editTo" value="<?php echo $currentMilestone->editTo?>"  placeholder="mm/dd/yyyy" id="milestoneEditTo" /><br />

        <div class="row">
            <div class="col-md-6">
                <input type="submit" value="Save"/>
            </div>
            <div class="col-md-6 align-right padding-top-sm">
                <?php if (isset($currentMilestone->id) && $currentMilestone->id != '' && ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager')) { ?>
                    <a href="<?=BASE_URL ?>/tickets/delMilestone/<?php echo $currentMilestone->id; ?>" class="delete formModal milestoneModal"><i class="fa fa-trash"></i> Delete Milestone</a>
                <?php } ?>
            </div>
        </div>

    </form>

    <?php

    if(isset($currentMilestone->id) && $currentMilestone->id !== '') { ?>
        <br />
        <input type="hidden" name="comment" value="1" />

        <?php
        $this->assign("formUrl", "/tickets/editMilestone/".$currentMilestone->id."");
        $this->displaySubmodule('comments-generalComment');?>
    <?php } ?>

    <script type="text/javascript">
        jQuery(document).ready(function(){
            leantime.ticketsController.initModals();
        })
    </script>

</div>
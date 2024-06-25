<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$currentMilestone = $tpl->get('milestone');
$milestones = $tpl->get('milestones');
$statusLabels = $tpl->get('statusLabels');
?>

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?=BASE_URL ?>/tickets/roadmap?showMilestoneModal=<?php echo $currentMilestone->id; ?>";
        }
    }
</script>

<div class="modal-icons">
    <?php if (isset($currentMilestone->id) && $currentMilestone->id != '') {?>
        <a href="#/tickets/delMilestone/<?php echo $currentMilestone->id; ?>" class="danger" data-tippy-content="Delete"><i class='fa fa-trash-can'></i></a>
    <?php } ?>
</div>

<h4 class="widgettitle title-light"><?=$tpl->__("headline.milestone"); ?> </h4>

<?php echo $tpl->displayNotification(); ?>

<form class="formModal" method="post" action="<?=BASE_URL ?>/tickets/editMilestone/<?php echo $currentMilestone->id ?>" style="min-width: 250px;">

    <label><?=$tpl->__("label.milestone_title"); ?></label>
    <input type="text" name="headline" value="<?php $tpl->e($currentMilestone->headline) ?>" placeholder="<?=$tpl->__("label.milestone_title"); ?>"/><br />

    <label class="control-label"><?=$tpl->__('label.project') ?></label>
    <select name="projectId" class="tw-w-full">
        <?php foreach ($allAssignedprojects as $project) {
            if (empty($project['type']) || $project['type'] == "project") {
                ?>
            <option value="<?=$project['id'] ?>"
                <?php
                if (
                    !empty($currentMilestone->projectId)
                        && $currentMilestone->projectId == $project['id']
                ) {
                    echo "selected";
                } else if (session("currentProject") == $project['id']) {
                    echo "selected";
                }
                ?>
            ><?=$tpl->escape($project["name"]); ?></option>
            <?php }
        } ?>
    </select>

    <label><?php echo $tpl->__('label.todo_status'); ?></label>
    <select id="status-select" name="status" class="span11"
            data-placeholder="<?php echo isset($statusLabels[$currentMilestone->status]) ? $statusLabels[$currentMilestone->status]["name"] : ''; ?>">

        <?php  foreach ($statusLabels as $key => $label) {?>
            <option value="<?php echo $key; ?>"
                <?php if ($currentMilestone->status == $key) {
                    echo "selected='selected'";
                } ?>
            ><?php echo $tpl->escape($label["name"]); ?></option>
        <?php } ?>
    </select>

    <label><?=$tpl->__("label.dependent_on"); ?></label>
    <select name="dependentMilestone"  class="span11">
        <option value=""><?=$tpl->__("label.no_dependency"); ?></option>
        <?php foreach ($tpl->get('milestones') as $milestoneRow) {
            if ($milestoneRow->id !== $currentMilestone->id) {
                echo "<option value='" . $milestoneRow->id . "'";

                if ($currentMilestone->milestoneid == $milestoneRow->id) {
                    echo " selected='selected' ";
                }

                echo ">" . $tpl->escape($milestoneRow->headline) . " </option>";
            }
        }
        ?>

    </select>

    <label><?=$tpl->__("label.owner"); ?></label>
    <select data-placeholder="<?php echo $tpl->__('input.placeholders.filter_by_user'); ?>"
            name="editorId" class="user-select span11">
        <option value=""><?=$tpl->__("dropdown.not_assigned"); ?></option>
        <?php foreach ($tpl->get('users') as $userRow) { ?>
            <?php echo "<option value='" . $userRow["id"] . "'";

            if ($currentMilestone->editorId == $userRow["id"]) {
                echo " selected='selected' ";
            }

            echo ">" . $tpl->escape($userRow["firstname"]) . " " . $tpl->escape($userRow["lastname"]) . "</option>"; ?>

        <?php } ?>
    </select>

    <label><?=$tpl->__("label.color"); ?></label>
    <input type="text" name="tags" autocomplete="off" value="<?php echo $currentMilestone->tags?>" placeholder="<?=$tpl->__("input.placeholders.pick_a_color"); ?>" class="simpleColorPicker"/><br />

    <label><?=$tpl->__("label.planned_start_date"); ?></label>
    <input type="text" name="editFrom" autocomplete="off" value="<?php echo format($currentMilestone->editFrom)->date() ?>" placeholder="<?=$tpl->__("language.dateformat"); ?>" id="milestoneEditFrom" /><br />

    <label><?=$tpl->__("label.planned_end_date"); ?></label>
    <input type="text" name="editTo" autocomplete="off" value="<?php echo format($currentMilestone->editTo)->date() ?>"  placeholder="<?=$tpl->__("language.dateformat"); ?>" id="milestoneEditTo" /><br />

    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="<?=$tpl->__("buttons.save"); ?>" class="btn btn-primary"/>
        </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
    </div>

</form>

    <?php
    if (isset($currentMilestone->id) && $currentMilestone->id !== '') {
        ?>
    <br />
    <input type="hidden" name="comment" value="1" />

        <?php
        $tpl->assign("formUrl", "/tickets/editMilestone/" . $currentMilestone->id . "");
        $tpl->displaySubmodule('comments-generalComment');?>
    <?php } ?>

<script type="text/javascript">
    jQuery(document).ready(function(){

        leantime.ticketsController.initSimpleColorPicker();
        leantime.ticketsController.initMilestoneDates();

        <?php if (!$login::userIsAtLeast($roles::$editor)) { ?>
            leantime.authController.makeInputReadonly(".nyroModalCont");
        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php }?>


    })
</script>


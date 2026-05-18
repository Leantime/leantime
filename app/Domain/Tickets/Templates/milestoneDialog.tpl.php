<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$currentMilestone = $tpl->get('milestone');
$milestones = $tpl->get('milestones');
$statusLabels = $tpl->get('statusLabels');
$milestoneProgress = $tpl->get('milestoneProgress') ?? 0;
$doneStatusId = $tpl->get('doneStatusId');
$readyForReviewStatusId = $tpl->get('readyForReviewStatusId') ?? 5;
$canCompleteMilestone = $tpl->get('canCompleteMilestone') ?? false;

$milestoneStatus = (int) ($currentMilestone->status ?? 3);
$isReadyForReview = $milestoneStatus === $readyForReviewStatusId;
$isCompleted = $doneStatusId !== null && (string) $milestoneStatus === (string) $doneStatusId;
$isInProgress = ! $isReadyForReview && ! $isCompleted;
$allTasksDone = $milestoneProgress >= 100;
?>

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="<?= BASE_URL ?>/tickets/roadmap?showMilestoneModal=<?php echo $currentMilestone->id; ?>";
        }
    }
</script>

<div class="modal-icons">
    <?php if (isset($currentMilestone->id) && $currentMilestone->id != '') {?>
        <a href="#/tickets/delMilestone/<?php echo $currentMilestone->id; ?>" class="danger" data-tippy-content="Delete"><i class='fa fa-trash-can'></i></a>
    <?php } ?>
</div>

<h4 class="widgettitle title-light"><?= $tpl->__('headline.milestone'); ?> </h4>

<?php echo $tpl->displayNotification(); ?>

<form class="formModal" method="post" action="<?= BASE_URL ?>/tickets/editMilestone/<?php echo $currentMilestone->id ?>" style="min-width: 250px;">

    <label><?= $tpl->__('label.milestone_title'); ?></label>
    <input type="text" name="headline" value="<?php $tpl->e($currentMilestone->headline) ?>" placeholder="<?= $tpl->__('label.milestone_title'); ?>"/><br />

    <label class="control-label"><?= $tpl->__('label.project') ?></label>
    <select name="projectId" class="tw-w-full">
        <?php foreach ($allAssignedprojects as $project) {
            if (empty($project['type']) || $project['type'] == 'project') {
                ?>
            <option value="<?= $project['id'] ?>"
                <?php
                if (
                    ! empty($currentMilestone->projectId)
                        && $currentMilestone->projectId == $project['id']
                ) {
                    echo 'selected';
                } elseif (session('currentProject') == $project['id']) {
                    echo 'selected';
                }
                ?>
            ><?= $tpl->escape($project['name']); ?></option>
            <?php }
            } ?>
    </select>

    <label><?php echo $tpl->__('label.todo_status'); ?></label>
    <select id="status-select" name="status" class="span11"
            data-placeholder="<?php echo isset($statusLabels[$currentMilestone->status]) ? $statusLabels[$currentMilestone->status]['name'] : ''; ?>">

        <?php foreach ($statusLabels as $key => $label) {?>
            <option value="<?php echo $key; ?>"
                <?php if ($currentMilestone->status == $key) {
                    echo "selected='selected'";
                } ?>
            ><?php echo $tpl->escape($label['name']); ?></option>
        <?php } ?>
    </select>

    <?php if (isset($currentMilestone->id) && $currentMilestone->id != '') { ?>
        <label><?= $tpl->__('subtitles.milestone_progress'); ?></label>
        <div class="progress">
            <div class="progress-bar progress-bar-success"
                 role="progressbar"
                 aria-valuenow="<?= (int) round($milestoneProgress); ?>"
                 aria-valuemin="0"
                 aria-valuemax="100"
                 style="width: <?= (int) round($milestoneProgress); ?>%">
                <span class="sr-only"><?= (int) round($milestoneProgress); ?>%</span>
            </div>
        </div>
        <p><?= sprintf($tpl->__('text.percent_complete'), (int) round($milestoneProgress)); ?></p>
    <?php } ?>

    <label><?= $tpl->__('label.dependent_on'); ?></label>
    <select name="dependentMilestone"  class="span11">
        <option value=""><?= $tpl->__('label.no_dependency'); ?></option>
        <?php foreach ($tpl->get('milestones') as $milestoneRow) {
            if ($milestoneRow->id !== $currentMilestone->id) {
                echo "<option value='".$milestoneRow->id."'";

                if ($currentMilestone->milestoneid == $milestoneRow->id) {
                    echo " selected='selected' ";
                }

                echo '>'.$tpl->escape($milestoneRow->headline).' </option>';
            }
        }
?>

    </select>

    <label><?= $tpl->__('label.owner'); ?></label>
    <select data-placeholder="<?php echo $tpl->__('input.placeholders.filter_by_user'); ?>"
            name="editorId" class="user-select span11">
        <option value=""><?= $tpl->__('dropdown.not_assigned'); ?></option>
        <?php foreach ($tpl->get('users') as $userRow) { ?>
            <?php echo "<option value='".$userRow['id']."'";

            if ($currentMilestone->editorId == $userRow['id']) {
                echo " selected='selected' ";
            }

            echo '>'.$tpl->escape($userRow['firstname']).' '.$tpl->escape($userRow['lastname']).'</option>'; ?>

        <?php } ?>
    </select>

    <label><?= $tpl->__('label.color'); ?></label>
    <input type="text" name="tags" autocomplete="off" value="<?php echo $currentMilestone->tags?>" placeholder="<?= $tpl->__('input.placeholders.pick_a_color'); ?>" class="simpleColorPicker"/><br />

    <label><?= $tpl->__('label.planned_start_date'); ?></label>
    <input type="text" name="editFrom" autocomplete="off" value="<?php echo format($currentMilestone->editFrom)->date() ?>" placeholder="<?= $tpl->__('language.dateformat'); ?>" id="milestoneEditFrom" /><br />

    <label><?= $tpl->__('label.planned_end_date'); ?></label>
    <input type="text" name="editTo" autocomplete="off" value="<?php echo format($currentMilestone->editTo)->date() ?>"  placeholder="<?= $tpl->__('language.dateformat'); ?>" id="milestoneEditTo" /><br />

    <?php if (isset($currentMilestone->id) && $currentMilestone->id != '') { ?>

        <!-- Milestone status badge -->
        <div style="margin-bottom: 12px;">
            <?php if ($isCompleted) { ?>
                <span class="label label-success" style="font-size:var(--font-size-s); padding: 4px 10px;">
                    <i class="fa fa-check-circle"></i> <?= $tpl->__('label.milestone_completed'); ?>
                </span>
            <?php } elseif ($isReadyForReview) { ?>
                <span class="label label-info" style="font-size:var(--font-size-s); padding: 4px 10px;">
                    <i class="fa fa-hourglass-half"></i> <?= $tpl->__('label.milestone_ready_for_review'); ?>
                </span>
            <?php } elseif ($allTasksDone) { ?>
                <span class="label label-warning" style="font-size:var(--font-size-s); padding: 4px 10px;">
                    <i class="fa fa-tasks"></i> <?= $tpl->__('label.milestone_all_tasks_done'); ?>
                </span>
            <?php } ?>
        </div>

    <?php } ?>

    <div class="row">
        <div class="col-md-6">
            <?php if (! $isCompleted) { ?>
                <input type="submit" value="<?= $tpl->__('buttons.save'); ?>" class="btn btn-primary"/>
            <?php } ?>
        </div>
        <div class="col-md-6 align-right padding-top-sm" style="display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap;">

            <?php if (isset($currentMilestone->id) && $currentMilestone->id != '' && ! $isCompleted) { ?>

                <?php if ($isReadyForReview && $canCompleteMilestone) { ?>
                    <!-- Senior: Approve or Reject -->
                    <button type="submit" name="markComplete" value="1" class="btn btn-success">
                        <i class="fa fa-check"></i> <?= $tpl->__('buttons.approve_milestone'); ?>
                    </button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectPanel').style.display='block'; this.style.display='none';">
                        <i class="fa fa-times"></i> <?= $tpl->__('buttons.reject_milestone'); ?>
                    </button>

                <?php } elseif ($isInProgress && ! $isReadyForReview) { ?>
                    <?php if ($canCompleteMilestone) { ?>
                        <!-- Senior can still force-complete or send for review -->
                        <button type="submit" name="sendForReview" value="1" class="btn btn-default">
                            <i class="fa fa-paper-plane"></i> <?= $tpl->__('buttons.send_for_review'); ?>
                        </button>
                        <button type="submit" name="markComplete" value="1" class="btn btn-success">
                            <i class="fa fa-check"></i> <?= $tpl->__('buttons.mark_milestone_complete'); ?>
                        </button>
                    <?php } else { ?>
                        <!-- Junior/Editor: can only send for review when all tasks done -->
                        <?php if ($allTasksDone) { ?>
                            <button type="submit" name="sendForReview" value="1" class="btn btn-primary">
                                <i class="fa fa-paper-plane"></i> <?= $tpl->__('buttons.send_for_review'); ?>
                            </button>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>

            <?php } ?>

        </div>
    </div>

    <!-- Rejection panel (hidden by default) -->
    <?php if (isset($currentMilestone->id) && $currentMilestone->id != '' && $isReadyForReview && $canCompleteMilestone) { ?>
        <div id="rejectPanel" style="display:none; margin-top:12px; padding:12px; background:var(--secondary-background); border-radius:var(--box-radius-small);">
            <label><?= $tpl->__('label.rejection_note'); ?></label>
            <textarea name="rejectionNote" rows="3" style="width:100%; margin-bottom:8px;" placeholder="<?= $tpl->__('input.placeholders.rejection_reason'); ?>"></textarea>
            <button type="submit" name="rejectMilestone" value="1" class="btn btn-danger">
                <i class="fa fa-times"></i> <?= $tpl->__('buttons.confirm_reject'); ?>
            </button>
        </div>
    <?php } ?>

</form>

    <?php
    if (isset($currentMilestone->id) && $currentMilestone->id !== '') {
        ?>
    <br />
    <input type="hidden" name="comment" value="1" />

        <?php
        $tpl->assign('formUrl', '/tickets/editMilestone/'.$currentMilestone->id.'');
        $tpl->displaySubmodule('comments-generalComment'); ?>
    <?php } ?>

<script type="text/javascript">
    jQuery(document).ready(function(){

        leantime.ticketsController.initSimpleColorPicker();
        leantime.ticketsController.initMilestoneDates();

        <?php if (! $login::userIsAtLeast($roles::$editor)) { ?>
            leantime.authController.makeInputReadonly(".nyroModalCont");
        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php }?>


    })
</script>

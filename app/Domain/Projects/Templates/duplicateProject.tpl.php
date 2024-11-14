<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$project = $tpl->get('project');
?>

<h4 class="widgettitle title-light"><?php echo sprintf($tpl->__('headlines.duplicate_project_x'), $project['name']); ?></h4>

<?php echo $tpl->displayNotification(); ?>

<form class="formModal" method="post" action="<?= BASE_URL ?>/projects/duplicateProject/<?php echo $project['id']; ?>">

    <label><?= $tpl->__('label.newProjectName') ?></label>
    <input type="text" name="projectName" value="<?= $tpl->__('label.copy_of')?> <?php $tpl->e($project['name'])?>" /><br />

    <label><?= $tpl->__('label.planned_start_date') ?></label>
    <input type="text" name="startDate" class="projectDateFrom" value="<?php echo format(date('Y-m-d'))->date()?>" placeholder="<?= $tpl->__('language.dateformat') ?>" id="sprintStart" /><br />

    <label><?= $tpl->__('label.client_product') ?></label>
    <select name="clientId" id="clientId">
        <?php foreach ($tpl->get('allClients') as $row) { ?>
            <option value="<?php echo $row['id']; ?>"
                <?php if ($project['clientId'] == $row['id']) {
                    ?> selected=selected
                <?php } ?>><?php $tpl->e($row['name']); ?></option>
        <?php } ?>
    </select>
    <br />
    <input style="float:left; margin-right:5px;"
           type="checkbox" name="assignSameUsers" id="assignSameUsers"/>
    <label for="assignSameUsers"><?php echo $tpl->__('label.assignSameUsers') ?></label>

    <br />

    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="<?= $tpl->__('buttons.duplicate') ?>"/>
        </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
    </div>

</form>


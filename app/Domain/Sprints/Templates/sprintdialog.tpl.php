<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$currentSprint = $tpl->get('sprint');
?>

<h4 class="widgettitle title-light"><i class="fa fa-list-1-2"></i> <?=$tpl->__('label.sprint') ?> <?php echo $currentSprint->name?></h4>

<?php echo $tpl->displayNotification();

$id = "";
if (isset($currentSprint->id)) {
    $id = $currentSprint->id;
}
?>

<form class="formModal" method="post" action="<?=BASE_URL ?>/sprints/editSprint/<?php echo $id;?>">

    <label><?=$tpl->__('label.sprint_name') ?></label>
    <input type="text" name="name" value="<?php echo $currentSprint->name?>" placeholder="<?=$tpl->__('label.sprint_name') ?>"/><br />

    <label><?=$tpl->__('label.project') ?></label>
    <select name="projectId">
        <?php foreach($allAssignedprojects as $project) { ?>
            <option value="<?=$project['id'] ?>"
                    <?php
                    if(isset($currentSprint)) {
                        if($currentSprint->projectId == $project['id']) {
                            echo "selected";
                        }
                    }elseif( session("currentProject") == $project['id']){
                        echo "selected";
                    }
                    ?>
        ><?=$tpl->escape($project["name"]); ?></option>
        <?php } ?>
    </select><br />

    <br /><br />
    <p><?=$tpl->__('label.sprint_dates') ?></p><br/>
    <label><?=$tpl->__('label.first_day') ?></label>
    <input type="text" name="startDate" autocomplete="off" value="<?php echo $currentSprint->startDate?>" placeholder="<?=$tpl->__('language.dateformat') ?>" id="sprintStart" /><br />

    <label><?=$tpl->__('label.last_day') ?></label>
    <input type="text" name="endDate" autocomplete="off" value="<?php echo $currentSprint->endDate?>"  placeholder="<?=$tpl->__('language.dateformat') ?>" id="sprintEnd"  />

    <br />

    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="<?=$tpl->__('buttons.save') ?>"/>
        </div>
        <div class="col-md-6 align-right padding-top-sm">
            <?php if (isset($currentSprint->id) && $currentSprint->id != '' && $login::userIsAtLeast($roles::$editor)) { ?>
                <a href="<?=BASE_URL ?>/sprints/delSprint/<?php echo $currentSprint->id; ?>" class="delete formModal sprintModal"><i class="fa fa-trash"></i> <?=$tpl->__('links.delete_sprint') ?></a>
            <?php } ?>
        </div>
    </div>

</form>

<script>
    leantime.ticketsController.initSprintDates();
</script>


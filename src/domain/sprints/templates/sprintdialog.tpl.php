<?php
  $currentSprint = $this->get('sprint');
?>

<h4 class="widgettitle title-light"><i class="fa fa-rocket"></i> <?=$this->__('label.sprint') ?> <?php echo $currentSprint->name?></h4>

<?php echo $this->displayNotification();

$id = "";
if(isset($currentSprint->id)) {$id = $currentSprint->id;
}
?>

<form class="formModal" method="post" action="<?=BASE_URL ?>/sprints/editSprint/<?php echo $id;?>">

    <label><?=$this->__('label.sprint_name') ?></label>
    <input type="text" name="name" value="<?php echo $currentSprint->name?>" placeholder="<?=$this->__('input.placeholders.sprint_x') ?>"/><br />

    <label><?=$this->__('label.first_day') ?></label>
    <input type="text" name="startDate" value="<?php echo $currentSprint->startDate?>" placeholder="<?=$this->__('language.jsdateformat') ?>" id="sprintStart" /><br />

    <label><?=$this->__('label.last_day') ?></label>
    <input type="text" name="endDate" value="<?php echo $currentSprint->endDate?>"  placeholder="<?=$this->__('language.jsdateformat') ?>" id="sprintEnd"  />

    <br />

    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="<?=$this->__('buttons.save') ?>"/>
        </div>
        <div class="col-md-6 align-right padding-top-sm">
            <?php if (isset($currentSprint->id) && $currentSprint->id != '' && $login::userIsAtLeast("clientManager")) { ?>
                <a href="<?=BASE_URL ?>/sprints/delSprint/<?php echo $currentSprint->id; ?>" class="delete formModal sprintModal"><i class="fa fa-trash"></i> <?=$this->__('links.delete_sprint') ?></a>
            <?php } ?>
        </div>
    </div>

</form>


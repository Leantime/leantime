<?php
defined('RESTRICTED') or die('Restricted access');
$project = $this->get('project');
?>

<h4 class="widgettitle title-light"><?php echo sprintf($this->__('headlines.duplicate_project_x'), $project['name']); ?></h4>

<?php echo $this->displayNotification(); ?>

<form class="formModal" method="post" action="<?=BASE_URL ?>/projects/duplicateProject/<?php echo $project['id'];?>">

    <label><?=$this->__('label.newProjectName') ?></label>
    <input type="text" name="projectName" value="<?=$this->__('label.copy_of')?> <?php $this->e($project['name'])?>" /><br />

    <label><?=$this->__('label.planned_start_date') ?></label>
    <input type="text" name="startDate" class="projectDateFrom" value="<?php echo $this->getFormattedDateString( date("Y-m-d") ) ?>" placeholder="<?=$this->__('language.jsdateformat') ?>" id="sprintStart" /><br />

    <label><?=$this->__('label.client_product') ?></label>
    <select name="clientId" id="clientId">
        <?php foreach($this->get('allClients') as $row){ ?>
            <option value="<?php echo $row['id']; ?>"
                <?php if($project['clientId'] == $row['id']) { ?> selected=selected
                <?php } ?>><?php $this->e($row['name']); ?></option>
        <?php } ?>
    </select>
    <br />
    <input style="float:left; margin-right:5px;"
           type="checkbox" name="assignSameUsers" id="assignSameUsers"/>
    <label for="assignSameUsers"><?php echo $this->__('label.assignSameUsers') ?></label>

    <br />

    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="<?=$this->__('buttons.duplicate') ?>"/>
        </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
    </div>

</form>


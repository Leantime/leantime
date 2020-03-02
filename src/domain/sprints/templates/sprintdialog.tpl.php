<?php
  $currentSprint = $this->get('sprint');
?>

<h4 class="widgettitle title-light"><i class="fa fa-rocket"></i> Sprint <?php echo $currentSprint->name?></h4>

<?php echo $this->displayNotification();

$id = "";
if(isset($currentSprint->id)) {$id = $currentSprint->id;
}
?>

<form class="formModal" method="post" action="<?=BASE_URL ?>/sprints/editSprint/<?php echo $id;?>" style="min-width: 320px;">

    <label>Sprint Name</label>
    <input type="text" name="name" value="<?php echo $currentSprint->name?>" placeholder="Sprint X"/><br />

    <label>First Day</label>
    <input type="text" name="startDate" value="<?php echo $currentSprint->startDate?>" placeholder="mm/dd/yyyy" id="sprintStart" /><br />

    <label>Last Day</label>
    <input type="text" name="endDate" value="<?php echo $currentSprint->endDate?>"  placeholder="mm/dd/yyyy" id="sprintEnd"  />

    <a href="javascript:void(0)" class="infoToolTip" data-placement="left" data-toggle="tooltip" data-original-title="We recommend a sprint to be 14 days long">
        &nbsp;<i class="fa fa-question-circle"></i>&nbsp;</a>
    <br />


    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="Save"/>
        </div>
        <div class="col-md-6 align-right padding-top-sm">
            <?php if (isset($currentSprint->id) && $currentSprint->id != '' && ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager')) { ?>
                <a href="<?=BASE_URL ?>/sprints/delSprint/<?php echo $currentSprint->id; ?>" class="delete formModal sprintModal"><i class="fa fa-trash"></i> Delete Sprint</a>
            <?php } ?>
        </div>
    </div>

</form>


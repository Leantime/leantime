<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_events_2p66.svg");
                echo"</div>";?>
            <h3 class="primaryColor"><?php echo $this->__('headlines.congrats_on_your_project') ?></h3><br />
            <?php echo $this->__('notifications.project_created_successfully') ?>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12 align-center">
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><?php echo $this->__('links.close') ?></a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('projectSuccess')"><?php echo $this->__('links.close_dont_show_again') ?></a>
        </div>
    </div>


</div>

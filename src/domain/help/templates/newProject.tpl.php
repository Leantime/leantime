<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_Organizing_projects_0p9a.svg");
                echo"</div>";?><br />
            <h3 class="primaryColor"><?php echo $this->__('headlines.welcome_to_your_project') ?></h3><br />
            <?php echo $this->__('text.new_project_helper_content') ?>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12 align-center">
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><?php echo $this->__('links.close') ?></a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('newProject')"><?php echo $this->__('links.close_dont_show_again') ?></a>
        </div>
    </div>


</div>

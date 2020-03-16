<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_Organizing_projects_0p9a.svg");
                echo"</div>";?><br />
            <h3 class="primaryColor"></h3><br />
            <?php echo $this->__('text.show_projects_helper_content') ?>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">

            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><?php echo $this->__('links.close') ?></a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('showProjects')"><?php echo $this->__('links.close_dont_show_again') ?></a>
        </div>
    </div>


</div>

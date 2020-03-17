<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_new_ideas_jdea.svg");
                echo"</div>";?>
            <h3 class="primaryColor"><?php echo $this->__('headlines.welcome_to_organized_idea_board') ?></h3><br />
            <p><?php echo $this->__('text.advanced_boards_helper_content') ?>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><?php echo $this->__('links.close') ?></a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('advancedIdeaBoards')"><?php echo $this->__('this.close_dont_show_again') ?></a>
        </div>
    </div>


</div>

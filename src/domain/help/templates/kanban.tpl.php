<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_scrum_board_cesn.svg");
                echo"</div>";?>
            <h3 class="primaryColor"><?php echo $this->__('headlines.the_kanban_board') ?></h3><br />
            <p><?php echo $this->__('text.kanban_helper_content') ?></p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="leantime.helperController.startKanbanTour();" class="btn btn-primary"><?php echo $this->__('buttons.take_kanban_tour') ?></a><br />
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><?php echo $this->__('links.close') ?></a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('kanban')"><?php echo $this->__('links.close_dont_show_again') ?></a>
        </div>
    </div>


</div>

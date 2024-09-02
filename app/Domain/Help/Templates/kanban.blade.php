<?php
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:300px' class='svgContainer'>
                <?php    echo file_get_contents(ROOT . "/dist/images/svg/undraw_scrum_board_cesn.svg");
                echo"</div>";?>
                <br />
            <h1><?php echo $tpl->__('headlines.the_kanban_board') ?></h1><br />
            <p><?php echo $tpl->__('text.kanban_helper_content') ?></p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="leantime.helperController.startKanbanTour();" class="btn btn-primary"><?php echo $tpl->__('buttons.take_kanban_tour') ?></a><br />

            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('kanban')"><?php echo $tpl->__('links.close_dont_show_again') ?></a>
        </div>
    </div>


</div>

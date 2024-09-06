<?php
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT . "/dist/images/svg/undraw_adjustments_p22m.svg");
                echo"</div>";?>
            <h3 class="primaryColor"><?php echo $tpl->__('headlines.welcome_to_your_roadmap') ?></h3><br />
            <?php echo $tpl->__('text.roadmap_helper_content') ?>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><?php echo $tpl->__('links.close') ?></a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('roadmap')"><?php echo $tpl->__('links.close_dont_show_again') ?></a>
        </div>
    </div>


</div>

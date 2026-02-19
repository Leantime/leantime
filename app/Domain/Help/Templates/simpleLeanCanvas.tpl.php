<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
            <?php echo file_get_contents(ROOT.'/dist/images/svg/undraw_design_data_khdb.svg');
echo '</div>'; ?>

            <h3 class="primaryColor"><?php echo $tpl->__('headlines.welcome_to_simple_research_board') ?></h3><br />
            <?php echo $tpl->__('text.simple_lean_canvas_helper_content') ?>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><?php echo $tpl->__('links.close') ?></a><br />
        </div>
    </div>


</div>

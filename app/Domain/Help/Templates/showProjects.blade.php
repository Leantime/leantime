<?php
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php echo file_get_contents(ROOT.'/dist/images/svg/undraw_Organizing_projects_0p9a.svg');
echo '</div>'; ?><br />
            <h3 class="primaryColor"></h3><br />
            {{ __("text.show_projects_helper_content") }}
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">

            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()">{{ __("links.close") }}</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('showProjects')">{{ __("links.close_dont_show_again") }}</a>
        </div>
    </div>


</div>

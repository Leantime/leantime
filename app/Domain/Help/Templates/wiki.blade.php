<?php
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php echo file_get_contents(ROOT.'/dist/images/svg/undraw_real_time_collaboration_c62i.svg');
echo '</div>'; ?>
            <h3 class="primaryColor">Documentation where you can find it</h3><br />
            <p>Our docs allow you to write and share documentation with your team. You can create multiple spaces to organize your documentation into teams, areas or document category.<br/>
                Create documents to share knowledge, processes and procedures. You can also create a document to share a link to a file or a folder in your cloud storage.<br/>
            </p>
            <br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('fullLeanCanvas')">{{ __("links.close_dont_show_again") }}</a>
        </div>
    </div>


</div>

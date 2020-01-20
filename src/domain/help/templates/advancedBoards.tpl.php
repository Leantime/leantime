<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_new_ideas_jdea.svg");
                echo"</div>";?>
            <h3 class="primaryColor">Welcome to the organized idea board!</h3><br />
            <p>Ideas evolve, grow and mature. This is the place to keep track of them. <br />Move idea cards from one column to the next to reflect their current state.<br />
            This is a good place to start research, develop prototypes and validate these prototypes with a few customers before your start implementing.</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('advancedIdeaBoards')">Close and don't show this screen again</a>
        </div>
    </div>


</div>

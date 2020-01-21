<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_Organizing_projects_0p9a.svg");
                echo"</div>";?><br />
            <h3 class="primaryColor">Welcome to your Project!</h3><br />
            <p>This is the place you'll describe, define, and set the parameters for your project.  This is important as it's the piece that communicates to the <br/>entire team what the project priorities, expectations, and guidelines are.</p>
            <p><br /><em>Things to think about here: the 4Cs.<br />Be Clear, be Concise, Complete and Credible.<br /><br /></em></p>
            <p>Mastering these things into your project descriptions keeps everyone on the same page and helps to minimize risks of error or missteps. </p>
            <br />Time to get started!<br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12 align-center">
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('newProject')">Close and don't show this screen again</a>
        </div>
    </div>


</div>

<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_Organizing_projects_0p9a.svg");
                echo"</div>";?><br />
            <h3 class="primaryColor">Welcome to Projects!</h3><br />
            <p>Projects are the heart of your business and leantime! Change, development, goals, and anything you can build or dream of will involve projects. <br /> Project management is simply the process of ensuring that your projects are delivered timely, correctly, and with real business value.
<br /><br />
                This is where your Projects will live.  Your projects page will allow you to create a project, quickly see to do numbers, hours, budget dollars and the client associated with the project.
                <br /><br />
                Use this page for a quick overview of all your projects.<br /><br /></p>

            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">

            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('showProjects')">Close and don't show this screen again</a>
        </div>
    </div>


</div>

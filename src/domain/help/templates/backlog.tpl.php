<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_schedule_pnbk.svg");
                echo"</div>";?><br />
            <h3 class="primaryColor">Welcome to Your Backlog!</h3><br />
            <p>A backlog is the home of all of your to-dos; those you are working on currently and plan to work on.<br />
                <br/>To make progress towards completing your backlog we recommend working in short iterations - Sprints.<br />
                Sprints are two week timed intervals that allow you to focus on smaller, deliverable chunks of work. <br/>At the end of two weeks you should have something to "demo". Something that provides value to your users or clients.
                <br/><br/>On this screen, you'll be able to organize and manage your to-do list, prioritize your backlog via drag and drop and move Backlog items into your current Sprint.<br/>
            Want more info? Start the tour or close to get started!</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="leantime.helperController.startBacklogTour();" class="btn btn-primary"><i class="fas fa-map-signs"></i> Take the Backlog Tour</a><br />
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('backlog')">Close and don't show this screen again</a>
        </div>
    </div>


</div>

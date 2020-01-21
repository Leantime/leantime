<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_adjustments_p22m.svg");
                echo"</div>";?>
            <h3 class="primaryColor">Welcome to Your Roadmap!</h3><br />
            <p>Roadmaps are helpful for scaling down large projects into smaller and more manageable chunks.<br /><br /></p>
            <p>To create your roadmap, we recommend setting milestones that can be achieved within a 3 month time span.</p>
            <p>Milestones are checkpoints on the way to project completion.<br /> Simply speaking: If you're taking a cross country road trip, then each state you pass through could be a milestone.<br/>
                <br />Click "Add Milestone" on the top left hand side to get started.  Set the date on this tab or in the roadmap chart itself by clicking and dragging the milestone within the calendar. </p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('roadmap')">Close and don't show this screen again</a>
        </div>
    </div>


</div>

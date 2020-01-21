<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_time_management_30iu.svg");
                echo"</div>";?><br />
            <h3 class="primaryColor">The Timesheets!</h3>
            <p>Timesheets are the place to track the actual hours worked on a To-Do.<br /> This is helpful for billing purpose but also to validate how well you are planning your tasks upfront. <br />
            Overtime leantime can adjust due dates to account for planning discrepencies.</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('mytimesheets')">Close and don't show this screen again</a>
        </div>
    </div>


</div>

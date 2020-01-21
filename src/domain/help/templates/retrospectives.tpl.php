<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_team_spirit_hrr4.svg");
                echo"</div>";?>
            <h3 class="primaryColor">Welcome to Retrospectives!</h3><br />
            <p>Retrospectives are quick reflections about the work, team functioning, and of course progress.  <br/>
                This is where your team can take a moment to hone in on what went well, what didn't go well and what could be done differently next time. <br/>
            We recommend running a review meeting in regular intervals, or at minimum, at the end of each Milestone.<br/>
            </p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('retrospectives')">Close and don't show this screen again</a>
        </div>
    </div>


</div>

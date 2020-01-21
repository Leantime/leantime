<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_design_data_khdb.svg");
                echo"</div>";?>
            <h3 class="primaryColor">Welcome to the Full Research Board!</h3><br />
            <p>The full research board will take you through the full idea validation process.  <br/>
            On this page, you'll again address your Customer Segment, Problem, Solution (but don't worry, if you filled that out on the Simple board, you'll see it again here!).<br/>
                <br/>From there, you'll move into <br/>
                Unique Value Proposition (What makes your idea better than other offerings?), <br/>
                Distribution Channels (How will you get your solution to your customers?)<br/>
                Revenue Streams (How will your solution make money?)<br/>
                Cost Structure (How much is your solution worth?)<br/>
                Key Metrics (How will you measure success?)<br/>
                and Unfair Advantage (What do you have that will help you succeed against the competition?)<br/>

                Answering these questions are designed to promote success, mitigate failure, and allow you to track and validate your assumptions.  Being Lean is about eliminating waste </br>
                and working to build products, business, and solutions that your customers need.(</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('fullLeanCanvas')">Close and don't show this screen again</a>
        </div>
    </div>


</div>

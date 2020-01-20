<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
            <?php    echo file_get_contents(ROOT."/images/svg/undraw_design_data_khdb.svg");
            echo"</div>";?>

            <h3 class="primaryColor">Welcome to The Simple Research Board!</h3><br />
            <p>The foundation of any project is rooted here -- in your research.  On this board, you'll focus on building for your customer by using the Problem Solution Fit.<br/><br />
                In Problem Solution fit, you'll start with identifying who your customer is. Think about demographics, what they do, and what they need.<br/>
            Next, you'll identify their struggles or problem. Finally, you'll add how you plan to solve their problem. <br/><br/>Building products, projects, or offerings that solve customer problems help promote product success and mitigate failure.<br/><br/>
            You may find that you have multiple ways to solve their problems.  That's great!  You can put them here or move to the Idea Board to keep track of those ideas.<br/>
            Once you've got this started, the next step will be to validate your assumptions about your customer, their problems, and your solutions. <br/>You can add that here or move<br/>
            to your Full Research Board to start collecting more information.</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('simpleLeanCanvas')">Close and don't show this screen again</a>
        </div>
    </div>


</div>

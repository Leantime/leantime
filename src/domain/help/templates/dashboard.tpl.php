<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <h3 class="primaryColor">Welcome to Leantime!</h3>
            <p>We're glad you're here.  Let's take a minute to get acquainted.<br />
                 </p>
            <br /><br />
        </div>
    </div>

    <div class="row onboarding">
        <div class="col-md-4">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_design_data_khdb.svg");
                echo"</div>";?>

                <br />
            <h4 class="primaryColor">1. Discover!</h4>

            <p>Our Business Research section is your new home for your ideas. This section is designed to take you through the steps of customer development, problem research, and solution ideation. </p>

        </div>
        <div class="col-md-4">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_scrum_board_cesn.svg");
                echo"</div>";?>
                <br />
            <h4 class="primaryColor">2. Plan!</h4>

            <p>Our roadmap is designed to take you from smaller manageable milestones to big picture completion.  Plan for milestones about 3 months long and use these project markers to stay on target.</p>
        </div>
        <div class="col-md-4">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_time_management_30iu.svg");
                echo"</div>";?>
                <br />
            <h4 class="primaryColor">3. Track!</h4>

            <p>This is where you’ll spend most of your time - doing.  Plan your 2 week Sprints and execute successfully with the Kanban Board and our Backlog tools.  Use Retrospectives to grow for the next Sprint.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <br /><br />
            <p>
                <br /></p>
            <a href="javascript:void(0);" class="btn btn-primary" onclick="leantime.helperController.startDashboardTour()"><i class="fas fa-map-signs"></i> Take the Full Tour</a><br />
            <a href="<?=BASE_URL ?>/projects/newProject">Skip the tour and Start a Project</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('dashboard')">Skip the tour & don't show again</a>
        </div>
    </div>


</div>

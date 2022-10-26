<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <h3 class="primaryColor"><?php echo $this->__('headlines.welcome_to_leantime') ?></h3>
            <p><?php echo $this->__('text.glad_youre_here') ?><br />
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
            <h4 class="primaryColor"><?php echo $this->__('headlines.step_one_discover') ?></h4>

            <p><?php echo $this->__('text.business_research_section') ?></p>

        </div>
        <div class="col-md-4">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_scrum_board_cesn.svg");
                echo"</div>";?>
                <br />
            <h4 class="primaryColor"><?php echo $this->__('headlines.step_two_plan') ?></h4>

            <p><?php echo $this->__('text.our_roadmap_design') ?></p>
        </div>
        <div class="col-md-4">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_time_management_30iu.svg");
                echo"</div>";?>
                <br />
            <h4 class="primaryColor"><?php echo $this->__('headlines.step_three_track') ?></h4>

            <p><?php echo $this->__('text.this_is_where_you_spend_most_time') ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <br /><br />
            <p>
                <br /></p>
            <a href="javascript:void(0);" class="btn btn-primary" onclick="leantime.helperController.startDashboardTour()"><?php echo $this->__('buttons.take_full_tour') ?></a><br />
            <a href="<?=BASE_URL ?>/projects/newProject"><?php echo $this->__('links.skip_tour_start_project') ?></a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('dashboard')"><?php echo $this->__('links.skip_tour_dont_show_again') ?></a>
        </div>
    </div>


</div>

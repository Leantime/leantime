
<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <h1 style="font-size:var(--font-size-xxxl)">Stay on top of your project</h1><br />
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT . "/dist/images/svg/undraw_joyride_re_968t.svg");
                echo"</div>";?>

            </div>
        </div>
    </div>

    <div class="row onboarding">

        <div class="col-md-4"  style="max-width:400px;">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa fa-check-double"></i>
                </div>

                <span class="userName">
                    <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('projectDashboard');" target="_blank">
                        <strong>Project Checklist</strong>
                    </a>
                </span>

                Starting with the project checklist, you can quickly see what needs to be done next. The project checklist is list of activities you should do to ensure your projects are well defined, planned and executed. <br />
                <br /><br />

                <div class="clearall"></div>

            </div>
        </div>

        <div class="col-md-4" style="max-width:400px;">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa-solid fa-list-check"></i>
                </div>

                <span class="userName">
                    <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('projectDashboard')" target="_blank">
                        <strong>Latest To-Dos</strong>
                    </a>
                </span>

                Next you will see the latest To-Do's that have been added to the project. Make sure all To-Dos have a priority, effort and milestone assigned to them. Next assign them to your team members.<br />
                <br /><br />

                <div class="clearall"></div>

            </div>
        </div>

        <div class="col-md-4" style="max-width:400px;">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa fa-bullhorn"></i>
                </div>

                <span class="userName">
                    <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('projectDashboard')" target="_blank">
                        <strong>Status Updates</strong>
                    </a>
                </span>

                Keep your team and stakeholders up to date using our "Status Update" feature. Write a short update and set the project to "On Track", "At Risk" or "Off Track".<br />
                <br /><br />

                <div class="clearall"></div>

            </div>
        </div>


    </div>


    <div class="row">
        <div class="col-md-12 center">
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('projectDashboard')"><?php echo $tpl->__('links.close_dont_show_again') ?></a>
        </div>
    </div>


</div>

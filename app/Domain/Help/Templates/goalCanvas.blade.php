
<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <h1 style="font-size:var(--font-size-xxxl);">Manage goals and drive outcomes</h1><br />
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT . "/dist/images/svg/undraw_join_re_w1lh.svg");
                echo"</div>";?>

            </div>
        </div>

    </div>


    <div class="row onboarding">

        <div class="col-md-4" style="max-width:400px;">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa fa-bullseye"></i>
                </div>

                <span class="userName">
                    <a href="javascript:void(0);" target="_blank">
                        <strong>Define Goals</strong>
                    </a>
                </span>

                This section will help you define goals and metrics that you can track over time.


                <br /><br />
                <div class="clearall"></div>

            </div>
        </div>


        <div class="col-md-4" style="max-width:400px;">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa-solid fa-folder-tree"></i>
                </div>

                <span class="userName">
                    <a href="javascript:void(0);" target="_blank">
                        <strong>Organize Teams</strong>
                    </a>
                </span>

                Create multiple boards to organize your goals into timeframes, teams or departments.

                <br /><br />
                <div class="clearall"></div>

            </div>
        </div>

        <div class="col-md-4" style="max-width:400px;">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa-solid fa-link"></i>
                </div>

                <span class="userName">
                    <a href="javascript:void(0);" target="_blank">
                        <strong>Link Milestones</strong>
                    </a>
                </span>

                You can link your goals to milestones and see the actual task progress towards accomplishing your goals.

                <br /><br />
                <div class="clearall"></div>

            </div>
        </div>

    </div>

    <div class="row center">
        <div class="col-md-12">
            <p>
                <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('goalCanvas')">{{ __("links.close_dont_show_again") }}</a>
            </p>
        </div>
    </div>

</div>

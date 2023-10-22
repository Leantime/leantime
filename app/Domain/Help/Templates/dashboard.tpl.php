<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg" style="max-width:1200px;">

    <div class="row">
        <div class="col-md-12">
            <h1 style="font-size:var(--font-size-xxxl);">Welcome to Leantime!<br />What do you want to do first?</h1><br />
            <div style='width:300px' class='svgContainer'>
                <?php    echo file_get_contents(ROOT . "/dist/images/svg/undraw_party_re_nmwj.svg"); ?>
            </div>


            <br />
            <br />
        </div>
    </div>


    <div class="row onboarding">

        <div class="col-md-3">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa fa-check-circle"></i>
                </div>

                <span class="userName">
                    <a href="<?=BASE_URL ?>/dashboard/show" target="_blank">
                        <strong>Set up the project</strong>
                    </a>
                </span>

                Learn about the project set up in Leantime and check off the items as you go.
                <br /><br />
                <a href="<?=BASE_URL ?>/dashboard/show" class="btn btn-primary">Set Up a Project <i class="fa-solid fa-arrow-right"></i></a>
                <div class="clearall"></div>

            </div>
        </div>


        <div class="col-md-3">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa fa-bullseye"></i>
                </div>

                <span class="userName">
                    <a href="https://docs.leantime.io" target="_blank">
                        <strong>Define Goals & Metrics</strong>
                    </a>
                </span>
                Accomplish what you set out to do and keep your team focused with s.m.a.r.t goals and metrics.
                <br /><br />
                <a href="<?=BASE_URL?>/goalcanvas/dashboard" class="btn btn-primary">Create Goals <i class="fa-solid fa-arrow-right"></i></a>
                <div class="clearall"></div>

            </div>
        </div>

        <div class="col-md-3">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa-solid fa-lightbulb"></i>
                </div>

                <span class="userName">
                    <a href="" target="_blank">
                        <strong> Collect Ideas</strong>
                    </a>
                </span>

                Got so many thoughts and ideas that you just need to start writing them down? Do it right here.
                <br /><br />
                <a href="<?=BASE_URL?>/ideas/showBoards" class="btn btn-primary">Track Ideas <i class="fa-solid fa-arrow-right"></i></a>

                <div class="clearall"></div>

            </div>
        </div>


        <div class="col-md-3">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa fa-thumbtack"></i>
                </div>

                <span class="userName">
                    <a href="<?=BASE_URL?>/tickets/showKanban" target="_blank">
                        <strong> Add Tasks</strong>
                    </a>
                </span>

                Start adding and prioritizing tasks, then create milestones and organize your tasks on a timeline.
                <br /><br />
                <a href="<?=BASE_URL?>/tickets/showKanban" class="btn btn-primary">Create To-Dos <i class="fa-solid fa-arrow-right"></i></a>
                <div class="clearall"></div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 center">
            <br />
            <a href="javascript:void(0);" class="btn btn-default" onclick="leantime.helperController.startDashboardTour()">Take a quick tour of the UI</a><br /><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('dashboard')">Skip and don't show this page again</a>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <br />
                 </div>
    </div>


</div>

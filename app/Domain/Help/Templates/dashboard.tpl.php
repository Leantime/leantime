<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg" style="max-width:1200px;">

    <div class="row">
        <div class="col-md-12">
            <h1 style="font-size:var(--font-size-xxxl);">Welcome to Leantime!<br />What do you want to do next?</h1><br />
            <div style='width:300px' class='svgContainer'>
                <?php    echo file_get_contents(ROOT . "/dist/images/svg/undraw_party_re_nmwj.svg"); ?>
            </div>


            <br />
            <br />
        </div>
    </div>


    <div class="row onboarding">

        <div class="col-md-6">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa-solid fa-briefcase"></i>
                </div>

                <span class="userName">
                    <a href="<?=BASE_URL ?>/dashboard/show" target="_blank">
                        <strong>Set up your project</strong>
                    </a>
                </span>

                Learn about the project set up in Leantime and check off the items as you go.
                <br /><br />
                <a href="<?=BASE_URL ?>/dashboard/show" class="btn btn-primary">Set Up Your Project <i class="fa-solid fa-arrow-right"></i></a>
                <div class="clearall"></div>

            </div>
        </div>


        <div class="col-md-6">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>

                <span class="userName">
                    <a href="/" target="_blank">
                        <strong>Explore Your Dashboard</strong>
                    </a>
                </span>
                Explore your personal dashboard and see how you can timebox your tasks.
                <br /><br />
                <a href="<?=BASE_URL?>" class="btn btn-primary">Explore Your Dashboard <i class="fa-solid fa-arrow-right"></i></a>
                <div class="clearall"></div>

            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12 center">
            <br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('dashboard')">Skip and don't show this page again</a>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <br />
                 </div>
    </div>


</div>

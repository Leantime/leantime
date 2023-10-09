<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg" style="max-width:1200px;">

    <div class="row">
        <div class="col-md-12">
            <div style='width:300px' class='svgContainer'>
                <?php    echo file_get_contents(ROOT . "/dist/images/svg/undraw_party_re_nmwj.svg"); ?>
            </div>
            <br />
            <h1><?php echo $tpl->__('headlines.welcome_to_leantime') ?></h1>
            <p><?php echo $tpl->__('text.glad_youre_here') ?><br /><br /></p>
            <p><?php echo $tpl->__('text.helpful_resources') ?><br /></p>
            <br />
            <br />
        </div>
    </div>

    <div class="row onboarding">
        <div class="col-md-3">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa fa-message"></i>
                </div>

                <span class="userName">
                    <a href="https://discord.gg/4zMzJtAq9z" target="_blank">
                        Community Chat
                    </a>
                </span>

                <?=$tpl->__('text.discordChat') ?>
                <div class="clearall"></div>

            </div>
        </div>

        <div class="col-md-3">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa fa-question-circle-o"></i>
                </div>

                <span class="userName">
                    <a href="https://docs.leantime.io" target="_blank">
                        <?=$tpl->__('links.documentation') ?>
                    </a>
                </span>

                <?=$tpl->__('text.documentation') ?>
                <div class="clearall"></div>

            </div>
        </div>

        <div class="col-md-3">
            <div class="profileBox">
                <div class="commentImage icon">
                    <i class="fa fa-lightbulb"></i>
                </div>

                <span class="userName">
                    <a href="https://github.com/Leantime/leantime/issues" target="_blank">
                        <?=$tpl->__('links.feature_idea') ?>
                    </a>
                </span>

                <?=$tpl->__('text.feature_ideas') ?>
                <div class="clearall"></div>

            </div>
        </div>

        <div class="col-md-3">
            <div class="profileBox">
                <div class="commentImage icon">

                    <i class="fa-solid fa-headset"></i>
                </div>

                <span class="userName">
                    <a href="https://leantime.io/contact/" target="_blank">
                         <?=$tpl->__('links.support') ?>
                    </a>
                </span>

                <?=$tpl->__('text.support') ?>
                <div class="clearall"></div>

            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <br />
            <a href="javascript:void(0);" class="btn btn-primary" onclick="leantime.helperController.startDashboardTour()"><?php echo $tpl->__('buttons.take_quick_tour') ?></a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('dashboard')"><?php echo $tpl->__('links.skip_tour_dont_show_again') ?></a>
        </div>
    </div>


</div>

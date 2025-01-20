<?php

/**
 * Strategy Brief - Template
 */
defined('RESTRICTED') or exit('Restricted access');

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'sb';
?>

<?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'showCanvasTop'), array_merge($__data, ['canvasName' => 'sb']))->render(); ?>

<?php
$stakeholderStatusLabels = $statusLabels;
$varsToPass = array_merge($__data, ['statusLabels' => []]);
?>
<?php if (count($allCanvas) > 0) { ?>
<div id="sortableCanvasKanban" class="sortableTicketList disabled">
    <div class="row-fluid">
        <div class="column" style="width: 100%; min-width: calc(4 * 250px);">

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_description']))->render(); ?>
                </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_industry']))->render(); ?>
                </div>
            </div>



            <?php $varsToPass = array_merge($__data, ['statusLabels' => $stakeholderStatusLabels]); ?>
            <div class="row canvas-row" id="stakeholderRow">
                <div class="column" style="width:25%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_st_design']))->render(); ?>
                </div>
                <div class="column" style="width:25%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_st_decision']))->render(); ?>
                </div>
                <div class="column" style="width:25%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_st_experts']))->render(); ?>
                </div>
                <div class="column" style="width:25%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_st_support']))->render(); ?>
                </div>
            </div>
            <?php $varsToPass = array_merge($__data, ['statusLabels' => []]); ?>

            <div class="row canvas-row" id="financialsRow">
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_budget']))->render(); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_time']))->render(); ?>
                </div>
            </div>

            <div class="row canvas-row" id="culturechangeRow">
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_culture']))->render(); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_change']))->render(); ?>
                </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($varsToPass, ['canvasName' => 'sb', 'elementName' => 'sb_principles']))->render(); ?>
                </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <h4 class="widgettitle title-primary center"><i class='fas fa-person-falling'></i>
                        <?= $tpl->__('box.sb.risks') ?></h4>
                    <div class="contentInner even" style="padding-top: 10px;">
                        <?php echo sprintf($tpl->__('text.sb.risks_analysis'), BASE_URL); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<?php } ?>

<?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'showCanvasBottom'), array_merge($__data, ['canvasName' => 'sb']))->render(); ?>

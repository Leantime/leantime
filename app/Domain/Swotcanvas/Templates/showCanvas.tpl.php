<?php

/**
 * Template
 */
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'swot';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasTop'),
    array_merge($__data, ['canvasName' => 'swot'])
)->render(); ?>

    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(2 * 250px);">

            <div class="row canvas-row">
                <div class="column" style="width: 50%">
                  <h4 class="widgettitle title-primary center canvas-title-only">
                    <large><i class="far fa-thumbs-up"></i> <?= $tpl->__('box.header.swot.helpful') ?></large>
                  </h4>
                </div>
                <div class="column" style="width: 50%">
                  <h4 class="widgettitle title-primary center" style="border-radius: var(--box-radius-small);">
                    <large><i class="far fa-thumbs-down"></i> <?= $tpl->__('box.header.swot.harmful') ?></large>
                  </h4>
                </div>
            </div>

            <div class="row canvas-row" id="firstRow">
                <div class="column" style="width: 50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'swot', 'elementName' => 'swot_strengths'])
                    )->render(); ?>
                </div>
                <div class="column"  style="width: 50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'swot', 'elementName' => 'swot_weaknesses'])
                    )->render(); ?>
                </div>
            </div>

            <div class="row canvas-row" id="secondRow">
                <div class="column" style="width: 50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'swot', 'elementName' => 'swot_opportunities'])
                    )->render(); ?>
                </div>
                <div class="column" style="width: 50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'swot', 'elementName' => 'swot_threats'])
                    )->render(); ?>
                </div>
            </div>

          </div></div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasBottom'),
    array_merge($__data, ['canvasName' => 'swot'])
)->render(); ?>

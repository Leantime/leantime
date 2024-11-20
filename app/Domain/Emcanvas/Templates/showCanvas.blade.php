<?php

/**
 * Template
 */
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'em';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasTop'),
    array_merge($__data, ['canvasName' => 'em'])
)->render(); ?>

    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(4 * 250px);">

                <div class="row canvas-row">
                    <div class="column" style="width:100%">
                        <h4 class="widgettitle title-primary center canvas-title-only">
                            <i class="fas fa-bullseye"></i> <?= $tpl->__('box.em.header.goal') ?>
                        </h4>
                    </div>
                </div>

                <div class="row canvas-row" id="firstRow">
                    <div class="column" style="width: 50%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'em', 'elementName' => 'em_who'])
                        )->render(); ?>
                    </div>
                    <div class="column" style="width: 50%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'em', 'elementName' => 'em_what'])
                        )->render(); ?>
                    </div>
                </div>

                <div class="row canvas-row">
                    <div class="column" style="width: 100%">
                        <h4 class="widgettitle title-primary center canvas-title-only">
                            <i class="fas fa-heart"></i> <?= $tpl->__('box.em.header.empathy') ?>
                        </h4>
                    </div>
                </div>

                <div class="row canvas-row" id="secondRow">
                    <div class="column" style="width: 25%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'em', 'elementName' => 'em_see'])
                        )->render(); ?>
                    </div>
                    <div class="column" style="width: 25%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'em', 'elementName' => 'em_say'])
                        )->render(); ?>
                    </div>
                    <div class="column" style="width: 25%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'em', 'elementName' => 'em_do'])
                        )->render(); ?>
                    </div>
                    <div class="column" style="width: 25%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'em', 'elementName' => 'em_hear'])
                        )->render(); ?>
                    </div>
                </div>

                <div class="row canvas-row">
                    <div class="column" style="width: 100%">
                        <h4 class="widgettitle title-primary center canvas-title-only">
                            <i class="fas fa-7"></i> <?= $tpl->__('box.em.header.think_feel') ?>
                        </h4>
                    </div>
                </div>

                <div class="row canvas-row" id="thirdRow">
                    <div class="column" style="width: 50%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'em', 'elementName' => 'em_pains'])
                        )->render(); ?>
                    </div>
                    <div class="column" style="width: 50%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'em', 'elementName' => 'em_gains'])
                        )->render(); ?>
                    </div>
                </div>

                <div class="row canvas-row" id="fourthRow">
                    <div class="column" style="width: 100%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'em', 'elementName' => 'em_motives'])
                        )->render(); ?>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasBottom'),
    array_merge($__data, ['canvasName' => 'em'])
)->render(); ?>

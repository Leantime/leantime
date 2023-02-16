<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');

$canvasName = 'cp';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>

    <?php if (count($this->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(7 * 250px);">
              
                    <div class="row canvas-row">
                        <div class="column" style="width: 16%">
                        </div>
                        <div class="column" style="width: 84%">
                            <h4 class="widgettitle title-primary center canvas-title-only">
                                <large><i class="fa fa-user-doctor"></i> <?=$this->__('box.header.cp.cj') ?></large>
                            </h4>
                        </div>
                    </div>

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 16%">
                            <h4 class="widgettitle title-primary center canvas-element-title-empty">&nbsp;</h4>
                            <div class="contentInner even status_<?php echo $canvasName; ?> canvas-element-center-middle">
                                <strong><?=$this->__('box.label.cp.need') ?></strong></div>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_cj_rv';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_cj_rc';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_cj_e';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>

                    <div class="row canvas-row">
                        <div class="column" style="width: 16%">&nbsp;</div>
                        <div class="column center" style="width: 28%"><i class="fa fa-arrows-up-down"></i></div>
                        <div class="column center" style="width: 28%"><i class="fa fa-arrows-up-down"></i></div>
                        <div class="column center" style="width: 28%"><i class="fa fa-arrows-up-down"></i></div>
                    </div>
                             
                    <div class="row canvas-row">
                        <div class="column" style="width: 16%">
                        </div>
                        <div class="column" style="width: 84%">
                            <h4 class="widgettitle title-primary center canvas-title-only">
                                <large><i class="fa fa-barcode"></i> <?=$this->__('box.header.cp.ovp') ?></large>
                            </h4>
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 16%">
                            <h4 class="widgettitle title-primary center canvas-element-title-empty">&nbsp;</h4>
                            <div class="contentInner even status_<?php echo $canvasName; ?> canvas-element-center-middle">
                                <strong><?=$this->__('box.label.cp.unique') ?></strong></div>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_ou_rv';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_ou_rc';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_ou_e';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>
                    <div class="row canvas-row" id="thirdRow">
                        <div class="column" style="width: 16%">
                            <h4 class="widgettitle title-primary center canvas-element-title-empty">&nbsp;</h4>
                            <div class="contentInner even status_<?php echo $canvasName; ?> canvas-element-center-middle">
                                <strong><?=$this->__('box.label.cp.superior') ?></strong></div>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_os_rv';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_os_rc';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_os_e';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>
                    <div class="row canvas-row" id="fourthRow">
                        <div class="column" style="width: 16%">
                            <h4 class="widgettitle title-primary center canvas-element-title-empty">&nbsp;</h4>
                            <div class="contentInner even status_<?php echo $canvasName; ?> canvas-element-center-middle">
                              <strong><?=$this->__('box.label.cp.indifferent') ?></strong></div>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_oi_rv';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_oi_rc';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 28%">
                            <?php $elementName = 'cp_oi_e';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>
                
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

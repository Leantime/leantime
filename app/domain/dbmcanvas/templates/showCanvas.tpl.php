<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');

$canvasName = 'dbm';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>

    <?php if (count($this->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(8 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_cs';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_cr';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_ovp';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 13.33%">
                            <?php $elementName = 'dbm_kad';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 13.33%">
                            <?php $elementName = 'dbm_kac';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 13.33%">
                            <?php $elementName = 'dbm_kao';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>
                    
                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_cj';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_cd';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_ops';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width:40%">
                           <div class="row canvas-row" id="secondRowTop">
                               <div class="column" style="width:50%; padding-top: 0px">
                                   <?php $elementName = 'dbm_krp';
                                    require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                               </div>
                               <div class="column" style="width:50%; padding-top: 0">
                                   <?php $elementName = 'dbm_krc';
                                    require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                               </div>
                           </div>
                           <div class="row canvas-row" id="secondRowBottom">
                               <div class="column" style="width:50%; padding-bottom: 0">
                                    <?php $elementName = 'dbm_krl';
                                    require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                               </div>
                               <div class="column" style="width:50%; padding-bottom: 0">
                                   <?php $elementName = 'dbm_krs';
                                    require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                               </div>
                           </div>
                        </div>

                    </div>
                    
                    <div class="row canvas-row" id="thirdRow">
                        <div class="column" style="width: 50%">
                            <?php $elementName = 'dbm_fr';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 50%">
                            <?php $elementName = 'dbm_fc';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>
                              
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

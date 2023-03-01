<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');

$canvasName = 'ea';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>

    <?php if (count($this->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(4 * 250px);">
              
                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width:33.33%">
                            <?php $elementName = 'ea_political';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width:33.33%">
                            <?php $elementName = 'ea_economic';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width:33.33%">
                            <?php $elementName = 'ea_societal';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width:33.33%">
                            <?php $elementName = 'ea_technological';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width:33.33%">
                            <?php $elementName = 'ea_legal';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width:33.33%">
                            <?php $elementName = 'ea_ecological';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');

$canvasName = 'retros';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>

    <?php if (count($this->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(3 * 250px);">
              
                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 33%">
                            <?php $elementName = 'well';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 33%">
                            <?php $elementName = 'notwell';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                        <div class="column" style="width: 33%">
                            <?php $elementName = 'startdoing';
                            require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                        </div>
                    </div>
                
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

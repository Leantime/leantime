<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'risks';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(2 * 250px);">

            <div class="row canvas-row" id="firstRow">
                <div class="column" style="width:50%">
                    <?php $elementName = 'risks_imp_low_pro_high'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php $elementName = 'risks_imp_high_pro_high'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
            </div>

            <div class="row canvas-row" id="secondRow">
                <div class="column" style="width:50%">
                    <?php $elementName = 'risks_imp_low_pro_low'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php $elementName = 'risks_imp_high_pro_low'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
            </div>
          </div></div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

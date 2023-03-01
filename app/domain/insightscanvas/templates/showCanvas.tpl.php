<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');

$canvasName = 'insights';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>

    <?php if (count($this->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(5 * 250px);">

            <div class="row canvas-row" id="firstRow">
                <?php foreach ($canvasTypes as $key => $box) { ?>
                    <div class="column" style="width:20%">
                        <?php $elementName = $key;
                        require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                    </div>
                <?php } ?>
            </div>
          </div></div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'NEW';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">

            <div class="row" style="margin-left: 0px; margin-right: 0px">
                <div class="column" style="width:100%">
				    <h4 class="widgettitle title-primary center"><i class="fas <?=$canvasTypes['NEW_XXX']['icon'] ?>"></i> <?=$this->__($canvasTypes['NEW_XXX']['title']) ?></h4>
                    <?php $elementName = 'NEW_XXX'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

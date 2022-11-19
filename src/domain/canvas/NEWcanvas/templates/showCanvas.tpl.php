<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'NEW';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
			    <div class="column" style="width: 100%; min-width: calc(XXX * 250px);">

				    <div class="row canvas-row">
                        <div class="column" style="width:100%">
						    <?php $elementName = 'NEW_XXX'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
					    </div>
				    </div>

			    </div>
			</div>
	    </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

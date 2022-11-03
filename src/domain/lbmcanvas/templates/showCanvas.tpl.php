!<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'lbm';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
			    <div class="column" style="width: 100%; min-width: calc(3 * 250px);">
			  
				    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 33.33%">
                            <?php $elementName = 'lbm_customers'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
					    </div>
                        <div class="column" style="width: 33.33%">
			                <?php $elementName = 'lbm_offerings'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
					    </div>
                        <div class="column" style="width: 33.33%">
			                <?php $elementName = 'lbm_capabilities'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
					    </div>
				    </div>
				
				    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 100%">
						    <?php $elementName = 'lbm_financials'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
					    </div>
				    </div>
							  
			    </div>
			</div>
	    </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

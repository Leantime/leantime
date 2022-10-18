<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'lbm';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
			    <div class="column" style="width: 100%; min-width: calc(3 * 250px);">
			  
				    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 33.33%">
                            <?php $elementName = 'lbm_customers'; $bgColor = $canvasTypes['lbm_customers']['color'];
                                require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 33.33%">
			                <?php $elementName = 'lbm_offerings'; $bgColor = $canvasTypes['lbm_offerings']['color'];
                                require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 33.33%">
			                <?php $elementName = 'lbm_capabilities'; $bgColor = $canvasTypes['lbm_capabilities']['color'];
                                require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
				
				    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 100%">
						    <?php $elementName = 'lbm_financials'; $bgColor = $canvasTypes['lbm_financials']['color'];
                                require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
							  
			    </div>
			</div>
	    </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

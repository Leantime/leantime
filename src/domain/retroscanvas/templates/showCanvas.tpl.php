<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'retros';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
			    <div class="column" style="width: 100%; min-width: calc(3 * 250px);">
			  
				    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 33%">
						    <?php $elementName = 'well'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 33%">
						    <?php $elementName = 'notwell'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 33%">
						    <?php $elementName = 'startdoing'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
				
			    </div>
			</div>
	    </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

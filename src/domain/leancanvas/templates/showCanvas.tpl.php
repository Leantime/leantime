<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'lean';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
			    <div class="column" style="width: 100%; min-width: calc(5 * 250px);">
			  
				    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'problem'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 20%">
			                <?php $elementName = 'solution'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 20%">
			                <?php $elementName = 'uniquevalue'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 20%">
			                <?php $elementName = 'unfairadvantage'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 20%">
			                <?php $elementName = 'customersegment'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
				
				    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 20%">
                            <?php $elementName = 'alternatives'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 20%">
			                <?php $elementName = 'keymetrics'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 20%">
			                <?php $elementName = 'highlevelconcept'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 20%">
			                <?php $elementName = 'channels'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 20%">
			                <?php $elementName = 'earlyadopters'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
							  
				    <div class="row canvas-row" id="thirdRow">
                        <div class="column" style="width: 50%">
						    <?php $elementName = 'cost'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width: 50%">
						    <?php $elementName = 'revenue'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
							  
			    </div>
			</div>
	    </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

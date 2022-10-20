<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'sq';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
			    <div class="column" style="width: 100%; min-width: calc(500px);">
			  
				    <div class="row canvas-row">
                        <div class="column" style="width:100%">
						    <?php $elementName = 'sq_qa'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
				    <div class="row canvas-row">
                        <div class="column" style="width:100%">
						    <?php $elementName = 'sq_qb'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
				    <div class="row canvas-row">
                        <div class="column" style="width:100%">
						    <?php $elementName = 'sq_qc'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
				    <div class="row canvas-row">
                        <div class="column" style="width:100%">
						    <?php $elementName = 'sq_qd'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
				    <div class="row canvas-row">
                        <div class="column" style="width:100%">
						    <?php $elementName = 'sq_qe'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>
				
			    </div>
			</div>
	    </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

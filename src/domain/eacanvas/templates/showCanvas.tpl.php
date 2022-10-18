<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'ea';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
			    <div class="column" style="width: 100%; min-width: calc(4 * 250px);">
			  
				    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width:33.33%">
						    <?php $elementName = 'ea_political'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width:33.33%">
						    <?php $elementName = 'ea_economic'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width:33.33%">
						    <?php $elementName = 'ea_societal'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>

 				    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width:33.33%">
						    <?php $elementName = 'ea_technological'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width:33.33%">
						    <?php $elementName = 'ea_legal'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
                        <div class="column" style="width:33.33%">
						    <?php $elementName = 'ea_ecological'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					    </div>
				    </div>

			    </div>
			</div>
	    </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

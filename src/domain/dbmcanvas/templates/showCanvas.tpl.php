<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'dbm';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
			    <div class="column" style="width: 100%; min-width: calc(8 * 250px);">

			        <div class="row canvas-row" id="firstRow">
 			            <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_cs'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
 			            <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_cr'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
 			            <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_ovp'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
 			            <div class="column" style="width: 13.33%">
                            <?php $elementName = 'dbm_kad'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
 			            <div class="column" style="width: 13.33%">
                            <?php $elementName = 'dbm_kac'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
 			            <div class="column" style="width: 13.33%">
                            <?php $elementName = 'dbm_kao'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
                    </div>
					
			        <div class="row canvas-row" id="secondRow">
 			            <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_cj'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
 			            <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_cd'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
 			            <div class="column" style="width: 20%">
                            <?php $elementName = 'dbm_ops'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
						<div class="column" style="width:40%">
                           <div class="row canvas-row" id="secondRowTop">
					           <div class="column" style="width:50%; padding-top: 0px">
                                   <?php $elementName = 'dbm_krp'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                               </div>
                               <div class="column" style="width:50%; padding-top: 0">
                                   <?php $elementName = 'dbm_krc'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                               </div>
                           </div>
                           <div class="row canvas-row" id="secondRowBottom">
					           <div class="column" style="width:50%; padding-bottom: 0">
                                    <?php $elementName = 'dbm_krl'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                               </div>
                               <div class="column" style="width:50%; padding-bottom: 0">
                                   <?php $elementName = 'dbm_krs'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
					           </div>
                           </div>
                        </div>

                    </div>
					
			        <div class="row canvas-row" id="thirdRow">
 			            <div class="column" style="width: 50%">
                            <?php $elementName = 'dbm_fr'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
 			            <div class="column" style="width: 50%">
                            <?php $elementName = 'dbm_fc'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
		            </div>
							  
			    </div>
			</div>
	    </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

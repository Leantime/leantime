<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'obm';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
			    <div class="column" style="width: 100%; min-width: calc(5 * 250px);">

			        <div class="row canvas-row" id="firstRow">

 			            <div class="column" style="width: 20%">
                            <?php $elementName = 'obm_kp'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
				
                        <div class="column" style="width: 20%">
						    <div class="row canvas-row" id="firstRowTop">
					            <div class="column" style="width: 100%; padding-top: 0px">
                                    <?php $elementName = 'obm_ka'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                                 </div>
                            </div>
                            <div class="row canvas-row" id="firstRowBottom">
					            <div class="column" style="width: 100%">
                                    <?php $elementName = 'obm_kr'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                                </div>
                            </div>
                        </div>

 			            <div class="column" style="width: 20%">
                            <?php $elementName = 'obm_vp'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>

                        <div class="column" style="width: 20%">
						    <div class="row canvas-row" id="firstRowTop">
					            <div class="column" style="width: 100%; padding-top: 0px">
                                    <?php $elementName = 'obm_cr'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                                 </div>
                            </div>
                            <div class="row canvas-row" id="firstRowBottom">
					            <div class="column" style="width: 100%">
                                    <?php $elementName = 'obm_ch'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                                </div>
                            </div>
                        </div>

 			            <div class="column" style="width: 20%">
                            <?php $elementName = 'obm_cs'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>

                    </div>
					
			        <div class="row canvas-row" id="secondRow">

 			            <div class="column" style="width: 50%">
                            <?php $elementName = 'obm_fc'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>

 			            <div class="column" style="width: 50%">
                            <?php $elementName = 'obm_fr'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                        </div>
		            </div>
							  
			    </div>
			</div>
	    </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

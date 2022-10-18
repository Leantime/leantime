<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'em';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(4 * 250px);">
			  
            <div class="row canvas-row">
                <div class="column" style="width:100%">
			      <h4 class="widgettitle title-primary center canvas-title-only">
			        <i class="fas fa-bullseye"></i> <?=$this->__('box.em.header.goal') ?>
                  </h4>
                </div>
            </div>
					  
			<div class="row canvas-row" id="firstRow">
			  <div class="column" style="width: 50%">
			    <h4 class="widgettitle title-primary center">
			      <i class="fas <?=$canvasTypes['em_who']['icon'] ?>"></i> <?=$canvasTypes['em_who']['title'] ?></h4>
			    <?php $elementName = 'em_who'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
              </div>
			  <div class="column" style="width: 50%">
			    <h4 class="widgettitle title-primary center">
			      <i class="fas <?=$canvasTypes['em_what']['icon'] ?>"></i> <?=$canvasTypes['em_what']['title'] ?></h4>
			    <?php $elementName = 'em_what'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
              </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width: 100%">
			      <h4 class="widgettitle title-primary center canvas-title-only">
			        <i class="fas fa-heart"></i> <?=$this->__('box.em.header.empathy') ?>
                  </h4>
                </div>
            </div>

			<div class="row canvas-row" id="secondRow">
			  <div class="column" style="width: 25%">
			    <h4 class="widgettitle title-primary center">
			      <i class="fas <?=$canvasTypes['em_see']['icon'] ?>"></i> <?=$canvasTypes['em_see']['title'] ?></h4>
			    <?php $elementName = 'em_see'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
              </div>
			  <div class="column" style="width: 25%">
			    <h4 class="widgettitle title-primary center">
			      <i class="fas <?=$canvasTypes['em_say']['icon'] ?>"></i> <?=$canvasTypes['em_say']['title'] ?></h4>
			    <?php $elementName = 'em_say'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
              </div>
			  <div class="column" style="width: 25%">
			    <h4 class="widgettitle title-primary center">
			      <i class="fas <?=$canvasTypes['em_do']['icon'] ?>"></i> <?=$canvasTypes['em_do']['title'] ?></h4>
			    <?php $elementName = 'em_do'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
              </div>
			  <div class="column" style="width: 25%">
			    <h4 class="widgettitle title-primary center">
			      <i class="fas <?=$canvasTypes['em_hear']['icon'] ?>"></i> <?=$canvasTypes['em_hear']['title'] ?></h4>
			    <?php $elementName = 'em_hear'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
              </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width: 100%">
			      <h4 class="widgettitle title-primary center canvas-title-only">
			        <i class="fas fa-7"></i> <?=$this->__('box.em.header.think_feel') ?>
                  </h4>
                </div>
            </div>
			  
			<div class="row canvas-row" id="thirdRow">
			  <div class="column" style="width: 50%">
			    <h4 class="widgettitle title-primary center">
			      <i class="fas <?=$canvasTypes['em_pains']['icon'] ?>"></i> <?=$canvasTypes['em_pains']['title'] ?></h4>
			    <?php $elementName = 'em_pains'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
              </div>
			  <div class="column" style="width: 50%">
			    <h4 class="widgettitle title-primary center">
			      <i class="fas <?=$canvasTypes['em_gains']['icon'] ?>"></i> <?=$canvasTypes['em_gains']['title'] ?></h4>
			    <?php $elementName = 'em_gains'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
              </div>
            </div>
					
			<div class="row canvas-row" id="fourthRow">
			  <div class="column" style="width: 100%">
			    <h4 class="widgettitle title-primary center">
			      <i class="fas <?=$canvasTypes['em_motives']['icon'] ?>"></i> <?=$canvasTypes['em_motives']['title'] ?></h4>
			    <?php $elementName = 'em_motives'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
              </div>
            </div>
		  </div></div>
		</div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

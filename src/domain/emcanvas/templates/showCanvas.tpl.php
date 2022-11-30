<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'em';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>

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
			    <?php $elementName = 'em_who'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
              </div>
			  <div class="column" style="width: 50%">
			    <?php $elementName = 'em_what'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
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
			    <?php $elementName = 'em_see'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
              </div>
			  <div class="column" style="width: 25%">
			    <?php $elementName = 'em_say'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
              </div>
			  <div class="column" style="width: 25%">
			    <?php $elementName = 'em_do'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
              </div>
			  <div class="column" style="width: 25%">
			    <?php $elementName = 'em_hear'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
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
			    <?php $elementName = 'em_pains'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
              </div>
			  <div class="column" style="width: 50%">
			    <?php $elementName = 'em_gains'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
              </div>
            </div>

			<div class="row canvas-row" id="fourthRow">
			  <div class="column" style="width: 100%">
			    <?php $elementName = 'em_motives'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
              </div>
            </div>
		  </div></div>
		</div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

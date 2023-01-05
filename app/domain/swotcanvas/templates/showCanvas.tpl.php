<?php
/**
 * Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'swot';
?>

<?php require($this->getTemplatePath('canvas' ,'showCanvasTop.inc.php')); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(2 * 250px);">

            <div class="row canvas-row">
                <div class="column" style="width: 50%">
			      <h4 class="widgettitle title-primary center canvas-title-only">
			        <large><i class="far fa-thumbs-up"></i> <?=$this->__('box.header.swot.helpful') ?></large>
                  </h4>
                </div>
                <div class="column" style="width: 50%">
			      <h4 class="widgettitle title-primary center" style="border-radius: var(--box-radius-small);">
			        <large><i class="far fa-thumbs-down"></i> <?=$this->__('box.header.swot.harmful') ?></large>
                  </h4>
                </div>
            </div>
			  
            <div class="row canvas-row" id="firstRow">
                <div class="column" style="width: 50%">
                    <?php $elementName = 'swot_strengths'; require($this->getTemplatePath('canvas' ,'element.inc.php')); ?>
                </div>
                <div class="column"  style="width: 50%">
                    <?php $elementName = 'swot_weaknesses'; require($this->getTemplatePath('canvas' ,'element.inc.php')); ?>
                </div>
            </div>

            <div class="row canvas-row" id="secondRow">
                <div class="column" style="width: 50%">
                    <?php $elementName = 'swot_opportunities'; require($this->getTemplatePath('canvas' ,'element.inc.php')); ?>
                </div>
                <div class="column" style="width: 50%">
                    <?php $elementName = 'swot_threats'; require($this->getTemplatePath('canvas' ,'element.inc.php')); ?>
                </div>
            </div>

          </div></div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas' ,'showCanvasBottom.inc.php')); ?>

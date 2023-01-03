<?php
/**
 * Strategy Brief - Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'sb';
?>

<?php require($this->getTemplatePath('canvas', 'showCanvasTop.inc.php')); ?>
<?php
    $stakeholderStatusLabels = $statusLabels;
    $statusLabels = [];
?>
    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(4 * 250px);">

              <div class="row canvas-row">
                  <div class="column" style="width:100%">
                      <?php $elementName = 'sb_description'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                  </div>
              </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php $elementName = 'sb_industry'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
            </div>



			<?php $statusLabels = $stakeholderStatusLabels; ?>
            <div class="row canvas-row" id="stakeholderRow">
                <div class="column" style="width:25%">
                    <?php $elementName = 'sb_st_design'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
                <div class="column" style="width:25%">
                    <?php $elementName = 'sb_st_decision'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
                <div class="column" style="width:25%">
                    <?php $elementName = 'sb_st_experts'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
                <div class="column" style="width:25%">
                    <?php $elementName = 'sb_st_support'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
            </div>
			<?php $statusLabels = []; ?>

            <div class="row canvas-row" id="financialsRow">
                <div class="column" style="width:50%">
                    <?php $elementName = 'sb_budget'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php $elementName = 'sb_time'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
            </div>

            <div class="row canvas-row" id="culturechangeRow">
                <div class="column" style="width:50%">
                    <?php $elementName = 'sb_culture'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php $elementName = 'sb_change'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php $elementName = 'sb_principles'; require($this->getTemplatePath('canvas', 'element.inc.php')); ?>
                </div>
            </div>

			<div class="row canvas-row">
                <div class="column" style="width:100%">
				   <h4 class="widgettitle title-primary center"><i class='fas fa-person-falling'></i> <?=$this->__('box.sb.risks') ?></h4>
                   <div class="contentInner even" style="padding-top: 10px;">
				     <?php echo sprintf($this->__('text.sb.risks_analysis'), BASE_URL); ?>
                   </div>
                </div>
            </div>
          </div></div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require($this->getTemplatePath('canvas', 'showCanvasBottom.inc.php')); ?>

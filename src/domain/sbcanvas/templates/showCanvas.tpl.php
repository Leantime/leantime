<?php
/**
 * Strategy Brief - Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'sb';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>
<?php
    $stakeholderStatusLabels = $statusLabels;
    $statusLabels = [];
?>
    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(4 * 250px);">

            <div class="row canvas-row">
                <div class="column" style="width:100%">
		          <h4 class="widgettitle title-primary center"><i class='fas fa-list-check'></i> <?=$this->__('box.sb.title') ?></h4>
                  <div class="contentInner even" style="padding-top: 10px;"><?=$this->e($_SESSION['currentProjectName']); ?></div>
                </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php $elementName = 'sb_industry'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php $elementName = 'sb_description'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

			<?php $statusLabels = $stakeholderStatusLabels; ?>
            <div class="row" id="stakeholderRow canvas-row">
                <div class="column" style="width:25%">
                    <?php $elementName = 'sb_st_design'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
                <div class="column" style="width:25%">
                    <?php $elementName = 'sb_st_decision'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
                <div class="column" style="width:25%">
                    <?php $elementName = 'sb_st_experts'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
                <div class="column" style="width:25%">
                    <?php $elementName = 'sb_st_support'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>
			<?php $statusLabels = []; ?>

            <div class="row" id="financialsRow canvas-row">
                <div class="column" style="width:50%">
                    <?php $elementName = 'sb_budget'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php $elementName = 'sb_time'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

            <div class="row" id="culturechangeRow canvas-row">
                <div class="column" style="width:50%">
                    <?php $elementName = 'sb_culture'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php $elementName = 'sb_change'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php $elementName = 'sb_principles'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
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

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

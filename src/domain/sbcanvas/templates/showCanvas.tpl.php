<?php
/**
 * Strategy Brief - Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'sb';
?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasTop.inc.php'); ?>

    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">

            <div class="row-fluid">
                <div class="column" style="width:100%">
		          <h4 class="widgettitle title-primary center"><i class='fas fa-list-check'></i> <?=$this->__('box.sb.title') ?></h4>
                  <div class="contentInner even" style="padding-top: 10px;"><?=$this->e($_SESSION['currentProjectName']); ?></div>
                </div>
            </div>

            <div class="row-fluid">
                <div class="column" style="width:100%">
				    <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_industry']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_industry']['title']) ?></h4>
                    <?php $elementName = 'sb_industry'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid">
                <div class="column" style="width:100%">
                    <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_description']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_description']['title']) ?></h4>
                    <?php $elementName = 'sb_description'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid" id="stakeholderRow">
                <div class="column" style="width:25%">
                    <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_st_design']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_st_design']['title']) ?></h4>
                    <?php $elementName = 'sb_st_design'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
                <div class="column" style="width:25%">
					  <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_st_decision']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_st_decision']['title']) ?></h4>
                    <?php $elementName = 'sb_st_decision'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
                <div class="column" style="width:25%">
                    <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_st_experts']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_st_experts']['title']) ?></h4>
                    <?php $elementName = 'sb_st_experts'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
                <div class="column" style="width:25%">
                    <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_st_support']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_st_support']['title']) ?></h4>
                    <?php $elementName = 'sb_st_support'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid" id="financialsRow">
                <div class="column" style="width:50%">
                    <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_budget']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_budget']['title']) ?></h4>
                    <?php $elementName = 'sb_budget'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
                <div class="column" style="width:50%">
                    <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_time']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_time']['title']) ?></h4>
                    <?php $elementName = 'sb_time'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid" id="culturechangeRow">
                <div class="column" style="width:50%">
                    <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_culture']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_culture']['title']) ?></h4>
                    <?php $elementName = 'sb_culture'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
                <div class="column" style="width:50%">
                    <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_change']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_change']['title']) ?></h4>
                    <?php $elementName = 'sb_change'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid">
                <div class="column" style="width:100%">
                    <h4 class="widgettitle title-primary center"><i class="<?=$canvasTypes['sb_principles']['icon'] ?>"></i> <?=$this->__($canvasTypes['sb_principles']['title']) ?></h4>
                    <?php $elementName = 'sb_principles'; require(ROOT.'/../src/domain/canvas/templates/element.inc.php'); ?>
                </div>
            </div>

			<div class="row-fluid">
                <div class="column" style="width:100%">
				   <h4 class="widgettitle title-primary center"><i class='fas fa-person-falling'></i> <?=$this->__('box.sb.risks') ?></h4>
                   <div class="contentInner even" style="padding-top: 10px;">
				     <?php echo sprintf($this->__('text.sb.risks_analysis'), BASE_URL); ?>
                   </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php require(ROOT.'/../src/domain/canvas/templates/showCanvasBottom.inc.php'); ?>

<script type="text/javascript">
    jQuery(document).ready(function() {
			
        leantime.<?=$canvasName ?>canvasLayout.setRowHeights();

	});
</script>
<
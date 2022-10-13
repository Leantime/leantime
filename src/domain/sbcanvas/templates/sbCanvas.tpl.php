<?php
/**
 * Strategy Brief - Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'sb';
$canvasTemplate = '';

$allCanvas = $this->get("allCanvas");
$canvasLabels = $this->get("canvasLabels");
$canvasTitle = "";
$statusLabels = $this->get("statusLabels");
$filter = $_GET['filter'] ?? ($_SESSION['filter'] ?? 'all');
$_SESSION['filter'] = $filter;
$filterStatus = match($filter)
  {'valid' => 'success', 'invalid' => 'danger', 'draft' => 'info', 'review' => 'warning', default => 'all' };
?>

<?php require(ROOT.'/../src/library/canvas/tpl.canvasTop.inc.php'); ?>

            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        <?php if(count($this->get('allCanvas')) > 0) {?>
                        <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$this->__("status.".$filter) ?> <?=$this->__("links.view") ?></button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasName ?>Canvas?filter=all" <?php if($filter == 'all') { ?>class="active" <?php } ?>><?=$this->__("status.all") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasName ?>Canvas?filter=draft" <?php if($filter == 'draft') { ?>class="active" <?php } ?>><?=$this->__("status.draft") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasName ?>Canvas?filter=review" <?php if($filter == 'review') { ?>class="active" <?php } ?>><?=$this->__("status.review") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasName ?>Canvas?filter=valid" <?php if($filter == 'valid') { ?>class="active" <?php } ?>><?=$this->__("status.valid") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasName ?>Canvas?filter=invalid" <?php if($filter == 'invalid') { ?>class="active" <?php } ?>><?=$this->__("status.invalid") ?></a></li>
                        </ul>
							<?php } ?>
                    </div>
                </div>
            </div>

        </div>

        <div class="clearfix"></div>
    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">

            <div class="row-fluid">
                <div class="column" style="width:100%">
		          <h4 class="widgettitle title-primary center"><?=$this->__('box.sb.title') ?></h4>
                  <div class="contentInner even" style="padding-top: 10px;"><?=$this->e($_SESSION['currentProjectName']); ?></div>
                </div>
            </div>

            <div class="row-fluid">
                <div class="column" style="width:100%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_industry"]; ?></h4>
                    <?php $elementName = 'sb_industry'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid">
                <div class="column" style="width:100%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_description"]; ?></h4>
                    <?php $elementName = 'sb_description'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid" id="stakeholderRow">
                <div class="column" style="width:25%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_st_design"]; ?></h4>
                    <?php $elementName = 'sb_st_design'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
                <div class="column" style="width:25%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_st_decision"]; ?></h4>
                    <?php $elementName = 'sb_st_decision'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
                <div class="column" style="width:25%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_st_experts"]; ?></h4>
                    <?php $elementName = 'sb_st_experts'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
                <div class="column" style="width:25%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_st_support"]; ?></h4>
                    <?php $elementName = 'sb_st_support'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid" id="financialsRow">
                <div class="column" style="width:50%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_budget"]; ?></h4>
                    <?php $elementName = 'sb_budget'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
                <div class="column" style="width:50%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_time"]; ?></h4>
                    <?php $elementName = 'sb_time'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid" id="culturechangeRow">
                <div class="column" style="width:50%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_culture"]; ?></h4>
                    <?php $elementName = 'sb_culture'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
                <div class="column" style="width:50%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_change"]; ?></h4>
                    <?php $elementName = 'sb_change'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid">
                <div class="column" style="width:100%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sb_principles"]; ?></h4>
                    <?php $elementName = 'sb_principles'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
            </div>

			<div class="row-fluid">
                <div class="column" style="width:100%">
				   <h4 class="widgettitle title-primary center"><?=$this->__('box.sb.risks') ?></h4>
                   <div class="contentInner even" style="padding-top: 10px;">
				     <?php echo sprintf($this->__('text.sb.risks_analysis'), BASE_URL); ?>
                   </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <?php
        if(isset($_SESSION['tourActive']) === true && $_SESSION['tourActive'] == 1) {     ?>
                <p class="align-center"><br />
                <?php echo sprintf($this->__("tour.$canvasName.once_your_done"), BASE_URL); ?>
                </p>
        <?php } ?>

    <?php } else {

        echo "<br /><br /><div class='center'>";

        echo"<div style='width:30%' class='svgContainer'>";
        echo file_get_contents(ROOT."/images/svg/undraw_design_data_khdb.svg");
        echo"</div>";

        echo"<h4>".$this->__("headlines.$canvasName.analysis")."</h4>";
         if($login::userIsAtLeast($roles::$editor)) {

            echo"<br />".$this->__("text.$canvasName.helper_content");
            }
            echo"</div>";

    }
    require(ROOT.'/../src/library/canvas/tpl.modals.inc.php');
    ?>
    </div>
</div>

<?php require(ROOT.'/../src/library/canvas/tpl.canvasBottom.inc.php'); ?>
				   
<?php
/**
 * Business Model Cavans / Lightweight
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'bm';
$canvasTemplate = 'l'; $_SESSION[$canvasName.'template'] = $canvasTemplate;
$canvasTitle = "";

$allCanvas = $this->get("allCanvas");
$canvasLabels = $this->get("canvasLabels");
$statusLabels = $this->get("statusLabels");
$filter = $_GET['filter'] ?? ($_SESSION['filter'] ?? 'all');
$_SESSION['filter'] = $filter;
$filterStatus = match($filter) {'validated_true' => 'success', 'validated_false' => 'danger', 'not_validated' => 'info', default => 'all' };
?>

<?php require(ROOT.'/../src/library/canvas/tpl.canvasTop.inc.php'); ?>

            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        <?php if(count($this->get('allCanvas')) > 0) {?>
                        <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$this->__("label.".$filter) ?> <?=$this->__("links.view") ?></button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasTemplate.$canvasName ?>Canvas?filter=all" <?php if($filter == 'all') { ?>class="active" <?php } ?>><?=$this->__("label.all") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasTemplate.$canvasName ?>Canvas?filter=validated_true" <?php if($filter == 'validated_true') { ?>class="active" <?php } ?>><?=$this->__("label.validated_true") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasTemplate.$canvasName ?>Canvas?filter=validated_false" <?php if($filter == 'validated_false') { ?>class="active" <?php } ?>><?=$this->__("label.validated_false") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasTemplate.$canvasName ?>Canvas?filter=not_validated" <?php if($filter == 'not_validated') { ?>class="active" <?php } ?>><?=$this->__("label.not_validated") ?></a></li>
                        </ul>
							<?php } ?>
                    </div>
                    <?php if(count($this->get('allCanvas')) > 0) {?>

                        <div class="btn-group viewDropDown">
                            <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$this->__("links.$canvasName.l$canvasName".".board") ?> <?=$this->__("links.view") ?></button>
                            <ul class="dropdown-menu">
						      <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/l<?=$canvasName ?>Canvas" class="active"><?=$this->__("links.$canvasName.l$canvasName".".board") ?></a></li>
						      <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/o<?=$canvasName ?>Canvas"><?=$this->__("links.$canvasName.o$canvasName".".board") ?></a></li>
						      <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/d<?=$canvasName ?>Canvas"><?=$this->__("links.$canvasName.d$canvasName".".board") ?></a></li>
                            </ul>
                        </div>

                    <?php } ?>
                </div>
            </div>

        </div>

        <div class="clearfix"></div>
    <?php if(count($this->get('allCanvas')) > 0) { ?>

        <div id="sortableCanvasKanban" class="sortableTicketList disabled">

            <div class="row-fluid" id="firstRow">

                <div class="column" style="width:33.33%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["bm_customers"]; ?></h4>
					<?php $elementName = 'bm_customers'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>

                <div class="column" style="width:33.33%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["bm_offerings"]; ?></h4>
					<?php $elementName = 'bm_offerings'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>

                <div class="column" style="width:33.33%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["bm_capabilities"]; ?></h4>
					<?php $elementName = 'bm_capabilities'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>

            </div>

            <div class="row-fluid" id="secondRow">
                <div class="column" style="width:100.0%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["bm_financials"]; ?></h4>
					<?php $elementName = 'bm_financials'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
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
        <br /><small class="align-center"><?=$this->__('text.bm.lbm.canvas_is_adapted_message') ?></small>
    </div>
</div>

<?php require(ROOT.'/../src/library/canvas/tpl.canvasBottom.inc.php'); ?>

<script type="text/javascript">

    jQuery(document).ready(function() {

        leantime.<?=$canvasName ?>canvasController.setLbmCanvasHeights();
    });

</script>

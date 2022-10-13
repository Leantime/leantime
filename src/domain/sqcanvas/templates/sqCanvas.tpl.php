<?php
/**
 * Porter's Five Strategy Questions - Template
 */
defined('RESTRICTED') or die('Restricted access');

$canvasName = 'sq';
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
                        <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$this->__("label.".$filter) ?> <?=$this->__("links.view") ?></button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasName ?>Canvas?filter=all" <?php if($filter == 'all') { ?>class="active" <?php } ?>><?=$this->__("label.all") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasName ?>Canvas?filter=draft" <?php if($filter == 'draft') { ?>class="active" <?php } ?>><?=$this->__("label.draft") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasName ?>Canvas?filter=review" <?php if($filter == 'review') { ?>class="active" <?php } ?>><?=$this->__("label.review") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasName ?>Canvas?filter=valid" <?php if($filter == 'valid') { ?>class="active" <?php } ?>><?=$this->__("label.valid") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/<?=$canvasName ?>Canvas?filter=invalid" <?php if($filter == 'invalid') { ?>class="active" <?php } ?>><?=$this->__("label.invalid") ?></a></li>
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
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sq_a"]; ?></h4>
                    <?php $elementName = 'sq_a'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
            </div>

            <div class="row-fluid">
                <div class="column" style="width:100%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sq_b"]; ?></h4>
                    <?php $elementName = 'sq_b'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
            </div>
				
            <div class="row-fluid">
                <div class="column" style="width:100%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sq_c"]; ?></h4>
                    <?php $elementName = 'sq_c'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
            </div>
				
            <div class="row-fluid">
                <div class="column" style="width:100%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sq_d"]; ?></h4>
                    <?php $elementName = 'sq_d'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
                </div>
            </div>
				
            <div class="row-fluid">
                <div class="column" style="width:100%">
                    <h4 class="widgettitle title-primary center"><?php echo $canvasLabels["sq_e"]; ?></h4>
                    <?php $elementName = 'sq_e'; require(ROOT.'/../src/library/canvas/tpl.element.inc.php'); ?>
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

<script type="text/javascript">

    jQuery(document).ready(function() {

        leantime.<?=$canvasName ?>canvasController.setRowHeights();
    });

</script>

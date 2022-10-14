<?php
/**
 * Top part of the main canvas page
 *
 * Required variables:
 * - $canvasName       Name of current canvas
 * - $canvasTemplate   Template name
 */
$allCanvas = $this->get("allCanvas");
$canvasTypes = $this->get("canvasTypes");
$canvasTitle = "";
$statusLabelsAll = $this->get("statusLabelsAll");
$statusLabels = $this->get("statusLabels");
$dataLabels = $this->get("dataLabels");
$relationLabels = $this->get("relationLabels");

$filter = $_GET['filter'] ?? ($_SESSION['filter'] ?? 'all');
$_SESSION['filter'] = $filter;
?>
 <div class="pageheader">
    <div class="pageicon"><span class='fa <?=$this->__("icon.$canvasName.board") ?>'></span></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
        <h1><?=$this->__("headline.$canvasName.board") ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-md-4"></div>

            <div class="col-md-4 center">
                <span class="currentSprint">
                    <form action="" method="post">
                        <input type="hidden" name="filter" value="<?=$filter ?>">
                        <?php if(count($this->get('allCanvas')) > 0) {?>
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_board") ?>" name="searchCanvas" class="mainSprintSelector" onchange="form.submit()" style="max-width:100%; text-align: center;">
                                <?php
                                $lastClient = "";
                                $i=0;
                                foreach($this->get('allCanvas') as $canvasRow){ 

                                    echo"<option value='".$canvasRow["id"]."'";
                                    if($this->get('currentCanvas') == $canvasRow["id"]) {
                                        $canvasTitle = $canvasRow["title"];
                                        echo" selected='selected' ";
                                    }
                                    echo">".$canvasRow["title"]."</option>";
                                }
                                ?>
                            </select><br />
                            <?php if($login::userIsAtLeast($roles::$editor)) { ?>
                                <small><a href="javascript:void(0)" class="addCanvasLink"><?=$this->__("links.icon.create") ?></a></small> |
                                <small><a href="javascript:void(0)" class="editCanvasLink "><?=$this->__("links.icon.edit") ?></a></small> |
                                <small><a href="javascript:void(0)" class="cloneCanvasLink "><?=$this->__("links.icon.clone") ?></a></small> |
                            <?php } ?>
                            <small><a href="<?=BASE_URL ?>/pdf.php?module=<?=$canvasName ?>canvas&amp;id=<?php echo $this->get('currentCanvas'); ?>&filter=<?=$filter ?>"><?=$this->__("links.icon.print") ?></a></small>
                            <?php if($login::userIsAtLeast($roles::$editor)) { ?>
                                | <small><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvas/<?php echo $this->get('currentCanvas');?>" class="delete"><?php echo $this->__("links.icon.delete") ?></a></small>
                            <?php } ?>
                        <?php } ?>
                    </form>

                </span>
            </div>

            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        <?php if(count($this->get('allCanvas')) > 0 && !empty($statusLabels)) {?>
		                    <?php if($filter == 'all') { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="<?=$this->__($statusLabelsAll['icon']) ?>"></i> <?=$this->__($statusLabelsAll['title']) ?> <?=$this->__("links.view") ?></button>
							<?php } else { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="<?=$this->__($statusLabels[$filter]['icon']) ?>"></i> <?=$this->__($statusLabels[$filter]['title']) ?> <?=$this->__("links.view") ?></button>
							<?php } ?>
                            <ul class="dropdown-menu">
                                <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter=all" <?php if($filter == 'all') { ?>class="active" <?php } ?>><i class="<?=$this->__($statusLabelsAll['icon']) ?>"></i> <?=$this->__($statusLabelsAll['title']) ?></a></li>
		    					<?php foreach($statusLabels as $key => $data) { ?>
			    	                 <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter=<?=$key ?>" <?php if($filter == $key) { ?>class="active" <?php } ?>><i class="<?=$this->__($data['icon']) ?>"></i> <?=$this->__($data['title']) ?></a></li>
				    			<?php } ?>
                            </ul>
						<?php } ?>
                    </div>
                </div>
            </div>

        </div>

        <div class="clearfix"></div>

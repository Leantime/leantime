<?php
/**
 * Top part of the main canvas page
 *
 * Required variables:
 * - $canvasName       Name of current canvas
 */
$canvasTitle = '';
$allCanvas = $this->get('allCanvas');
$canvasIcon = $this->get('canvasIcon');
$canvasTypes = $this->get('canvasTypes');
$statusLabels = $statusLabels ?? $this->get('statusLabels');
$relatesLabels = $relatesLabels ?? $this->get('relatesLabels');
$dataLabels = $this->get('dataLabels');
$canvasItems = $this->get('canvasItems');

$filter['status'] = $_GET['filter_status'] ?? ($_SESSION['filter_status'] ?? 'all'); $_SESSION['filter_status'] = $filter['status'];
$filter['relates'] = $_GET['filter_relates'] ?? ($_SESSION['filter_relates'] ?? 'all'); $_SESSION['filter_relates'] = $filter['relates'];

?>
 <div class="pageheader">
    <div class="pageicon"><span class='fa <?=$canvasIcon ?>'></span></div>
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
                        <input type="hidden" name="filter_status" value="<?=$filter['status'] ?>">
                        <input type="hidden" name="filter_relates" value="<?=$filter['relates'] ?>">
                        <?php if(count($allCanvas) > 0) {?>
                            <select data-placeholder="<?=$this->__("input.placeholders.filter_by_board") ?>" name="searchCanvas" class="mainSprintSelector" onchange="form.submit()" style="max-width:100%; text-align: center;">
                                <?php
                                $lastClient = "";
                                $i=0;
                                foreach($this->get('allCanvas') as $canvasRow){ 

                                    echo"<option value='".$canvasRow["id"]."'";
                                    if($this->get('currentCanvas') == $canvasRow["id"]) {
                                        $canvasTitle= $canvasRow["title"];
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
                            <small><a href="<?=BASE_URL ?>/pdf.php?module=<?=$canvasName ?>canvas&amp;id=<?php echo $this->get('currentCanvas'); ?>&filter_status=<?=$filter['status'] ?>&filter_relates=<?=$filter['relates'] ?>"><?=$this->__("links.icon.print") ?></a></small>
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
                        <?php if(count($allCanvas) > 0 && !empty($statusLabels)) {?>
		                    <?php if($filter['status'] == 'all') { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw fa-globe"></i> <?=$this->__("status.all") ?> <?=$this->__("links.view") ?></button>
							<?php } else { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw <?=$this->__($statusLabels[$filter['status']]['icon']) ?>"></i> <?=$statusLabels[$filter['status']]['title'] ?> <?=$this->__("links.view") ?></button>
							<?php } ?>
                            <ul class="dropdown-menu">
                                <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter_status=all" <?php if($filter['status'] == 'all') { ?>class="active" <?php } ?>><i class="fas fa-globe"></i> <?=$this->__("status.all") ?></a></li>
		    					<?php foreach($statusLabels as $key => $data) { ?>
			    	                 <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter_status=<?=$key ?>" <?php if($filter['status'] == $key) { ?>class="active" <?php } ?>><i class="fas fa-fw <?=$data['icon'] ?>"></i> <?=$data['title'] ?></a></li>
				    			<?php } ?>
                            </ul>
						<?php } ?>
                    </div>
					
                    <div class="btn-group viewDropDown">
                        <?php if(count($allCanvas) > 0 && !empty($relatesLabels)) {?>
		                    <?php if($filter['relates'] == 'all') { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw fa-globe"></i> <?=$this->__("relates.all") ?> <?=$this->__("links.view") ?></button>
							<?php } else { ?>
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw <?=$this->__($relatesLabels[$filter['relates']]['icon']) ?>"></i> <?=$relatesLabels[$filter['relates']]['title'] ?> <?=$this->__("links.view") ?></button>
							<?php } ?>
                            <ul class="dropdown-menu">
                                <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter_relates=all" <?php if($filter['relates'] == 'all') { ?>class="active" <?php } ?>><i class="fas fa-globe"></i> <?=$this->__("relates.all") ?></a></li>
		    					<?php foreach($relatesLabels as $key => $data) { ?>
			    	                 <li><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas?filter_relates=<?=$key ?>" <?php if($filter['relates'] == $key) { ?>class="active" <?php } ?>><i class="fas fa-fw <?=$data['icon'] ?>"></i> <?=$data['title'] ?></a></li>
				    			<?php } ?>
                            </ul>
						<?php } ?>
                    </div>
					
                </div>
            </div>

        </div>

        <div class="clearfix"></div>

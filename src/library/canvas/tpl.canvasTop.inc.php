<?php
/**
 * Top part of the main canvas page
 *
 * Required variables:
 * - $canvasName       Name of current canvas
 * - $canvasTemplate   Template name
 */
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
                            <small><a href="<?=BASE_URL ?>/pdf.php?module=<?=$canvasName ?>canvas&amp;template=<?=$canvasTemplate; ?>&amp;id=<?php echo $this->get('currentCanvas'); ?>&filter=<?=$filter ?>"><?=$this->__("links.icon.print") ?></a></small>
                            <?php if($login::userIsAtLeast($roles::$editor)) { ?>
                                | <small><a href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvas/<?php echo $this->get('currentCanvas');?>" class="delete"><?php echo $this->__("links.icon.delete") ?></a></small>
                            <?php } ?>
                        <?php } ?>
                    </form>

                </span>
            </div>

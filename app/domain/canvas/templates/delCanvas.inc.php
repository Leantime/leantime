<?php

/**
 * delCanvas.inc template - Generic template for deleting canvas
 *
 * Required variables:
 * - $canvasName     Name of current canvas
 */

defined('RESTRICTED') or die('Restricted access');

$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
?>

<div class="pageheader">
    <div class="pageicon"><span class="fas fa-trash-can"></span></div>
    <div class="pagetitle">
        <h5><?php echo $_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName']; ?></h5>
        <h1><?=$this->__("headline.delete_board") ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">
        <h4 class="widget widgettitle"><?=$this->__("subtitles.delete") ?></h4>
        <div class="widgetcontent">
            <form method="post" action="<?=BASE_URL ?>/<?=$canvasName ?>canvas/delCanvas/<?=$id ?>">
                <p><?php echo $this->__('text.confirm_board_deletion'); ?></p><br />
                <input type="submit" value="<?php echo $this->__('buttons.yes_delete'); ?>" name="del" class="button" />
                <a class="btn btn-secondary"
                   href="<?=BASE_URL ?>/<?=$canvasName ?>canvas/showCanvas"><?php echo $this->__('buttons.back'); ?></a>
            </form>
        </div>

    </div>
</div>

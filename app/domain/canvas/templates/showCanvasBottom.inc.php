<?php

/**
 * showCanvasBottom.inc template - Bottom part of the main canvas page
 *
 * Required variables:
 * - $canvasName       Name of current canvas
 */

?>
    <?php if (count($this->get('allCanvas')) > 0) {
        if (isset($_SESSION['tourActive']) === true && $_SESSION['tourActive'] == 1) {     ?>
        <p class="align-center"><br />
            <?php echo sprintf($this->__("tour.$canvasName.once_your_done"), BASE_URL); ?>
        </p>
        <?php } ?>

    <?php } else {
        echo "<br /><br /><div class='center'>";

        echo "<div class='svgContainer'>";
        echo file_get_contents(ROOT . "/images/svg/undraw_design_data_khdb.svg");
        echo "</div>";

        echo"<h3>" . $this->__("headlines.$canvasName.analysis") . "</h3>";
        echo "<br />" . $this->__("text.$canvasName.helper_content");

        if ($login::userIsAtLeast($roles::$editor)) {
            echo "<br /><br /><a href='javascript:void(0)' class='addCanvasLink btn btn-primary'>
                 " . $this->__("links.icon.create_new_board") . "</a>.";
        }
        echo"</div>";
    }
    if (!empty($disclaimer) && count($this->get('allCanvas')) > 0) { ?>
        <small class="align-center"><?=$disclaimer ?></small>
        <?php
    }
    require($this->getTemplatePath('canvas', '/modals.inc.php'));
    ?>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {

        if(jQuery('#searchCanvas').length > 0) {
            new SlimSelect({ select: '#searchCanvas' });
        }

        leantime.<?=$canvasName ?>CanvasController.setRowHeights();
        leantime.canvasController.setCanvasName('<?=$canvasName ?>');
        leantime.canvasController.initFilterBar();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.canvasController.initCanvasLinks();
            leantime.canvasController.initUserDropdown();
            leantime.canvasController.initStatusDropdown();
            leantime.canvasController.initRelatesDropdown();
        <?php } else { ?>
            leantime.generalController.makeInputReadonly(".maincontentinner");

        <?php } ?>


        <?php if (isset($_GET['showModal'])) {
            if ($_GET['showModal'] == "") {
                $modalUrl = "&type=" . array_key_first($canvasTypes);
            } else {
                $modalUrl = "/" . (int)$_GET['showModal'];
            }
            ?>
        leantime.canvasController.openModalManually("<?=BASE_URL?>/<?=$canvasName ?>canvas/editCanvasItem<?=$modalUrl ?>");
        window.history.pushState({},document.title, '<?=BASE_URL?>/<?=$canvasName ?>canvas/showCanvas/');

        <?php } ?>

    });

</script>

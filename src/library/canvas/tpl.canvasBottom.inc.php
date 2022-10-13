<?php
/**
 * Bottom part of the main canvas page
 *
 * Required variables:
 * - $canvasName       Name of current canvas
 * - $canvasTemplate   Template name
 */
?>

<script type="text/javascript">

    jQuery(document).ready(function() {

        leantime.<?=$canvasName ?>canvasController.initFilterBar();
        leantime.<?=$canvasName ?>canvasController.setRowHeights();
        leantime.generalController.initSimpleEditor();

        <?php if($login::userIsAtLeast($roles::$editor)) { ?>

            leantime.<?=$canvasName ?>canvasController.initCanvasLinks();
            leantime.<?=$canvasName ?>canvasController.initUserDropdown();
            leantime.<?=$canvasName ?>canvasController.initStatusDropdown();

        <?php }else{ ?>

            leantime.generalController.makeInputReadonly(".maincontentinner");

        <?php } ?>

        <?php if(isset($_SESSION['userdata']['settings']["modals"]["<?=$canvasName ?>Canvas"]) === false || 
            $_SESSION['userdata']['settings']["modals"]["<?=$canvasName ?>Canvas"] == 0) {     ?>
        leantime.helperController.showHelperModal("<?=$canvasName ?>Canvas");
        <?php
        //Only show once per session
        $_SESSION['userdata']['settings']["modals"]["<?=$canvasName ?>Canvas"] = 1;
        } ?>


        <?php if(isset($_GET['showModal'])) {

        if($_GET['showModal'] == "") {
            $modalUrl = "&type=";
        }else{
            $modalUrl = "/".(int)$_GET['showModal'];
        }
        ?>

        leantime.<?=$canvasName ?>canvasController.openModalManually("<?=BASE_URL?>/<?=$canvasName ?>canvas/editCanvasItem<?php echo $modalUrl; ?>");
        window.history.pushState({},document.title, '<?=BASE_URL?>/<?=$canvasName ?>canvas/<?=$canvasTemplate.$canvasName ?>Canvas/');

        <?php } ?>

    });

</script>

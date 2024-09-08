
    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
    <?php } else {
        echo "<br /><br /><div class='center'>";

        echo "<div class='svgContainer'>";
        echo file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg");
        echo "</div>";

        echo"<h3>" . $tpl->__("headlines.$canvasName.analysis") . "</h3>";
        echo "<br />" . $tpl->__("text.$canvasName.helper_content");

        if ($login::userIsAtLeast($roles::$editor)) {
            echo "<br /><br /><a href='javascript:void(0)' class='addCanvasLink btn btn-primary'>
                 " . $tpl->__("links.icon.create_new_board") . "</a>.";
        }
        echo"</div>";
    }
    if (!empty($disclaimer) && count($tpl->get('allCanvas')) > 0) { ?>
        <small class="align-center"><?=$disclaimer ?></small>
        <?php
    }

    echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'modals'), $__data)->render();

    ?>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {

        if(jQuery('#searchCanvas').length > 0) {
            new SlimSelect({ select: '#searchCanvas' });
        }

        <?php if (isset($_GET['closeModal'])) { ?>
            jQuery.nmTop().close();
        <?php } ?>

        leantime.{{ $canvasName }}CanvasController.setRowHeights();
        leantime.canvasController.setCanvasName('{{ $canvasName }}');
        leantime.canvasController.initFilterBar();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.canvasController.initCanvasLinks();
            leantime.canvasController.initUserDropdown('{{ $canvasName }}');
            leantime.canvasController.initStatusDropdown('{{ $canvasName }}');
            leantime.canvasController.initRelatesDropdown('{{ $canvasName }}');
        <?php } else { ?>
            leantime.authController.makeInputReadonly(".maincontentinner");

        <?php } ?>

    });

</script>

    @endsection

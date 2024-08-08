@if (count($tpl->get('allCanvas')) > 0)
@else
<br /><br />
<div class='center'>
  <div class='svgContainer'>
    {!! file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg") !!}
  </div>

  <h3>{{ __("headlines.$canvasName.analysis") }}</h3>
  <br />{{ __("text.$canvasName.helper_content") }}

  @if ($login::userIsAtLeast($roles::$editor))
  <br /><br />
  <a href='javascript:void(0)' class='addCanvasLink btn btn-primary'>
    {{ __("links.icon.create_new_board") }}
  </a>
  @endif
</div>
@endif

@if (!empty($disclaimer) && count($tpl->get('allCanvas')) > 0)
<small class="align-center">{{ $disclaimer }}</small>
@endif

{!! $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'modals'), $__data)->render() !!}
</div>
</div>


<script type="text/javascript">
  jQuery(document).ready(function() {

    if (jQuery('#searchCanvas').length > 0) {
      new SlimSelect({
        select: '#searchCanvas'
      });
    }

    leantime.<?= $canvasName ?>CanvasController.setRowHeights();
    leantime.canvasController.setCanvasName('<?= $canvasName ?>');
    leantime.canvasController.initFilterBar();

    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
      leantime.canvasController.initCanvasLinks();
      leantime.canvasController.initUserDropdown();
      leantime.canvasController.initStatusDropdown();
      leantime.canvasController.initRelatesDropdown();
    <?php } else { ?>
      leantime.authController.makeInputReadonly(".maincontentinner");

    <?php } ?>


    <?php if (isset($_GET['showModal'])) {
      if ($_GET['showModal'] == "") {
        $modalUrl = "&type=" . array_key_first($canvasTypes);
      } else {
        $modalUrl = "/" . (int)$_GET['showModal'];
      }
    ?>
      leantime.canvasController.openModalManually("<?= BASE_URL ?>/<?= $canvasName ?>canvas/editCanvasItem<?= $modalUrl ?>");
      window.history.pushState({}, document.title, '<?= BASE_URL ?>/<?= $canvasName ?>canvas/showCanvas/');

    <?php } ?>

  });
</script>
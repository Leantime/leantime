@if (count($allCanvas) > 0)
@else
    <br /><br />
    <div class='center'>
        <div class='svgContainer'>
            {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
        </div>

        <h3>{{ __("headlines.goal.analysis") }}</h3>
        <br />{{ __("text.goal.helper_content") }}

        @if ($login::userIsAtLeast($roles::$editor))
            <br /><br />
            <a href='javascript:void(0)' class='addCanvasLink btn btn-primary'>
                {{ __('links.icon.create_new_board') }}
            </a>
        @endif
    </div>
@endif

@if (!empty($disclaimer) && count($allCanvas) > 0)
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

      leantime.goalCanvasController.setRowHeights();
      leantime.canvasController.setCanvasName('goal');
      leantime.canvasController.initFilterBar();

      @if ($login::userIsAtLeast($roles::$editor))
      leantime.canvasController.initCanvasLinks();
      leantime.canvasController.initUserDropdown();
      leantime.canvasController.initStatusDropdown();
      leantime.canvasController.initRelatesDropdown();
      @else
      leantime.authController.makeInputReadonly(".maincontentinner");
      @endif

      @if (isset($_GET['showModal']))
          @php
              if ($_GET['showModal'] == "") {
                  $modalUrl = "&type=" . array_key_first($canvasTypes);
              } else {
                  $modalUrl = "/" . (int)$_GET['showModal'];
              }
          @endphp
      leantime.canvasController.openModalManually(
          "{{ BASE_URL }}/goalcanvas/editCanvasItem{{ $modalUrl }}");
      window.history.pushState({}, document.title, '{{ BASE_URL }}/goalcanvas/showCanvas/');
      @endif

  });
</script>

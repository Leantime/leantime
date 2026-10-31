@if (count($allCanvas) > 0)
@else
    <br /><br />
    <div class='center'>
        <div class='svgContainer'>
            {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
        </div>

        <h3>{{ __("headlines.goal.analysis") }}</h3>
        <br />{{ __("text.goal.helper_content") }}

        @can('goals.create')
            <br /><br />
            <a href='javascript:void(0)' class='addCanvasLink btn btn-primary'>
                {{ __('links.icon.create_new_board') }}
            </a>
        @endcan
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

      leantime.goalCanvasController.setRowHeights();
      leantime.canvasController.setCanvasName('goal');
      leantime.canvasController.initFilterBar();

      @canany(['goals.create', 'goals.edit'])
      leantime.canvasController.initCanvasLinks();
      @endcanany
      @cannot('goals.edit')
      leantime.authController.makeInputReadonly(".maincontentinner");
      @endcannot

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

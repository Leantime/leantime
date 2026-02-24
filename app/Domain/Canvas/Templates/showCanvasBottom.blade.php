@php
    $allCanvas = $tpl->get('allCanvas');
    $canvasTypes = $tpl->get('canvasTypes');
    $disclaimer = $tpl->get('disclaimer');
@endphp

    @if (count($allCanvas) > 0)
    @else
        <br /><br />
        <div class="center">
            <div class="svgContainer">
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
            </div>
            <h3>{{ $tpl->__("headlines.$canvasName.analysis") }}</h3>
            <br />{{ $tpl->__("text.$canvasName.helper_content") }}
            @if ($login::userIsAtLeast($roles::$editor))
                <br /><br /><x-globals::forms.button link="javascript:void(0)" type="primary" class="addCanvasLink">{!! $tpl->__('links.icon.create_new_board') !!}</x-globals::forms.button>.
            @endif
        </div>
    @endif

    @if (! empty($disclaimer) && count($allCanvas) > 0)
        <small class="center">{{ $disclaimer }}</small>
    @endif

    @include('canvas::modals', ['canvasName' => $canvasName])

    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {

        if(jQuery('#searchCanvas').length > 0) {
            new SlimSelect({ select: '#searchCanvas' });
        }

        @if (isset($_GET['closeModal']))
            jQuery.nmTop().close();
        @endif

        leantime.{{ $canvasName }}CanvasController.setRowHeights();
        leantime.canvasController.setCanvasName('{{ $canvasName }}');
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
                if ($_GET['showModal'] == '') {
                    $modalUrl = '&type=' . array_key_first($canvasTypes);
                } else {
                    $modalUrl = '/' . (int) $_GET['showModal'];
                }
            @endphp
            leantime.canvasController.openModalManually("{{ BASE_URL }}/{{ $canvasName }}canvas/editCanvasItem{{ $modalUrl }}");
            window.history.pushState({},document.title, '{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas/');
        @endif

    });
</script>

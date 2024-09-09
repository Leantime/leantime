

@if (count($tpl->get('allCanvas')) > 0)
    {{-- Do something when allCanvas has items --}}
@else
    <br /><br />
    <div class="center">
        <div class="svgContainer">
            {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
        </div>

        <h3>{{ __("headlines.$canvasName.analysis") }}</h3>
        <br />{{ __("text.$canvasName.helper_content") }}

        @if ($login::userIsAtLeast($roles::$editor))
            <br /><br />
            <a href="javascript:void(0)" class="addCanvasLink btn btn-primary">
                {{ __("links.icon.create_new_board") }}
            </a>.
        @endif
    </div>
@endif

@if (!empty($disclaimer) && count($tpl->get('allCanvas')) > 0)
    <small class="align-center">{{ $disclaimer }}</small>
@endif

@include('canvas::modals', $__data)



@section('scripts')
<script type="text/javascript">
    jQuery(document).ready(function() {
        if (jQuery('#searchCanvas').length > 0) {
            new SlimSelect({
                select: '#searchCanvas'
            });
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
                $modalUrl = $_GET['showModal'] == "" ? "&type=" . array_key_first($canvasTypes) : "/" . (int)$_GET['showModal'];
            @endphp
            leantime.canvasController.openModalManually(
                "{{ BASE_URL }}/{{ $canvasName }}canvas/editCanvasItem{{ $modalUrl }}");
            window.history.pushState({}, document.title, '{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas/');
        @endif
    });
</script>
@endsection

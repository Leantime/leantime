@if(count($allCanvas) > 0)
@else
    <br /><br />
    <div class='center'>
        <div class='svgContainer'>
            {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
        </div>

        <h3>{!! __("headlines.$canvasSlug.analysis") !!}</h3>
        <br />{!! __("text.$canvasSlug.helper_content") !!}

        @if($login::userIsAtLeast($roles::$editor))
            <br /><br />
            <a href='javascript:void(0)' class='addCanvasLink btn btn-primary'>
                {!! __('links.icon.create_new_board') !!}</a>.
        @endif
    </div>
@endif

@if(! empty($disclaimer) && count($allCanvas) > 0)
    <small class="align-center">{{ $disclaimer }}</small>
@endif

@include('blueprints::modals')

    </div>
</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function() {

        if(jQuery('#searchCanvas').length > 0) {
            new SlimSelect({ select: '#searchCanvas' });
        }

        @if(isset($_GET['closeModal']))
            jQuery.nmTop().close();
        @endif

        leantime.blueprintsController.setRowHeights();
        leantime.blueprintsController.setCanvasName('{{ $canvasSlug }}');
        leantime.blueprintsController.initFilterBar();

        @if($login::userIsAtLeast($roles::$editor))
            leantime.blueprintsController.initCanvasLinks();
            leantime.blueprintsController.initUserDropdown();
            leantime.blueprintsController.initStatusDropdown();
            leantime.blueprintsController.initRelatesDropdown();
        @else
            leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

        @if(isset($_GET['showModal']))
            @php
                if ($_GET['showModal'] == '') {
                    $modalUrl = '&type=' . array_key_first($canvasTypes);
                } else {
                    $modalUrl = '/' . (int) $_GET['showModal'];
                }
            @endphp
            leantime.blueprintsController.openModalManually("{{ BASE_URL }}/blueprints/{{ $canvasSlug }}/editCanvasItem{{ $modalUrl }}");
            window.history.pushState({},document.title, '{{ BASE_URL }}/blueprints/{{ $canvasSlug }}/showCanvas/');
        @endif

    });

</script>
@endpush @endonce

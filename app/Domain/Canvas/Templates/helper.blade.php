<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:300px' class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
            </div>
            <br />
            <h1>{!! __("headlines.$canvasName.welcome_to_board") !!}</h1><br />
            {!! __("text.$canvasName.helper_content") !!}
            <br /><br />
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <p></p>
            <x-global::forms.button tag="a" link="javascript:void(0);" onclick="jQuery.nmTop().close()" contentRole="secondary">{!! __('links.close') !!}</x-global::forms.button><br />
        </div>
    </div>

</div>

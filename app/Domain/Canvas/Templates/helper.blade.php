<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style="width:300px" class="svgContainer">
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
            </div>
            <br />
            <h1>{{ $tpl->__("headlines.$canvasName.welcome_to_board") }}</h1><br />
            {{ $tpl->__("text.$canvasName.helper_content") }}
            <br /><br />
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <p></p>
            <a href="javascript:void(0);" onclick="jQuery.nmTop().close()">{{ $tpl->__('links.close') }}</a><br />
        </div>
    </div>

</div>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
            </div>
            <h1>{!! __('headlines.welcome_to_research_board') !!}</h1><br />
            <p>{!! __('text.full_lean_canvas_helper_content') !!}</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <x-global::forms.button tag="a" link="javascript:void(0);"  onclick="jQuery.nmTop().close()" contentRole="secondary">{!! __('links.close') !!}</x-global::forms.button><br />

        </div>
    </div>


</div>

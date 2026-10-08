<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
            </div>
            <h1>{{ __('headlines.define_your_projects_with_ease') }}</h1><br />
            <p>{!! __('text.blueprints_intro') !!}</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">

            <x-global::forms.button tag="a" link="{{ BASE_URL }}/valuecanvas/showCanvas" contentRole="primary">{{ __('buttons.create_project_value_canvas') }}</x-global::forms.button><br />

        </div>
    </div>


</div>

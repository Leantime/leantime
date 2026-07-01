<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_Organizing_projects_0p9a.svg') !!}
            </div><br />
            <h3 class="primaryColor"></h3><br />
            {!! __('text.show_projects_helper_content') !!}
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">

            <x-global::forms.button tag="a" link="javascript:void(0);"  onclick="jQuery.nmTop().close()" contentRole="tertiary">{!! __('links.close') !!}</x-global::forms.button><br />
        </div>
    </div>


</div>

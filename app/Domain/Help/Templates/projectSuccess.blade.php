<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_events_2p66.svg') !!}
            </div>
            <h3 class="primaryColor">{!! __('headlines.congrats_on_your_project') !!}</h3><br />
            {!! __('notifications.project_created_successfully') !!}
        </div>
    </div>


    <div class="row">
        <div class="col-md-12 align-center">
            <x-global::forms.button tag="a" link="javascript:void(0);"  onclick="jQuery.nmTop().close()" contentRole="secondary">{!! __('links.close') !!}</x-global::forms.button><br />
        </div>
    </div>


</div>

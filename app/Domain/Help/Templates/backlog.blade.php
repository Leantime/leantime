<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_schedule_pnbk.svg') !!}
            </div><br />
            <h3 class="primaryColor">{!! __('headlines.welcome_to_backlog') !!}</h3><br />
            <p>{!! __('text.backlog_helper_content') !!}</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <x-global::forms.button tag="a" link="javascript:void(0);"  onclick="jQuery.nmTop().close()" contentRole="secondary">{!! __('links.close') !!}</x-global::forms.button><br />
        </div>
    </div>


</div>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_complete_task_u2c3.svg') !!}
            </div><br />
            <h3 class="primaryColor">{!! __('headlines.welcome_to_clients_products') !!}</h3><br />
            {!! __('text.show_clients_helper_content') !!}
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <x-global::forms.button tag="a" link="javascript:void(0);"  onclick="jQuery.nmTop().close()" contentRole="tertiary">{!! __('links.close') !!}</x-global::forms.button><br />
        </div>
    </div>


</div>

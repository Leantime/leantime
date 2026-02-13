<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <x-global::undrawSvg
                image="undraw_complete_task_u2c3.svg"
                maxWidth="50%"
                headlineSize="var(--font-size-xxxl)"
                maxheight="auto"
                height="250px"
                headline="{{ __('headlines.welcome_to_clients_products') }}"
            ></x-global::undrawSvg>
            <br />
            {!! __('text.show_clients_helper_content') !!}
            <br /><br />
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />
        </div>
    </div>

</div>

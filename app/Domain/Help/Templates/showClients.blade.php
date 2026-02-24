<div class="center padding-lg">

    <x-globals::undrawSvg
        image="undraw_complete_task_u2c3.svg"
        maxWidth="50%"
        headlineSize="var(--font-size-xxxl)"
        maxheight="auto"
        height="250px"
        headline="{{ __('headlines.welcome_to_clients_products') }}"
    ></x-globals::undrawSvg>
    <br />
    {!! __('text.show_clients_helper_content') !!}
    <br /><br />

    <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />

</div>

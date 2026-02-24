<div class="center padding-lg">

    <x-global::undrawSvg
        image="undraw_schedule_pnbk.svg"
        maxWidth="50%"
        headlineSize="var(--font-size-xxxl)"
        maxheight="auto"
        height="250px"
        headline="{{ __('headlines.welcome_to_backlog') }}"
    ></x-global::undrawSvg>
    <br />
    <p>{!! __('text.backlog_helper_content') !!}</p>
    <br /><br />

    <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />

</div>

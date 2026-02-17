<div class="center padding-lg">

    <x-global::undrawSvg
        image="undraw_events_2p66.svg"
        maxWidth="50%"
        headlineSize="var(--font-size-xxxl)"
        maxheight="auto"
        height="250px"
        headline="{{ __('headlines.congrats_on_your_project') }}"
    ></x-global::undrawSvg>
    <br />
    {!! __('notifications.project_created_successfully') !!}

    <div class="tw:text-center">
        <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />
    </div>

</div>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
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
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 align-center">
            <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />
        </div>
    </div>

</div>

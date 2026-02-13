<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <x-global::undrawSvg
                image="undraw_time_management_30iu.svg"
                maxWidth="50%"
                headlineSize="var(--font-size-xxxl)"
                maxheight="auto"
                height="250px"
                headline="{{ __('headlines.the_timesheets') }}"
            ></x-global::undrawSvg>
            <br />
            <p>{!! __('text.my_timesheets_helper_content') !!}</p>
            <br /><br />
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />
        </div>
    </div>

</div>

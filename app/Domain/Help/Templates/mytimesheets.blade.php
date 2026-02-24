<div class="center padding-lg">

    <x-globals::undrawSvg
        image="undraw_time_management_30iu.svg"
        maxWidth="50%"
        headlineSize="var(--font-size-xxxl)"
        maxheight="auto"
        height="250px"
        headline="{{ __('headlines.the_timesheets') }}"
    ></x-globals::undrawSvg>
    <br />
    <p>{!! __('text.my_timesheets_helper_content') !!}</p>
    <br /><br />

    <a href="javascript:void(0);" onclick="leantime.helperController.closeModal()">{{ __('links.close') }}</a><br />

</div>

@dispatchEvent('beforeFooterOpen')

{{--<div class="footer">--}}
<span style="color:var(--main-titles-color); padding-left:15px; opacity:0.5;">
    @dispatchEvent('afterFooterOpen')
</span>
{{--    <div class="row">--}}
{{--        <div class="col-md-6">--}}
{{--            © {{ date("Y") }} by <a href="http://leantime.io" target="_blank">Leantime</a>--}}
{{--        </div>--}}
{{--        <div class="col-md-6 align-right">--}}
{{--            <a href="http://leantime.io" target="_blank">--}}
{{--                <img--}}
{{--                    style="height: 18px; opacity:0.5; vertical-align:sub;"--}}
{{--                    src="{!! BASE_URL !!}/dist/images/logo-powered-by-leantime.png"--}}
{{--                />--}}
{{--                <span style="color:var(--primary-font-color); opacity:0.5;">v{{ $version }}</span>--}}
{{--            </a>--}}
{{--        </div>--}}
{{--    </div>--}}


    @dispatchEvent('beforeFooterClose')


{{--</div>--}}

@dispatchEvent('afterFooter')

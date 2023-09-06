@dispatchEvent('beforeFooterOpen')

<div class="footer" style="padding-right: 50px;">

    @dispatchEvent('afterFooterOpen')

    <a href="http://leantime.io" target="_blank">
        <img
            style="height: 18px; opacity:0.5; vertical-align:sub;"
            src="{!! BASE_URL !!}/dist/images/logo-powered-by-leantime.png"
        />
    </a>

    <span style="color:var(--primary-font-color); opacity:0.5;">v{{ $version }}</span>

    @dispatchEvent('beforeFooterClose')

</div>

@dispatchEvent('afterFooter')

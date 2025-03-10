<title>@dispatchFilter('page_title', $sitename)</title>

<meta name="requestId" content="{{ \Illuminate\Support\Str::random(4) }}">
<meta name="description" content="{{ $sitename }}">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="theme-color" content="{{ $primaryColor }}">
<meta name="color-scheme" content="{{ $themeColorMode }}">
<meta name="theme" content="{{ $theme }}">
<meta name="identifier-URL" content="{!! BASE_URL !!}">
<meta name="leantime-version" content="{{ $version }}">
<meta name="view-transition" content="same-origin"/>

@dispatchEvent('afterMetaTags')

<link rel="shortcut icon" href="{!! BASE_URL !!}/dist/images/favicon.png"/>
<link rel="apple-touch-icon" href="{!! BASE_URL !!}/dist/images/apple-touch-icon.png">

<link rel="stylesheet" href="{!! BASE_URL !!}/dist/css/app.{!! $version !!}.min.css"/>
<link rel="stylesheet" href="{!! BASE_URL !!}/dist/css/main.{!! $version !!}.min.css"/>

@dispatchEvent('afterLinkTags')

<script>window.leantime = window.leantime || {};</script>
<script src="{!! BASE_URL !!}/api/i18n?v={!! $version !!}"></script>

<!-- Core Dependencies -->
<script src="@mix('js/manifest.js')"></script>
<script src="@mix('js/vendor.js')"></script>
<script src="@mix('js/app.js')"></script>

@dispatchEvent('afterMainScriptTag')


@dispatchEvent('afterScriptLibTags')

<!-- theme & custom -->
@foreach ($themeScripts as $script)
    <script src="{!! $script !!}"></script>
@endforeach

@foreach ($themeStyles as $style)
    <link rel="stylesheet" @isset($style['id']) id="{{{ $style['id'] }}}" @endisset href="{!! $style['url'] !!}"/>
@endforeach

@dispatchEvent('afterScriptsAndStyles')

<!-- Replace main theme colors -->
<style id="colorSchemeSetter">
    @foreach ($accents as $accent)
        @if($accent !== false)
    :root {
        --accent{{ $loop->iteration }}: {{{ $accent }}};
    }
    @endif
    @endforeach
</style>

<style id="fontStyleSetter">
    :root {
        --primary-font-family: '{{{ $themeFont }}}', 'Helvetica Neue', Helvetica, sans-serif;
    }
</style>


    <style id="backgroundImageSetter">
        @if(!empty($themeBg))
            .rightpanel {
                background-image: url({!! filter_var($themeBg, FILTER_SANITIZE_URL) !!});
                opacity: {{ $themeOpacity }};
                mix-blend-mode: {{ $themeType == 'image' ? 'normal' : 'multiply' }};
                background-size: var(--background-size, cover);
                background-position: center;
                background-attachment: fixed;
            }

            @if($themeType === 'image')
                .rightpanel:before {
                    background:none;
                }
            @endif
        @endif
    </style>


@dispatchEvent('afterThemeColors')


<script>
    window.leantime.currentProject = '{{ session("currentProject") }}';
</script>

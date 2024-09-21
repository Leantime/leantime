<title>@dispatchFilter('page_title', $sitename)</title>

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

<link rel="stylesheet" href="{!! BASE_URL !!}/dist/css/main.{!! $version !!}.min.css"/>
<link rel="stylesheet" href="{!! BASE_URL !!}/dist/css/app.{!! $version !!}.min.css"/>

@dispatchEvent('afterLinkTags')

<script src="{!! BASE_URL !!}/api/i18n?v={!! $version !!}"></script>

<script src="{!! BASE_URL !!}/dist/js/compiled-htmx.{!! $version !!}.min.js"></script>
<script src="{!! BASE_URL !!}/dist/js/compiled-htmx-headSupport.{!! $version !!}.min.js"></script>

<!-- libs -->
<script src="{!! BASE_URL !!}/dist/js/compiled-frameworks.{!! $version !!}.min.js"></script>
<script src="{!! BASE_URL !!}/dist/js/compiled-framework-plugins.{!! $version !!}.min.js"></script>
<script src="{!! BASE_URL !!}/dist/js/compiled-global-component.{!! $version !!}.min.js"></script>
<script src="{!! BASE_URL !!}/dist/js/compiled-calendar-component.{!! $version !!}.min.js"></script>
<script src="{!! BASE_URL !!}/dist/js/compiled-table-component.{!! $version !!}.min.js"></script>
<script src="{!! BASE_URL !!}/dist/js/compiled-editor-component.{!! $version !!}.min.js"></script>
<script src="{!! BASE_URL !!}/dist/js/compiled-gantt-component.{!! $version !!}.min.js"></script>
<script src="{!! BASE_URL !!}/dist/js/compiled-chart-component.{!! $version !!}.min.js"></script>

@dispatchEvent('afterScriptLibTags')

<!-- app -->
<script src="{!! BASE_URL !!}/dist/js/compiled-app.{!! $version !!}.min.js"></script>
@dispatchEvent('afterMainScriptTag')

<!--
//For future file based ref js loading
<script src="{!! BASE_URL !!}/dist/js/{{ ucwords(\Leantime\Core\Controller\Frontcontroller::getModuleName()) }}/Js/{{ \Leantime\Core\Controller\Frontcontroller::getModuleName() }}Controller.js"></script>
-->

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

<style id="fontStyleSetter">:root {
        --primary-font-family: '{{{ $themeFont }}}', 'Helvetica Neue', Helvetica, sans-serif;
    }</style>

@dispatchEvent('afterThemeColors')


<script>
    window.leantime.currentProject = '{{ session("currentProject") }}';
</script>

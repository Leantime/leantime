@php
    $isAjax = request()->headers->has('HX-Request') || request()->ajax();
@endphp

@if(!$isAjax)
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['public/assets/css/entries/main.css', 'public/assets/css/entries/app.css'])
</head>
<body>
@endif

@isset($action, $module)
    @include("$module::$action")
@else
    @yield('content')
@endisset

@if(!$isAjax)
</body>
</html>
@endif

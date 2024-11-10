@isset($action, $module)
    @include("$module::$action")
@else
    @yield('content')
@endisset

@dispatchEvent('beforeBodyClose')

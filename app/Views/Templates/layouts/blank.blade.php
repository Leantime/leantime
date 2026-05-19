@if(isset($action) && isset($module))
    @include("$module::$action")
@else
    @yield('content')
@endif
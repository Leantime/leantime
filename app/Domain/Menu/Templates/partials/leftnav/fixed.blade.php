<li class="fixedMenuPoint {{ $module == $settingsLink['module'] && $action == $settingsLink['action'] ? 'active' : '' }}">
    <a href="@if(isset($settingsLink['url'])) {{ $settingsLink['url']  }} @else {{ BASE_URL }}/{{ $settingsLink['module'] }}/{{ $settingsLink['action'] }} @endif"
       hx-boost="true"
       hx-target=".primaryContent"
       hx-select=".primaryContent"
       hx-swap="outerHTML show:window:top"
       hx-indicator="#page-loading"
    >
        {!! __($settingsLink['label'])  !!}
    </a>
</li>

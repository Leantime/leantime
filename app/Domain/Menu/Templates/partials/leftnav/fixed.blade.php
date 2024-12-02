<li class="fixedMenuPoint {{ $module == $settingsLink['module'] && $action == $settingsLink['action'] ? 'active' : '' }}">
    <a href="@if(isset($settingsLink['url'])) {{ $settingsLink['url']  }} @else {{ BASE_URL }}/{{ $settingsLink['module'] }}/{{ $settingsLink['action'] }} @endif">
        {!! __($settingsLink['label'])  !!}
    </a>
</li>

<li
    @if(
        $module == $menuItem['module']
        && (!isset($menuItem['active']) || in_array($action, $menuItem['active']))
    )
        class='active'
    @endif
>
    <a href="{{ BASE_URL . $menuItem['href'] }}"
       data-tippy-content="{{ strip_tags(__($menuItem['tooltip'])) }}"
       data-tippy-placement="right"
       @if(isset($menuItem['attributes']))
           @foreach($menuItem['attributes'] as $key => $value)
               {{ $key }}="{{ $value }}"
           @endforeach
       @endif
    >
    {!! $menuItem['title'] !!}
    </a>
</li>

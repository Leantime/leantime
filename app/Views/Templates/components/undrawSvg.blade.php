<div class="tw-w-full tw-text-center">

    @if (file_exists($image_path = ROOT . "/dist/images/svg/$image"))
        <div  style='width:30%' class='svgContainer'>
            {!! file_get_contents($image_path) !!}
        </div>
    @endif

    @if (! empty($headline))
        <h3>{{ $headline }}</h3>
    @endif

    {!! $slot ?? '' !!}

</div>

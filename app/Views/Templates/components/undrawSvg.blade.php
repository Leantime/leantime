<div class="tw-w-full tw-text-center">

    @if (file_exists($image_path = ROOT . "/dist/images/svg/$image"))
        <div  style='width:100%; display:flex; max-width: {{ $maxWidth ?? "30%" }}; max-height:{{ $maxHeight ?? "200px" }}; height: {{ $height ?? "auto" }}; overflow:hidden;' class='svgContainer'>
            {!! file_get_contents($image_path) !!}
        </div>
    @endif

    @if (! empty($headline))
        <h3 class="fancyLink">{{ $headline }}</h3>
    @endif

    {!! $slot ?? '' !!}

</div>

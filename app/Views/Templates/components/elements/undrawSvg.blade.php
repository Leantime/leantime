@props([
    "image",
    "headline",
    "maxWidth" => "30%",
    "maxHeight" => "200px",
    "height" => "auto",
    "headlineSize" => "",
    "align" => "center"
])
<div {{ $attributes->merge(['class' => 'tw-w-full tw-text-'.$align.' undrawContainer']) }}>

    @if (file_exists($image_path = ROOT . "/dist/images/svg/$image"))
        <div  style='width:100%; display:flex; max-width: {{ $maxWidth }}; max-height:{{ $maxHeight }}; height: {{ $height }}; overflow:hidden;' class='svgContainer'>
            {!! file_get_contents($image_path) !!}
        </div>
    @endif

    @if (! empty($headline))
        <h3 class="fancyLink" style="{{ $headlineSize !== "" ? "font-size:".$headlineSize : "" }}">{{ $headline }}</h3>
    @endif

    {!! $slot ?? '' !!}

</div>

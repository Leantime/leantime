<div class="tw-w-full tw-text-center">

    <div  style='width:30%' class='svgContainer'>
        {!! file_get_contents(ROOT . "/dist/images/svg/".$image."") !!}
    </div>

    <h3>{{ $headline }}</h3>

    {!! $slot !!}

</div>

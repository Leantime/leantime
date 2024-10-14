<a
    {{ $attributes->class([
        "btn",
        "btn-ghost",
        "btn-sm",
        "btn-circle",
        "float-right",
        ])->merge() }}
    {{ $variant == 'link' ? "onclick=\"leantime.snippets.copyToClipboard('".$href."')\"" : ""}}
>
    @switch($variant)
        @case("link")
            <i class="fa fa-link"></i>
            @break
        @case("delete")
            <span class="text-danger"><i class="fa fa-trash"></i></span>
            @break
    @endswitch
    {{ $slot }}
</a>

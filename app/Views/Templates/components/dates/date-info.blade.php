@props([
    "date" => "",
    "name" => "",
    "type" => ""
])


@if($type == \Leantime\Core\Support\DateTimeInfoEnum::WrittenOnAt)
{{
    sprintf(
    $tpl->__('text.written_on'),
    format($date)->date(),
    format($date)->time()
    )
}}
@elseif($type == \Leantime\Core\Support\DateTimeInfoEnum::UpcatedOnAt)
{{
    sprintf(
        $tpl->__('text.updated_on'),
        format($date)->date(),
        format($date)->time()
        )
}}

@elseif($type == \Leantime\Core\Support\DateTimeInfoEnum::HumanReadable)

@elseif($type == \Leantime\Core\Support\DateTimeInfoEnum::Plain)

{{ format($date)->date() }} {{ format($date)->time() }}

@endif

@props([
    'id' => '',
    'formName' => '',
    'options' => [],
    'selected' => '',
    'noSelection' => ''
])

<span class="dropdown">
    <a
       href="javascript:void(0);"
       role="button"
       id="{{ $id }}-link"
       {{ $attributes->merge(["class" => "dropdown-toggle"]) }}
       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="text">
           @if(empty($selected['value']))
               <span style="opacity:0.4">{{ $noSelection }}</span>
            @else
                {{ $selected['value'] }}
            @endif
        </span>
        <i class="fa fa-chevron-down"
           style="font-size: 10px;
                vertical-align: middle;"></i>
    </a>
    <ul class="dropdown-menu" id="{{ $id }}-options">
        @foreach($options as $key => $option)
            <li><a href="javascript:void(0);" data-id="{{ $key }}">{{ $option }}</a></li>
        @endforeach
    </ul>
</span>
<input type="hidden" name="{{ $formName }}" id="{{ $id }}-formField" value="{{ $selected['key'] }}" />

<script>
    jQuery("#{{ $id }}-options li a").each(function() {

        jQuery(this).click(function() {
            var newText = jQuery(this).text();
            var id = jQuery(this).attr("data-id");
            jQuery('#{{ $id }}-link .text').text(newText);
            jQuery("#{{ $id }}-formField").val(id).trigger("change");
            htmx.trigger("#{{ $id }}-formField", "change");

        });
    });



</script>

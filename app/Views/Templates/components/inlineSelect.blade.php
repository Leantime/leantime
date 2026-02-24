@props([
    'id' => '',
    'formName' => '',
    'options' => [],
    'selected' => '',
    'noSelection' => ''
])

<span class="dropdown">
    <a href="javascript:void(0)"
       id="{{ $id }}-link"
       data-toggle="dropdown"
       {{ $attributes->merge(["class" => "dropdown-toggle"]) }}>
        <span class="text">
           @if(empty($selected['value']))
               <span style="opacity:0.4">{{ $noSelection }}</span>
            @else
                {{ $selected['value'] }}
            @endif
        </span>
        <i class="fa fa-chevron-down" aria-hidden="true"
           style="font-size: 10px;
                vertical-align: middle;"></i>
    </a>
    <ul class="dropdown-menu" id="{{ $id }}-options">
        @foreach($options as $key => $option)
            <li><a href="javascript:void(0);" data-id="{{ $key }}"
                   onclick="document.querySelector('#{{ $id }}-link .text').textContent = this.textContent.trim();
                            var field = document.getElementById('{{ $id }}-formField');
                            field.value = this.getAttribute('data-id');
                            field.dispatchEvent(new Event('change', {bubbles: true}));
                            htmx.trigger(field, 'change');
                            document.activeElement.blur();"
                >{{ $option }}</a></li>
        @endforeach
    </ul>
</span>
<input type="hidden" name="{{ $formName }}" id="{{ $id }}-formField" value="{{ $selected['key'] }}" />

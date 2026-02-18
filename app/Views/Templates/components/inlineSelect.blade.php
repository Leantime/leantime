@props([
    'id' => '',
    'formName' => '',
    'options' => [],
    'selected' => '',
    'noSelection' => ''
])

<span class="tw:dropdown">
    <div
       tabindex="0"
       role="button"
       id="{{ $id }}-link"
       {{ $attributes->merge(["class" => "dropdown-toggle"]) }}>
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
    </div>
    <ul tabindex="0" class="dropdown-menu tw:dropdown-content tw:menu tw:bg-base-100 tw:rounded-box tw:z-50 tw:min-w-52 tw:p-2 tw:shadow-sm" id="{{ $id }}-options">
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

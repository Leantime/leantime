@props([
    'state' => $tpl->getToggleState("accordion_content-".$id) == 'closed' ? 'closed' : 'open',
    'id'
])

<div {{ $attributes->merge([ 'class' => 'accordionWrapper' ]) }}>

    @if(isset($actionlink) && $actionlink != '')
        <div class="pull-right pt-xs">
            {!! $actionlink !!}
        </div>
    @endif

    <a
        href="javascript:void(0)"
        class="accordion-toggle {{ $state }}"
        id="accordion_toggle_{{ $id }}"
        onclick="leantime.snippets.accordionToggle('{{ $id }}');"
    >
        <h5 {{ $title->attributes->merge([
            'class' => 'accordionTitle pb-15 text-l',
            'id' => "accordion_link_$id"
        ]) }}>
            <i class="fa fa-angle-{{ $state == 'closed' ? 'right' : 'down' }}"></i>
            {!! $title !!}
        </h5>
    </a>
    <div {{ $content->attributes->merge([
        'class' => "simpleAccordionContainer $state",
        'id' => "accordion_content-$id",
        'style' => $state =='closed' ? 'display:none;' : ''
    ]) }}>


        {!! $content !!}
    </div>
</div>

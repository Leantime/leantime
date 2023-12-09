@props([
    'state' => $tpl->getToggleState("accordion_content-".$id) == 'closed' ? 'closed' : 'open',
    'id'
])

<div class="accordionWrapper" {{ $attributes }}>
    <h5 class="accordionTitle tw-pb-15 tw-text-l" id="accordion_link_{{ $id }}">

        <a href="javascript:void(0)"
           class="accordion-toggle {{ $state }}"
           id="accordion_toggle_{{ $id }}"
           onclick="leantime.snippets.accordionToggle('{{ $id }}');">
            @if($tpl->getToggleState("accordion_content-".$id) == 'closed')
                <i class="fa fa-angle-right"></i>
            @else
                <i class="fa fa-angle-down"></i>
            @endif
                {{ $title }}
        </a>
    </h5>
    <div class="simpleAccordionContainer {{ $state }}" id="accordion_content-{{ $id }}" style="{{ $state =='closed' ? 'display:none;' : '' }}">
        {!! $content !!}
    </div>
</div>

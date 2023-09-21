@props([
    'state' => "open",
    'id'
])

<div class="accordionWrapper" {{ $attributes }}>
    <h5 class="accordionTitle tw-pb-15 tw-text-l" id="accordion_link_{{ $id }}">
        <a href="javascript:void(0)"
           class="accordion-toggle {{ $state }}"
           id="accordion_toggle_{{ $id }}"
           onclick="leantime.snippets.accordionToggle('{{ $id }}');">
                <i class="fa fa-angle-down"></i>
                <span class="fa fa-folder-open"></span>
                {{ $title }}
        </a>
    </h5>
    <div class="simpleAccordionContainer {{ $state }}" id="accordion_tickets-{{ $id }}">
        {!! $content !!}
    </div>
</div>

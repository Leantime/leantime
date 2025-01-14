@props([
    'state' => $tpl->getToggleState("accordion_content-".$id) == 'closed' ? 'closed' : 'open',
    'id',
    'light' => false
])

<div {{ $attributes->merge([ 'class' => 'accordionWrapper mb-md' ]) }}>

    @if(isset($actionlink) && $actionlink != '')
        <div class="pull-right pt-xs">
            {!! $actionlink !!}
        </div>
    @endif

    <a
        href="javascript:void(0)"
        class="block accordion-toggle font-bold {{ $state }} rounded-element @if($light)  text-primary-content @endif hover:bg-base-content/10"
        id="accordion_toggle_{{ $id }}"
        onclick="leantime.snippets.accordionToggle('{{ $id }}');"
    >
        <h5 {{ $title->attributes->merge([
            'class' => 'text-l',
            'id' => "accordion_link_$id"
        ]) }}>
            <span class="btn btn-circle btn-ghost btn-xs @if($light) hover:(bg-secondary/80 text-base-content) @endif">
                <i class="fa fa-angle-{{ $state == 'closed' ? 'right' : 'down' }} "></i>
            </span>{!! $title !!}
        </h5>
    </a>
    <div {{ $content->attributes->merge([
        'class' => "pl-lg py-md $state",
        'id' => "accordion_content-$id",
        'style' => $state =='closed' ? 'display:none;' : ''
    ]) }}>
        {!! $content !!}
    </div>
</div>

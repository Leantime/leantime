@props([
    'type' => '', //status, milestone, effort, priority, user, sprint
    'parentId' => '',
    'postUrl' => '',
])

@php
    $inputName = $type == 'milestone' ? 'headline' : 'name';
@endphp

<div {{ $attributes->merge([ 'class' => 'mx-2' ]) }}>
    <a href="javascript:void(0);" style="padding:10px; display:block; width:100%;"
        id="{{ $type }}_new_link_{{ $parentId }}"
        onclick="event.stopPropagation(); jQuery('#{{ $type }}_new_link_{{ $parentId }}').toggle('fast'); jQuery('#{{ $type }}_new_{{ $parentId }}').toggle('fast', function() { jQuery(this).find('input[name={{ $inputName }}]').focus(); });">
        <i class="fas fa-plus-circle"></i> Add {{ $type }}
    </a>
    <div class="hideOnLoad" id="{{ $type }}_new_{{ $parentId }}"
        style="padding-top:5px; padding-bottom:5px;">

        <form 
            hx-post={{ $postUrl }}
            hx-swap="none"
            {{ $attributes->merge(["class"=> "min-w-50"]) }}
        >
            <x-global::forms.text-input type="text" name="{{ $inputName }}"
            placeholder="Enter {{ $type }} name" title="{{ __('label.headline') }}" />
        
            
            @if($type == 'milestone')
                <input type="hidden" name="tags" value="blue" />
                <input type="hidden" name="editFrom" value="{{ dtHelper()->userNow()->formatDateForUser() }}" />
                <input type="hidden" name="editTo" value="{{ dtHelper()->userNow()->addDays(30)->formatDateForUser() }}" />
            @elseif ($type == 'sprint') 
                <input type="hidden" name="startDate" value="{{ dtHelper()->userNow()->next('Monday')->formatDateForUser() }}" />
                <input type="hidden" name="endDate" value="{{ dtHelper()->userNow()->next('Monday')->addDays(14)->formatDateForUser() }}" />
            @endif

            <x-global::forms.button type="submit" name="quickadd">
                Save
            </x-global::forms.button>

            <x-global::forms.button type="reset" content-role="tertiary"
                href="javascript:void(0);"
                onclick="jQuery('#{{ $type }}_new_{{ $parentId }}, #{{ $type }}_new_link_{{ $parentId }}').toggle('fast');">
                {{ __('links.cancel') }}
            </x-global::forms.button>

        </form>

        <div class="clearfix"></div>
    </div>
</div>
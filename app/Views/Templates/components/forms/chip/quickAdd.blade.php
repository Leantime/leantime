@props([
    'type' => '', //status, milestone, effort, priority, user, sprint
    'parentId' => '',
    'postUrl' => '',
    'redirectUrl' => '',
])

@php
    $inputName = $type == 'milestone' ? 'headline' : 'name';
@endphp

<div {{ $attributes->merge([ 'class' => '' ]) }}>
    <a href="javascript:void(0);" style="padding:10px; display:block; width:100%;"
        id="{{ $type }}_new_link_{{ $parentId }}"
        onclick="event.stopPropagation(); jQuery('#{{ $type }}_new_link_{{ $parentId }}').toggle('fast'); jQuery('#{{ $type }}_new_{{ $parentId }}').toggle('fast', function() { jQuery(this).find('input[name=headline]').focus(); });">
        <i class="fas fa-plus-circle"></i> Add {{ $type }}
    </a>
    <div class="hideOnLoad" id="{{ $type }}_new_{{ $parentId }}"
        style="padding-top:5px; padding-bottom:5px;">

        <form hx-post={{ $postUrl }}
            hx-redirect={{ $redirectUrl }}
            {{ $attributes->merge(["class"=> "min-w-50"]) }}
        >
            <x-global::forms.text-input type="text" name={{ $inputName }}
            placeholder="Enter {{ $type }} name" title="{{ __('label.headline') }}" />
        
            
            @if($type == 'milestone')
                <input type="hidden" name="tags" value="blue" />
                <input type="hidden" name="editFrom" value="{{ dtHelper()->userNow() }}" />
                <input type="hidden" name="editTo" value="{{ dtHelper()->userNow()->addDays(30) }}" />
            @elseif ($type == 'sprint') 
                <input type="hidden" name="startDate" value="{{ dtHelper()->userNow()->next('Monday') }}" />
                <input type="hidden" name="endDate" value="{{ dtHelper()->userNow()->next('Monday')->addDays(14)->formatDateTimeForDb() }}" />
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



        {{-- <form method="post" action="{{ BASE_URL }}/tickets/editMilestone" --}}
        {{-- {{ BASE_URL }}/tickets/editMilestone --}}
        {{-- "{{ BASE_URL }}/tickets/showAll" --}}

            {{-- @foreach ($options as $key => $value)
                <input type="hidden" name="{{ $type }}Field" value="{{ $key }}" />
            @endforeach  --}}
            
            {{-- <input type="hidden" name="tags" value="blue" />
            <input type="hidden" name="editFrom" value="{{ now()->format('m/d/Y') }}" />
            <input type="hidden" name="editTo" value="{{ now()->addDays(30)->format('m/d/Y') }}" /> --}}
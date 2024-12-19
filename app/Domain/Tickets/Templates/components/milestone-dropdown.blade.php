@props(['ticket', 'milestones'])

<div class="dropdown ticketDropdown milestoneDropdown colorized show firstDropdown">
    @php
        $milestoneLabelText = '<span class="text">';
        if ($ticket['milestoneid'] != '' && $ticket['milestoneid'] != 0) {
            $milestoneLabelText .= $ticket['milestoneHeadline'];
        } else {
            $milestoneLabelText .= __('label.no_milestone');
        }
        $milestoneLabelText .= '</span>&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>';
    @endphp

    <x-global::actions.dropdown 
        :label-text="$milestoneLabelText" 
        contentRole="link"
        position="bottom" 
        align="start"
    >
        <x-slot:menu>
            <li class="nav-header border">{{ __('dropdown.choose_milestone') }}</li>
            <x-global::actions.dropdown.item
                style="background-color: #b0b0b0"
                href="javascript:void(0);"
                data-label="{{ __('label.no_milestone') }}"
                data-value="{{ $ticket['id'] . '_0_#b0b0b0' }}"
            >
                {{ __('label.no_milestone') }}
            </x-global::actions.dropdown.item>
            
            @foreach ($milestones as $milestone)
                <x-global::actions.dropdown.item
                    href="javascript:void(0);"
                    data-label="{{ $milestone->headline }}"
                    data-value="{{ $ticket['id'] . '_' . $milestone->id . '_' . $milestone->tags }}"
                    id="ticketMilestoneChange{{ $ticket['id'] . $milestone->id }}"
                    style="background-color: {{ $milestone->tags }}"
                >
                    {{ $milestone->headline }}
                </x-global::actions.dropdown.item>
            @endforeach
        </x-slot:menu>
    </x-global::actions.dropdown>
</div> 
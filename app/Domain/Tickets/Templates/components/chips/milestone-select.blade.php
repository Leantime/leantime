@props([
    'contentRole' => 'primary',
    'variant' => 'chip', //chip, select
    'milestones' => [],
    'label' => true,
    'labelPosition' => 'top',
    'dropdownPosition' => 'left'
])

<x-global::forms.select
    name='status'
    search="false"
    :dropdown-position="$dropdownPosition"
    :label-position="$labelPosition"
    :variant="$variant"
    :content-role="$contentRole"
>

    @if($label)
        <x-slot:label-text>
            <x-global::content.icon icon="emergency_heat" /> {!!  __('label.priority') !!}
        </x-slot:label-text>
    @endif

    <x-global::forms.select.option :value="''">
        <x-global::elements.badge state="trivial" content-role="secondary">
            {!!  __('label.no_milestone') !!}
        </x-global::elements.badge>
    </x-global::forms.select.option>

    @foreach ($milestones as $milestone)
        <x-global::forms.select.option
            :value="strtolower($milestone->id)"
            :selected="strtolower($milestone->id) == strtolower( $ticket->milestoneId ?? '') ? 'true' : 'false'">

            <x-global::elements.badge :state="$milestone->tags" content-role="primary">
                <x-global::content.icon icon="label_important"/> {{ $milestone->headline }}
            </x-global::elements.badge>

        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>











{{--@props(['ticket', 'milestones'])--}}

{{--<div class="dropdown ticketDropdown milestoneDropdown colorized show firstDropdown">--}}
{{--    @php--}}
{{--        $milestoneLabelText = '<span class="text">';--}}
{{--        if ($ticket['milestoneid'] != '' && $ticket['milestoneid'] != 0) {--}}
{{--            $milestoneLabelText .= $ticket['milestoneHeadline'];--}}
{{--        } else {--}}
{{--            $milestoneLabelText .= __('label.no_milestone');--}}
{{--        }--}}
{{--        $milestoneLabelText .= '</span>&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>';--}}
{{--    @endphp--}}

{{--    <x-global::actions.dropdown--}}
{{--        :label-text="$milestoneLabelText"--}}
{{--        contentRole="link"--}}
{{--        position="bottom"--}}
{{--        align="start"--}}
{{--        :selectable="true"--}}
{{--        class="milestoneDropdown"--}}
{{--    >--}}
{{--        <x-slot:menu>--}}
{{--            <li class="nav-header border">{{ __('dropdown.choose_milestone') }}</li>--}}
{{--            <x-global::actions.dropdown.item--}}
{{--                style="background-color: #b0b0b0"--}}
{{--                href="javascript:void(0);"--}}
{{--                data-label="{{ __('label.no_milestone') }}"--}}
{{--                data-value="{{ $ticket['id'] . '_0_#b0b0b0' }}"--}}
{{--            >--}}
{{--                {{ __('label.no_milestone') }}--}}
{{--            </x-global::actions.dropdown.item>--}}

{{--            @foreach ($milestones as $milestone)--}}
{{--                <x-global::actions.dropdown.item--}}
{{--                    href="javascript:void(0);"--}}
{{--                    data-label="{{ $milestone->headline }}"--}}
{{--                    data-value="{{ $ticket['id'] . '_' . $milestone->id . '_' . $milestone->tags }}"--}}
{{--                    id="ticketMilestoneChange{{ $ticket['id'] . $milestone->id }}"--}}
{{--                    style="background-color: {{ $milestone->tags }}"--}}
{{--                    data-style="background-color: {{ $milestone->tags }}"--}}
{{--                    buttonStyle="background-color: {{ $milestone->tags }}"--}}
{{--                >--}}
{{--                    {{ $milestone->headline }}--}}
{{--                </x-global::actions.dropdown.item>--}}
{{--            @endforeach--}}
{{--        </x-slot:menu>--}}
{{--    </x-global::actions.dropdown>--}}
{{--</div>--}}

{{--<script type="module">--}}
{{--    import "@mix('/js/Domain/Tickets/Js/ticketsController.js')"--}}

{{--    jquery(document).ready(function() {--}}
{{--        ticketsController.initMilestoneDropdown();--}}
{{--    });--}}
{{--</script>--}}



<x-global::forms.select id='priority2' name='priority2' search="false">
    <x-slot:label-text>
        <span class="fa fa-fire-flame-simple text-error"></span> {!!  __('label.priority') !!}
    </x-slot:label-text>
    <x-slot:validation-text>

    </x-slot:validation-text>

    <x-global::forms.select.option
        :value="''">
        {{  __('label.priority_not_defined') }}
    </x-global::forms.select.option>
    @foreach ($priorities as $priorityKey => $priorityValue)
        <x-global::forms.select.option
            :value="strtolower($priorityKey)"
            :selected="strtolower($priorityKey) == strtolower( $ticket->priority ?? '') ? 'true' : 'false'">

            <span class="priority-text-{{ $priorityKey }} ">
                <i class="fa fa-fire-flame-curved size-sm pr-xs"></i> {{ $priorityValue }}
            </span>

        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>

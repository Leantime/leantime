<x-global::forms.select id='priority2' name='priority2' search="false">
    <x-global::forms.select.option
        :value="''">
        {{  __('label.priority_not_defined') }}
    </x-global::forms.select.option>
    @foreach ($priorities as $priorityKey => $priorityValue)
        <x-global::forms.select.option
            :value="strtolower($priorityKey)"
            :selected="strtolower($priorityKey) == strtolower( $ticket->priority ?? '') ? 'true' : 'false'">
            <span class="priority-text-{{ $priorityKey }} "><i class="fa fa-flag tw-size-sm tw-pr-xs"></i> {{ $priorityValue }}</span>
        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>

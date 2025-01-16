@props([
    'ticket' => [],
    'allAssignedprojects' => [],
    'statusLabels' => [],
    'ticketTypes' => [],
    'url' => '',
    'milestones' => [],
    'sprints' => []
])


<x-global::forms.select
    name='projectId'
    search="false"
    dropdown-position="left"
    label-position="top"
    hx-post="{{ $url }}"
    hx-trigger="change"
    hx-swap="none"
    class="select-bordered"
>
    <x-slot:label-text>
        <x-global::content.icon icon="business_center" /> {!!  __('label.project') !!}
    </x-slot:label-text>
    @foreach ($allAssignedprojects as $project)

        <x-global::forms.select.option
            :value="strtolower($project['id'])"
            :selected="( $project['id'] == ($ticket->projectId ?? '') || session('currentProject') == $project['id']) ? 'true' : 'false'">
            {{ $tpl->escape($project['name']) }}

        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>

<x-tickets::chips.milestone-select
    :milestones="$milestones"
    :ticket="(object)$ticket"
    :showLabel="true"
    variant="select"
    label-position="top"
    dropdown-position="left" />

<x-tickets::chips.sprint-select
    :sprints="$sprints"
    :ticket="(object)$ticket"
    :showLabel="true"
    variant="select"
    label-position="top"
    dropdown-position="left" />

<x-global::forms.select
    name='dependingTicketId'
    search="false"
    dropdown-position="left"
    label-position="top"
    hx-post="{{ $url }}"
    hx-trigger="change"
    hx-swap="none"
    class="select-bordered"
>
    <x-slot:label-text>
        <x-global::content.icon icon="tenancy" /> {!!  __('label.related_to') !!}
    </x-slot:label-text>
    <x-global::forms.select.option
        value=""
    >
        {{ __('label.not_related') }}
    </x-global::forms.select.option>

    @if (is_array($tpl->get('ticketParents')))

        @foreach ($tpl->get('ticketParents') as $ticketRow)

        <x-global::forms.select.option
            :value="strtolower($ticketRow->id)"
            :selected="( $ticket->dependingTicketId == $ticketRow->id) ? 'true' : 'false'">
            {{ $ticketRow->headline }}

        </x-global::forms.select.option>
    @endforeach
    @endif
</x-global::forms.select>


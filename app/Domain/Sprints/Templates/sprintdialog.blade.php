@extends($layout)

@section('content')

@php
    $currentSprint = $sprint;
    $id = '';
    if (isset($currentSprint->id)) {
        $id = $currentSprint->id;
    }
    $currentProject = session('currentProject');
@endphp

<x-global::actions.modal-header>
    <x-slot:title><i class="fa fa-list-1-2"></i> {!! __('label.sprint') !!} {{ $currentSprint->name }}</x-slot:title>
    @if (isset($currentSprint->id) && $currentSprint->id != '')
        @can('sprints.delete', $currentSprint->projectId ?? null)
        <x-slot:actions>
            <li><a href="#/sprints/delSprint/{{ $currentSprint->id }}" class="delete"><i class="fa fa-trash-can"></i> {!! __('links.delete_sprint') !!}</a></li>
        </x-slot:actions>
        @endcan
    @endif
</x-global::actions.modal-header>{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/sprints/editSprint/{{ $id }}">

    <x-global::forms.field for="sprintName" :label="__('label.sprint_name')">
        <x-global::forms.text-input name="name" id="sprintName" value="{{ $currentSprint->name }}" placeholder="{{ __('label.sprint_name') }}" />
    </x-global::forms.field>

    <x-global::forms.field for="sprintProject" :label="__('label.project')">
        @if (! empty($lockProject))
            {{-- Program sprint: project is fixed to the owning program and cannot be reassigned. --}}
            @php $lockedProject = $allAssignedprojects[0] ?? null; @endphp
            <input type="hidden" name="projectId" value="{{ $lockedProject['id'] ?? $currentProject }}" />
            <x-global::forms.select id="sprintProject" disabled>
                <option selected>{{ $tpl->escape($lockedProject['name'] ?? '') }}</option>
            </x-global::forms.select>
        @else
            <x-global::forms.select name="projectId" id="sprintProject">
                @foreach ($allAssignedprojects as $project)
                    <option value="{{ $project['id'] }}"
                        @if ((isset($currentSprint) && ($currentSprint->projectId == $project['id'] || $currentProject == $project['id'])) || (! isset($currentSprint) && $currentProject == $project['id']))
                            selected
                        @endif
                    >{{ $tpl->escape($project['name']) }}</option>
                @endforeach
            </x-global::forms.select>
        @endif
    </x-global::forms.field>

    <br />
    <p>{!! __('label.sprint_dates') !!}</p>

    <x-global::forms.field for="sprintStart" :label="__('label.first_day')">
        <input type="text" name="startDate" autocomplete="off" value="{{ format($currentSprint->startDate)->date() }}" placeholder="{{ __('language.dateformat') }}" id="sprintStart" />
    </x-global::forms.field>

    <x-global::forms.field for="sprintEnd" :label="__('label.last_day')">
        <input type="text" name="endDate" autocomplete="off" value="{{ format($currentSprint->endDate)->date() }} " placeholder="{{ __('language.dateformat') }}" id="sprintEnd" />
    </x-global::forms.field>

    <br />

    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" />
        </div>
        <div class="col-md-6 align-right padding-top-sm">
        </div>
    </div>

</form>

@once
@push('scripts')
<script>
    leantime.ticketsController.initSprintDates();
</script>
@endpush
@endonce

@endsection

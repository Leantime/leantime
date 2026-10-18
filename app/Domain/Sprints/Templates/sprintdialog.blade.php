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
    @if (isset($currentSprint->id) && $currentSprint->id != '' && $login::userIsAtLeast($roles::$editor))
        <x-slot:actions>
            <li><a href="#/sprints/delSprint/{{ $currentSprint->id }}" class="delete"><i class="fa fa-trash-can"></i> {!! __('links.delete_sprint') !!}</a></li>
        </x-slot:actions>
    @endif
</x-global::actions.modal-header>{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/sprints/editSprint/{{ $id }}">

    <x-global::forms.label>{!! __('label.sprint_name') !!}</x-global::forms.label>
    <x-global::forms.text-input name="name" value="{{ $currentSprint->name }}" placeholder="{{ __('label.sprint_name') }}" /><br />

    <x-global::forms.label>{!! __('label.project') !!}</x-global::forms.label>
    @if (! empty($lockProject))
        {{-- Program sprint: project is fixed to the owning program and cannot be reassigned. --}}
        @php $lockedProject = $allAssignedprojects[0] ?? null; @endphp
        <input type="hidden" name="projectId" value="{{ $lockedProject['id'] ?? $currentProject }}" />
        <x-global::forms.select disabled>
            <option selected>{{ $tpl->escape($lockedProject['name'] ?? '') }}</option>
        </x-global::forms.select><br />
    @else
        <x-global::forms.select name="projectId">
            @foreach ($allAssignedprojects as $project)
                <option value="{{ $project['id'] }}"
                    @if ((isset($currentSprint) && ($currentSprint->projectId == $project['id'] || $currentProject == $project['id'])) || (! isset($currentSprint) && $currentProject == $project['id']))
                        selected
                    @endif
                >{{ $tpl->escape($project['name']) }}</option>
            @endforeach
        </x-global::forms.select><br />
    @endif

    <br /><br />
    <p>{!! __('label.sprint_dates') !!}</p><br/>
    <x-global::forms.label>{!! __('label.first_day') !!}</x-global::forms.label>
    <input type="text" name="startDate" autocomplete="off" value="{{ format($currentSprint->startDate)->date() }}" placeholder="{{ __('language.dateformat') }}" id="sprintStart" /><br />

    <x-global::forms.label>{!! __('label.last_day') !!}</x-global::forms.label>
    <input type="text" name="endDate" autocomplete="off" value="{{ format($currentSprint->endDate)->date() }} " placeholder="{{ __('language.dateformat') }}" id="sprintEnd" />

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

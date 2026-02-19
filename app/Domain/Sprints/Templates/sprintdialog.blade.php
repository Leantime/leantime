@php
    $currentSprint = $tpl->get('sprint');
    $id = isset($currentSprint->id) ? $currentSprint->id : '';
    $currentProject = session('currentProject');
@endphp

<h4 class="widgettitle title-light"><i class="fa fa-list-1-2"></i> {{ __('label.sprint') }} {{ $currentSprint->name }}</h4>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/sprints/editSprint/{{ $id }}">

    <label>{{ __('label.sprint_name') }}</label>
    <x-global::forms.input name="name" value="{{ $currentSprint->name }}" placeholder="{{ __('label.sprint_name') }}" /><br />

    <label>{{ __('label.project') }}</label>
    <x-global::forms.select name="projectId">
        @foreach($allAssignedprojects as $project)
            <option value="{{ $project['id'] }}"
                {{ (isset($currentSprint) && ($currentSprint->projectId == $project['id'] || $currentProject == $project['id'])) || (!isset($currentSprint) && $currentProject == $project['id']) ? 'selected' : '' }}>{{ e($project['name']) }}</option>
        @endforeach
    </x-global::forms.select><br />

    <br /><br />
    <p>{{ __('label.sprint_dates') }}</p><br/>
    <label>{{ __('label.first_day') }}</label>
    <x-global::forms.date name="startDate" id="sprintStart" value="{{ format($currentSprint->startDate)->date() }}" placeholder="{{ __('language.dateformat') }}" /><br />

    <label>{{ __('label.last_day') }}</label>
    <x-global::forms.date name="endDate" id="sprintEnd" value="{{ format($currentSprint->endDate)->date() }}" placeholder="{{ __('language.dateformat') }}" />

    <br />

    <div class="row">
        <div class="col-md-6">
            <x-global::button submit type="primary">{{ __('buttons.save') }}</x-global::button>
        </div>
        <div class="col-md-6 align-right padding-top-sm">
            @if(isset($currentSprint->id) && $currentSprint->id != '' && $login::userIsAtLeast($roles::$editor))
                <a href="{{ BASE_URL }}/sprints/delSprint/{{ $currentSprint->id }}" class="delete formModal sprintModal"><i class="fa fa-trash"></i> {{ __('links.delete_sprint') }}</a>
            @endif
        </div>
    </div>

</form>

<script>
    leantime.ticketsController.initSprintDates();
</script>

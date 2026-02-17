@php
    $currentSprint = $tpl->get('sprint');
    $id = isset($currentSprint->id) ? $currentSprint->id : '';
    $currentProject = session('currentProject');
@endphp

<h4 class="widgettitle title-light"><i class="fa fa-list-1-2"></i> {{ __('label.sprint') }} {{ $currentSprint->name }}</h4>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/sprints/editSprint/{{ $id }}">

    <label>{{ __('label.sprint_name') }}</label>
    <input type="text" name="name" value="{{ $currentSprint->name }}" placeholder="{{ __('label.sprint_name') }}"/><br />

    <label>{{ __('label.project') }}</label>
    <select name="projectId">
        @foreach($allAssignedprojects as $project)
            <option value="{{ $project['id'] }}"
                {{ (isset($currentSprint) && ($currentSprint->projectId == $project['id'] || $currentProject == $project['id'])) || (!isset($currentSprint) && $currentProject == $project['id']) ? 'selected' : '' }}>{{ e($project['name']) }}</option>
        @endforeach
    </select><br />

    <br /><br />
    <p>{{ __('label.sprint_dates') }}</p><br/>
    <label>{{ __('label.first_day') }}</label>
    <input type="text" name="startDate" autocomplete="off" value="{{ format($currentSprint->startDate)->date() }}" placeholder="{{ __('language.dateformat') }}" id="sprintStart" /><br />

    <label>{{ __('label.last_day') }}</label>
    <input type="text" name="endDate" autocomplete="off" value="{{ format($currentSprint->endDate)->date() }} " placeholder="{{ __('language.dateformat') }}" id="sprintEnd" />

    <br />

    <div class="tw:grid tw:grid-cols-2 tw:gap-6">
        <div>
            <input type="submit" value="{{ __('buttons.save') }}"/>
        </div>
        <div class="tw:text-right padding-top-sm">
            @if(isset($currentSprint->id) && $currentSprint->id != '' && $login::userIsAtLeast($roles::$editor))
                <a href="{{ BASE_URL }}/sprints/delSprint/{{ $currentSprint->id }}" class="delete formModal sprintModal"><i class="fa fa-trash"></i> {{ __('links.delete_sprint') }}</a>
            @endif
        </div>
    </div>

</form>

<script>
    leantime.ticketsController.initSprintDates();
</script>

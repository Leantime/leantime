@php
    $project = $tpl->get('project');
@endphp

<h4 class="widgettitle title-light">{{ sprintf(__('headlines.duplicate_project_x'), $project['name']) }}</h4>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/projects/duplicateProject/{{ $project['id'] }}">

    <label>{{ __('label.newProjectName') }}</label>
    <input type="text" name="projectName" value="{{ __('label.copy_of') }} {{ e($project['name']) }}" /><br />

    <label>{{ __('label.planned_start_date') }}</label>
    <input type="text" name="startDate" class="projectDateFrom" value="{{ format(date('Y-m-d'))->date() }}" placeholder="{{ __('language.dateformat') }}" id="sprintStart" /><br />

    <label>{{ __('label.client_product') }}</label>
    <select name="clientId" id="clientId">
        @foreach($tpl->get('allClients') as $row)
            <option value="{{ $row['id'] }}"
                {{ $project['clientId'] == $row['id'] ? 'selected="selected"' : '' }}>{{ e($row['name']) }}</option>
        @endforeach
    </select>
    <br />
    <input style="float:left; margin-right:5px;"
           type="checkbox" name="assignSameUsers" id="assignSameUsers"/>
    <label for="assignSameUsers">{{ __('label.assignSameUsers') }}</label>

    <br />

    <div class="tw:grid tw:md:grid-cols-2 tw:gap-6">
        <div>
            <input type="submit" value="{{ __('buttons.duplicate') }}"/>
        </div>
        <div class="tw:text-right padding-top-sm">
        </div>
    </div>

</form>

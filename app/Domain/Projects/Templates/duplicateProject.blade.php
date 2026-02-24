@php
    $project = $tpl->get('project');
@endphp

<h4 class="widgettitle title-light">{{ sprintf(__('headlines.duplicate_project_x'), $project['name']) }}</h4>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/projects/duplicateProject/{{ $project['id'] }}">

    <label>{{ __('label.newProjectName') }}</label>
    <x-globals::forms.input name="projectName" value="{{ __('label.copy_of') }} {{ e($project['name']) }}" /><br />

    <label>{{ __('label.planned_start_date') }}</label>
    <input type="text" name="startDate" class="projectDateFrom" value="{{ format(date('Y-m-d'))->date() }}" placeholder="{{ __('language.dateformat') }}" id="sprintStart" /><br />

    <label>{{ __('label.client_product') }}</label>
    <x-globals::forms.select name="clientId" id="clientId">
        @foreach($tpl->get('allClients') as $row)
            <option value="{{ $row['id'] }}"
                {{ $project['clientId'] == $row['id'] ? 'selected="selected"' : '' }}>{{ e($row['name']) }}</option>
        @endforeach
    </x-globals::forms.select>
    <br />
    <x-globals::forms.checkbox name="assignSameUsers" label="{{ __('label.assignSameUsers') }}" />

    <br />

    <div class="row">
        <div class="col-md-6">
            <x-globals::forms.button submit type="primary">{{ __('buttons.duplicate') }}</x-globals::forms.button>
        </div>
        <div class="col-md-6 align-right padding-top-sm">
        </div>
    </div>

</form>

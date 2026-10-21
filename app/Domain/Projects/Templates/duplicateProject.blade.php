<x-global::actions.modal-header>
    <x-slot:title>{!! sprintf(__('headlines.duplicate_project_x'), e($project['name'])) !!}</x-slot:title>
</x-global::actions.modal-header>{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/projects/duplicateProject/{{ $project['id'] }}">

    <x-global::forms.label>{!! __('label.newProjectName') !!}</x-global::forms.label>
    <x-global::forms.text-input name="projectName" value="{!! __('label.copy_of') !!} {{ $project['name'] }}" /><br />

    <x-global::forms.label>{!! __('label.planned_start_date') !!}</x-global::forms.label>
    <input type="text" name="startDate" class="projectDateFrom" value="{{ dtHelper()->userNow()->formatDateForUser() }}" placeholder="{{ __('language.dateformat') }}" id="sprintStart" /><br />

    <x-global::forms.label>{!! __('label.client_product') !!}</x-global::forms.label>
    <x-global::forms.select name="clientId" id="clientId">
        @foreach ($allClients as $row)
            <option value="{{ $row['id'] }}"
                @if ($project['clientId'] == $row['id']) selected=selected @endif
            >{{ $row['name'] }}</option>
        @endforeach
    </x-global::forms.select>
    <br />
    <input style="float:left; margin-right:5px;"
           type="checkbox" name="assignSameUsers" id="assignSameUsers"/>
    <label for="assignSameUsers">{!! __('label.assignSameUsers') !!}</label>

    <br />

    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.duplicate')" />
        </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
    </div>

</form>

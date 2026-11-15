<x-global::actions.modal-header>
    <x-slot:title>{!! sprintf(__('headlines.duplicate_project_x'), e($project['name'])) !!}</x-slot:title>
</x-global::actions.modal-header>{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/projects/duplicateProject/{{ $project['id'] }}">

    <x-global::forms.field for="duplicateProjectName" :label="__('label.newProjectName')">
        <x-global::forms.text-input name="projectName" id="duplicateProjectName" value="{!! __('label.copy_of') !!} {{ $project['name'] }}" />
    </x-global::forms.field>

    <x-global::forms.field for="sprintStart" :label="__('label.planned_start_date')">
        <input type="text" name="startDate" class="projectDateFrom" value="{{ dtHelper()->userNow()->formatDateForUser() }}" placeholder="{{ __('language.dateformat') }}" id="sprintStart" />
    </x-global::forms.field>

    <x-global::forms.field for="duplicateClientId" :label="__('label.client_product')">
        <x-global::forms.select name="clientId" id="duplicateClientId">
            @foreach ($allClients as $row)
                <option value="{{ $row['id'] }}"
                    @if ($project['clientId'] == $row['id']) selected=selected @endif
                >{{ $row['name'] }}</option>
            @endforeach
        </x-global::forms.select>
    </x-global::forms.field>
    <x-global::forms.checkbox variant="toggle" name="assignSameUsers" id="assignSameUsers" value="on">{!! __('label.assignSameUsers') !!}</x-global::forms.checkbox>

    <br />

    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.duplicate')" />
        </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
    </div>

</form>

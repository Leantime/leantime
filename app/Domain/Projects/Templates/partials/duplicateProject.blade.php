<x-global::content.modal.modal-buttons/>

<?php
$project = $tpl->get('project');
?>

<h4 class="widgettitle title-light"><?php echo sprintf($tpl->__('headlines.duplicate_project_x'), $project['name']); ?></h4>

@displayNotification()

<x-global::content.modal.form action="{{ BASE_URL }}/projects/duplicateProject/<?php echo $project['id'];?>">

<x-global::forms.text-input 
    type="text" 
    name="projectName" 
    value="{{ $tpl->__('label.copy_of') }} {{ $tpl->escape($project['name']) }}" 
    labelText="{{ $tpl->__('label.newProjectName') }}" 
    variant="title" 
/>
<br />

    <label><?=$tpl->__('label.planned_start_date') ?></label>
    <input type="text" name="startDate" class="projectDateFrom" value="<?php echo format(date("Y-m-d"))->date()?>" placeholder="<?=$tpl->__('language.dateformat') ?>" id="sprintStart" /><br />

    <label><?=$tpl->__('label.client_product') ?></label>
    <select name="clientId" id="clientId">
        <?php foreach ($tpl->get('allClients') as $row) { ?>
            <option value="<?php echo $row['id']; ?>"
                <?php if ($project['clientId'] == $row['id']) {
                    ?> selected=selected
                <?php } ?>><?php $tpl->e($row['name']); ?></option>
        <?php } ?>
    </select>
    <br />
    <x-global::forms.checkbox
        name="assignSameUsers"
        id="assignSameUsers"
        {{-- :checked="$ add condition here" --}}
        labelText="{{ __("label.assignSameUsers") }}"
        labelPosition="right"
    />
    <br />

    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button type="submit">
                {{ __('buttons.duplicate') }}
            </x-global::forms.button>
        </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
    </div>

</x-global::content.modal.form>


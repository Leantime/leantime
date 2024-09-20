<x-global::content.modal.modal-buttons/>

<?php
$currentSprint = $tpl->get('sprint');
?>

<h4 class="widgettitle title-light"><i class="fa fa-list-1-2"></i> <?=$tpl->__('label.sprint') ?> <?php echo $currentSprint->name?></h4>

@displayNotification()

<?php
$id = "";
if (isset($currentSprint->id)) {
    $id = $currentSprint->id;
}
?>

<x-global::content.modal.form action="{{ BASE_URL}}/sprints/editSprint/{{ $id }}">
<x-global::forms.text-input 
    type="text" 
    name="name" 
    value="{{ $currentSprint->name }}" 
    placeholder="{{ $tpl->__('label.sprint_name') }}" 
    labelText="{{ $tpl->__('label.sprint_name') }}" 
    variant="title" 
/>
<br />

    <label><?=$tpl->__('label.project') ?></label>
    <select name="projectId">
        <?php foreach($allAssignedprojects as $project) { ?>
            <option value="<?=$project['id'] ?>"
                    <?php
                    if(isset($currentSprint)) {
                        if($currentSprint->projectId == $project['id']) {
                            echo "selected";
                        }
                    }elseif( session("currentProject") == $project['id']){
                        echo "selected";
                    }
                    ?>
        ><?=$tpl->escape($project["name"]); ?></option>
        <?php } ?>
    </select><br />

    <br /><br />
    <p><?=$tpl->__('label.sprint_dates') ?></p><br/>
    <x-global::forms.text-input 
        type="text" 
        name="startDate" 
        id="sprintStart" 
        autocomplete="off" 
        value="{{ $currentSprint->startDate }}" 
        placeholder="{{ $tpl->__('language.dateformat') }}" 
        labelText="{{ $tpl->__('label.first_day') }}"
    />
    <br />

    <x-global::forms.text-input 
        type="text" 
        name="endDate" 
        id="sprintEnd" 
        autocomplete="off" 
        value="{{ $currentSprint->endDate }}" 
        placeholder="{{ $tpl->__('language.dateformat') }}" 
        labelText="{{ $tpl->__('label.last_day') }}"
    />

    <br />

    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button type="submit">
                {{ __('buttons.save') }}
            </x-global::forms.button>
        </div>
        <div class="col-md-6 align-right padding-top-sm">
            <?php if (isset($currentSprint->id) && $currentSprint->id != '' && $login::userIsAtLeast($roles::$editor)) { ?>
                <a href="{{ BASE_URL }}/sprints/delSprint/<?php echo $currentSprint->id; ?>" class="delete formModal sprintModal"><i class="fa fa-trash"></i> <?=$tpl->__('links.delete_sprint') ?></a>
            <?php } ?>
        </div>
    </div>

</x-global::content.modal.form>

<script>
    leantime.ticketsController.initSprintDates();
</script>


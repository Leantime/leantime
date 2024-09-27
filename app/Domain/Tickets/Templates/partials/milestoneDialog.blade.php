<x-global::content.modal.modal-buttons/>

<?php
$currentMilestone = $tpl->get('milestone');
$milestones = $tpl->get('milestones');
$statusLabels = $tpl->get('statusLabels');
?>

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="{{ BASE_URL }}/tickets/roadmap?showMilestoneModal=<?php echo $currentMilestone->id; ?>";
        }
    }
</script>

<div class="modal-icons">
    <?php if (isset($currentMilestone->id) && $currentMilestone->id != '') {?>
        <a href="#/tickets/delMilestone/<?php echo $currentMilestone->id; ?>" class="danger" data-tippy-content="Delete"><i class='fa fa-trash-can'></i></a>
    <?php } ?>
</div>

<h4 class="widgettitle title-light"><?=$tpl->__("headline.milestone"); ?> </h4>

@displayNotification()

<x-global::content.modal.form action="{{ BASE_URL }}/tickets/editMilestone/{{ $currentMilestone->id }}">

    <x-global::forms.text-input 
        type="text" 
        name="headline" 
        value="{!! $tpl->escape($currentMilestone->headline) !!}" 
        placeholder="{!! $tpl->__('label.milestone_title') !!}" 
        labelText="{!! $tpl->__('label.milestone_title') !!}"
    />
    <br />

    <x-global::forms.select 
        name="projectId" 
        class="w-full" 
        labelText="{!! __('label.project') !!}"
    >
        @foreach ($allAssignedprojects as $project)
            @if (empty($project['type']) || $project['type'] == 'project')
                <x-global::forms.select.select-option 
                    value="{{ $project['id'] }}" 
                    :selected="(!empty($currentMilestone->projectId) && $currentMilestone->projectId == $project['id']) || session('currentProject') == $project['id']">
                    {!! $tpl->escape($project['name']) !!}
                </x-global::forms.select.select-option>
            @endif
        @endforeach
    </x-global::forms.select>


    <x-global::forms.select 
        id="status-select" 
        name="status" 
        class="span11" 
        :placeholder="isset($statusLabels[$currentMilestone->status]) ? $statusLabels[$currentMilestone->status]['name'] : ''"
        labelText="{!! __('label.todo_status') !!}"
    >
        @foreach ($statusLabels as $key => $label)
            <x-global::forms.select.select-option 
                value="{{ $key }}" 
                :selected="$currentMilestone->status == $key">
                {!! $tpl->escape($label['name']) !!}
            </x-global::forms.select.select-option>
        @endforeach
    </x-global::forms.select>

    <x-global::forms.select 
        name="dependentMilestone" 
        class="span11" 
        labelText="{!! __('label.dependent_on') !!}"
    >
        <x-global::forms.select.select-option value="">
            {!! __('label.no_dependency') !!}
        </x-global::forms.select.select-option>

        @foreach ($tpl->get('milestones') as $milestoneRow)
            @if ($milestoneRow->id !== $currentMilestone->id)
                <x-global::forms.select.select-option 
                    value="{{ $milestoneRow->id }}" 
                    :selected="$currentMilestone->milestoneid == $milestoneRow->id">
                    {!! $tpl->escape($milestoneRow->headline) !!}
                </x-global::forms.select.select-option>
            @endif
        @endforeach
    </x-global::forms.select>

    <x-global::forms.select 
        name="editorId" 
        class="user-select span11" 
        :placeholder="__('input.placeholders.filter_by_user')" 
        labelText="{!! __('label.owner') !!}"
    >
        <x-global::forms.select.select-option value="">
            {!! __('dropdown.not_assigned') !!}
        </x-global::forms.select.select-option>

        @foreach ($tpl->get('users') as $userRow)
            <x-global::forms.select.select-option 
                value="{{ $userRow['id'] }}" 
                :selected="$currentMilestone->editorId == $userRow['id']">
                {!! $tpl->escape($userRow['firstname']) . ' ' . $tpl->escape($userRow['lastname']) !!}
            </x-global::forms.select.select-option>
        @endforeach
    </x-global::forms.select>


    <x-global::forms.text-input 
        type="text" 
        name="tags" 
        value="{!! $currentMilestone->tags !!}" 
        placeholder="{!! $tpl->__('input.placeholders.pick_a_color') !!}" 
        labelText="{!! $tpl->__('label.color') !!}" 
        autocomplete="off" 
        class="simpleColorPicker"
    />
    <br />
    <x-global::forms.text-input 
        type="text" 
        name="editFrom" 
        id="milestoneEditFrom" 
        value="{!! format($currentMilestone->editFrom)->date() !!}" 
        placeholder="{!! $tpl->__('language.dateformat') !!}" 
        labelText="{!! $tpl->__('label.planned_start_date') !!}" 
        autocomplete="off" 
    />
    <br />

    <x-global::forms.text-input 
        type="text" 
        name="editTo" 
        id="milestoneEditTo" 
        value="{!! format($currentMilestone->editTo)->date() !!}" 
        placeholder="{!! $tpl->__('language.dateformat') !!}" 
        labelText="{!! $tpl->__('label.planned_end_date') !!}" 
        autocomplete="off" 
    />
    <br />

    <div class="row">
        <div class="col-md-6">
        <x-global::forms.button 
            type="submit"
            class="btn btn-primary">
            {{ __('buttons.save') }}
        </x-global::forms.button>
                </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
    </div>

</x-global::content.modal.form>

    <?php
    if (isset($currentMilestone->id) && $currentMilestone->id !== '') {
        ?>
    <br />
    <input type="hidden" name="comment" value="1" />
    @include("comments::includes.generalComment", ["formUrl" => BASE_URL . "/tickets/editMilestone/" . $currentMilestone->id])

    <?php } ?>

<script type="text/javascript">
    jQuery(document).ready(function(){

        leantime.ticketsController.initSimpleColorPicker();
        leantime.ticketsController.initMilestoneDates();

        <?php if (!$login::userIsAtLeast($roles::$editor)) { ?>
            leantime.authController.makeInputReadonly(".nyroModalCont");
        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php }?>


    })
</script>


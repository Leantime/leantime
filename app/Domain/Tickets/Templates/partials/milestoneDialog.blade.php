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

<div class="modal-icons align-right">
    <a href="#/tickets/delMilestone/<?php echo $currentMilestone->id; ?>" class="danger" data-tippy-content="Delete"><i class='fa fa-trash-can'></i></a>
    <?php if (isset($currentMilestone->id) && $currentMilestone->id != '') {?>
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


    <x-global::forms.select 
        name="projectId"
        id="projectId"
        labelText="{{ __('label.project') }}"
    >
        @foreach ($allAssignedprojects as $project)
            @if (empty($project['type']) || $project['type'] == "project")
                <x-global::forms.select.option
                    :value="$project['id']"
                    :selected="(!empty($currentMilestone->projectId) && $currentMilestone->projectId == $project['id']) || session('currentProject') == $project['id']"
                >
                    {{ $project['name'] }}
                </x-global::forms.select.option>
            @endif
        @endforeach
    </x-global::forms.select>


    <x-global::forms.select 
        name="status"
        id="status-select"
        labelText="{{ __('label.todo_status') }}"
        :data-placeholder="isset($statusLabels[$currentMilestone->status]) ? $statusLabels[$currentMilestone->status]['name'] : ''"
    >
        @foreach ($statusLabels as $key => $label)
                <x-global::forms.select.option
                    :value="$key"
                    :selected="$currentMilestone->status == $key"
                >
                    {{ $label["name"] }}
                </x-global::forms.select.option>
        @endforeach
    </x-global::forms.select>


    <x-global::forms.select 
        name="dependentMilestone"
        id="dependentMilestone"
        labelText="{{ __('label.dependent_on') }}"
    >
        <x-global::forms.select.option :value="''">
            {{  __('label.no_dependency') }}
        </x-global::forms.select.option>
        @foreach ($milestones as $milestoneRow)
            @if ($milestoneRow->id !== $currentMilestone->id)
                <x-global::forms.select.option
                    :value="$milestoneRow->id"
                    :selected="$currentMilestone->milestoneid == $milestoneRow->id"
                >
                    {{ $milestoneRow->headline }}
                </x-global::forms.select.option>
            @endif
        @endforeach
    </x-global::forms.select>


    <x-global::forms.select 
        name="editorId"
        id="editorId"
        :data-placeholder="__('input.placeholders.filter_by_user')"
        :label-text="__('label.owner')"
    >
        <x-global::forms.select.option :value="''">
            {{ __('dropdown.not_assigned') }}
        </x-global::forms.select.option>
        @foreach ($users as $userRow)
            <x-global::forms.select.option
                :value="$userRow['id']"
                :selected="$currentMilestone->editorId == $userRow['id']"
            >
                {!! $userRow['firstname'] !!} {!! $userRow['lastname'] !!}
            </x-global::forms.select.option>
        @endforeach
    </x-global::forms.select>

    <x-global::forms.text-input 
        type="text" 
        name="tags" 
        value="{!! $currentMilestone->tags !!}" 
        placeholder="{!! $tpl->__('input.placeholders.pick_a_color') !!}" 
        labelText="{!! $tpl->__('label.color') !!}" 
        autocomplete="off" 
        {{-- class="simpleColorPicker" --}}
        type="color"
    />

    <x-global::forms.text-input 
        type="text" 
        name="editFrom" 
        id="milestoneEditFrom" 
        value="{!! format($currentMilestone->editFrom)->date() !!}" 
        placeholder="{!! $tpl->__('language.dateformat') !!}" 
        labelText="{!! $tpl->__('label.planned_start_date') !!}" 
        autocomplete="off" 
    />

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
    {{-- @include("comments::includes.generalComment", ["formUrl" => BASE_URL . "/tickets/editMilestone/" . $currentMilestone->id]) --}}
    <x-comments::list :module="'tickets'" :statusUpdates="'false'" :moduleId="$currentMilestone->id" />

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


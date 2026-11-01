@php
    $currentMilestone = $milestone ?? null;
    $milestones = $milestones ?? [];
    $statusLabels = $statusLabels ?? [];
@endphp

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="{{ BASE_URL }}/tickets/roadmap?showMilestoneModal={{ $currentMilestone->id }}";
        }
    }
</script>

<x-global::actions.modal-header>
    <x-slot:title>{!! __('headline.milestone') !!}</x-slot:title>
    @if (isset($currentMilestone->id) && $currentMilestone->id != '')
        @can('tickets.delete', $currentMilestone->projectId)
        <x-slot:actions>
            <li class="nav-header">{!! __('headline.milestone') !!}</li>
            <li><a href="#/tickets/delMilestone/{{ $currentMilestone->id }}" class="delete" data-lt-permitted><i class="fa fa-trash-can"></i> {{ __('links.delete') }}</a></li>
        </x-slot:actions>
        @endcan
    @endif
</x-global::actions.modal-header>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/tickets/editMilestone/{{ $currentMilestone->id }}" style="min-width: 250px;">

    <x-global::forms.field for="milestoneHeadline" :label="__('label.milestone_title')">
        <x-global::forms.text-input width="full" name="headline" id="milestoneHeadline" value="{{ $currentMilestone->headline }}" placeholder="{{ __('label.milestone_title') }}" />
    </x-global::forms.field>

    <x-global::forms.field for="milestoneProject" :label="__('label.project')">
        <x-global::forms.select width="full" name="projectId" id="milestoneProject">
            @foreach ($allAssignedprojects as $project)
                @if (empty($project['type']) || $project['type'] == 'project')
                <option value="{{ $project['id'] }}"
                    @if (!empty($currentMilestone->projectId) && $currentMilestone->projectId == $project['id'])
                        selected
                    @elseif (session('currentProject') == $project['id'])
                        selected
                    @endif
                >{{ $project['name'] }}</option>
                @endif
            @endforeach
        </x-global::forms.select>
    </x-global::forms.field>

    <x-global::forms.field for="status-select" :label="__('label.todo_status')">
        <x-global::forms.select width="full" id="status-select" name="status"
                data-placeholder="{{ isset($statusLabels[$currentMilestone->status]) ? $statusLabels[$currentMilestone->status]['name'] : '' }}">

            @foreach ($statusLabels as $key => $label)
                <option value="{{ $key }}"
                    @if ($currentMilestone->status == $key) selected='selected' @endif
                >{{ $label['name'] }}</option>
            @endforeach
        </x-global::forms.select>
    </x-global::forms.field>

    <x-global::forms.field for="milestoneDependency" :label="__('label.dependent_on')">
        <x-global::forms.select width="full" name="dependentMilestone" id="milestoneDependency">
            <option value="">{!! __('label.no_dependency') !!}</option>
            @foreach ($milestones as $milestoneRow)
                @if ($milestoneRow->id !== $currentMilestone->id)
                    <option value="{{ $milestoneRow->id }}"
                        @if ($currentMilestone->milestoneid == $milestoneRow->id) selected='selected' @endif
                    >{{ $milestoneRow->headline }} </option>
                @endif
            @endforeach
        </x-global::forms.select>
    </x-global::forms.field>

    <x-global::forms.field for="milestoneOwner" :label="__('label.owner')">
        <x-global::forms.select width="full" data-placeholder="{{ __('input.placeholders.filter_by_user') }}"
                name="editorId" id="milestoneOwner" class="user-select">
            <option value="">{!! __('dropdown.not_assigned') !!}</option>
            @foreach ($users as $userRow)
                <option value="{{ $userRow['id'] }}"
                    @if ($currentMilestone->editorId == $userRow['id']) selected='selected' @endif
                >{{ $userRow['firstname'] }} {{ $userRow['lastname'] }}</option>
            @endforeach
        </x-global::forms.select>
    </x-global::forms.field>

    <x-global::forms.field for="milestoneColor" :label="__('label.color')">
        <input type="text" name="tags" id="milestoneColor" autocomplete="off" value="{{ $currentMilestone->tags }}" placeholder="{{ __('input.placeholders.pick_a_color') }}" class="field-width-full simpleColorPicker"/>
    </x-global::forms.field>

    <x-global::forms.field for="milestoneEditFrom" :label="__('label.planned_start_date')">
        <input type="text" name="editFrom" class="field-width-sm" autocomplete="off" value="{{ format($currentMilestone->editFrom)->date() }}" placeholder="{{ __('language.dateformat') }}" id="milestoneEditFrom" />
    </x-global::forms.field>

    <x-global::forms.field for="milestoneEditTo" :label="__('label.planned_end_date')">
        <input type="text" name="editTo" class="field-width-sm" autocomplete="off" value="{{ format($currentMilestone->editTo)->date() }}"  placeholder="{{ __('language.dateformat') }}" id="milestoneEditTo" />
    </x-global::forms.field>

    <x-global::forms.field for="milestoneOutcome" :label="__('label.outcome_impact')">
        <textarea name="outcomeImpact" id="milestoneOutcome" rows="3" class="tw-w-full"
                  placeholder="{{ __('input.placeholders.outcome_impact') }}">{{ $currentMilestone->outcomeImpact }}</textarea>
    </x-global::forms.field>

    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" />
        </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
    </div>

</form>

    @if (isset($currentMilestone->id) && $currentMilestone->id !== '')
    <br />
    <input type="hidden" name="comment" value="1" />

        @include('comments::submodules.generalComment', ['formUrl' => '/tickets/editMilestone/'.$currentMilestone->id, 'commentProjectId' => $currentMilestone->projectId ?? null])
    @endif

<script type="text/javascript">
    jQuery(document).ready(function(){

        @if (isset($_GET['closeModal']))
            jQuery.nmTop().close();
            return;
        @endif

        leantime.ticketsController.initSimpleColorPicker();
        leantime.ticketsController.initMilestoneDates();

        @cannot(empty($currentMilestone->id) ? 'tickets.create' : 'tickets.edit', $currentMilestone->projectId ?? null)
            leantime.authController.makeInputReadonly(".nyroModalCont");
            @can('comments.create', $currentMilestone->projectId ?? null)
                leantime.commentsController.enableCommenterForms();
            @endcan
        @endcannot


    })
</script>

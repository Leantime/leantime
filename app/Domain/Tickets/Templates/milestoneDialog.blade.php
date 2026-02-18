@php
    $currentMilestone = $tpl->get('milestone');
    $milestones = $tpl->get('milestones');
    $statusLabels = $tpl->get('statusLabels');
@endphp

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="{{ BASE_URL }}/tickets/roadmap?showMilestoneModal={{ $currentMilestone->id }}";
        }
    }
</script>

<div class="modal-icons">
    @if(isset($currentMilestone->id) && $currentMilestone->id != '')
        <a href="#/tickets/delMilestone/{{ $currentMilestone->id }}" class="danger" data-tippy-content="Delete"><i class='fa fa-trash-can'></i></a>
    @endif
</div>

<h4 class="widgettitle title-light">{!! __('headline.milestone') !!}</h4>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/tickets/editMilestone/{{ $currentMilestone->id }}" style="min-width: 250px;">

    <label>{{ __('label.milestone_title') }}</label>
    <x-global::forms.input name="headline" value="{{ e($currentMilestone->headline) }}" placeholder="{{ __('label.milestone_title') }}" /><br />

    <label class="control-label">{{ __('label.project') }}</label>
    <x-global::forms.select name="projectId" class="tw:w-full">
        @foreach($allAssignedprojects as $project)
            @if(empty($project['type']) || $project['type'] == 'project')
                <option value="{{ $project['id'] }}"
                    @if(!empty($currentMilestone->projectId) && $currentMilestone->projectId == $project['id'])
                        selected
                    @elseif(session('currentProject') == $project['id'])
                        selected
                    @endif
                >{{ e($project['name']) }}</option>
            @endif
        @endforeach
    </x-global::forms.select>

    <label>{{ __('label.todo_status') }}</label>
    <x-global::forms.select id="status-select" name="status"
            data-placeholder="{{ isset($statusLabels[$currentMilestone->status]) ? $statusLabels[$currentMilestone->status]['name'] : '' }}">
        @foreach($statusLabels as $key => $label)
            <option value="{{ $key }}"
                {{ $currentMilestone->status == $key ? "selected='selected'" : '' }}
            >{{ e($label['name']) }}</option>
        @endforeach
    </x-global::forms.select>

    <label>{{ __('label.dependent_on') }}</label>
    <x-global::forms.select name="dependentMilestone">
        <option value="">{{ __('label.no_dependency') }}</option>
        @foreach($tpl->get('milestones') as $milestoneRow)
            @if($milestoneRow->id !== $currentMilestone->id)
                <option value="{{ $milestoneRow->id }}"
                    {{ $currentMilestone->milestoneid == $milestoneRow->id ? "selected='selected'" : '' }}
                >{{ e($milestoneRow->headline) }}</option>
            @endif
        @endforeach
    </x-global::forms.select>

    <label>{{ __('label.owner') }}</label>
    <select data-placeholder="{{ __('input.placeholders.filter_by_user') }}"
            name="editorId" class="user-select span11">
        <option value="">{{ __('dropdown.not_assigned') }}</option>
        @foreach($tpl->get('users') as $userRow)
            <option value="{{ $userRow['id'] }}"
                {{ $currentMilestone->editorId == $userRow['id'] ? "selected='selected'" : '' }}
            >{{ e($userRow['firstname']) }} {{ e($userRow['lastname']) }}</option>
        @endforeach
    </select>

    <label>{{ __('label.color') }}</label>
    <x-global::forms.input name="tags" autocomplete="off" value="{{ $currentMilestone->tags }}" placeholder="{{ __('input.placeholders.pick_a_color') }}" class="simpleColorPicker" /><br />

    <label>{{ __('label.planned_start_date') }}</label>
    <x-global::forms.date name="editFrom" id="milestoneEditFrom" value="{{ format($currentMilestone->editFrom)->date() }}" placeholder="{{ __('language.dateformat') }}" /><br />

    <label>{{ __('label.planned_end_date') }}</label>
    <x-global::forms.date name="editTo" id="milestoneEditTo" value="{{ format($currentMilestone->editTo)->date() }}" placeholder="{{ __('language.dateformat') }}" /><br />

    <div class="tw:flex tw:justify-between tw:items-start">
        <div>
            <x-global::button submit type="primary">{{ __('buttons.save') }}</x-global::button>
        </div>
        <div class="tw:text-right padding-top-sm">
        </div>
    </div>
</form>

@if(isset($currentMilestone->id) && $currentMilestone->id !== '')
    <br />
    <input type="hidden" name="comment" value="1" />
    @php
        $tpl->assign('formUrl', '/tickets/editMilestone/' . $currentMilestone->id . '');
        $tpl->displaySubmodule('comments-generalComment');
    @endphp
@endif

<script type="text/javascript">
    jQuery(document).ready(function(){

        leantime.ticketsController.initSimpleColorPicker();
        leantime.ticketsController.initMilestoneDates();

        @if(!$login::userIsAtLeast($roles::$editor))
            leantime.authController.makeInputReadonly("#global-modal-content");
        @endif

        @if($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
        @endif

    })
</script>

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

<x-globals::actions.modal mode="content" :title="__('headline.milestone')">

    {!! $tpl->displayNotification() !!}

    <form class="formModal" method="post" action="{{ BASE_URL }}/tickets/editMilestone/{{ $currentMilestone->id }}" style="min-width: 250px;">

        <x-globals::forms.form-field label-text="{{ __('label.milestone_title') }}" name="headline">
            <x-globals::forms.input name="headline" value="{{ e($currentMilestone->headline) }}" placeholder="{{ __('label.milestone_title') }}" />
        </x-globals::forms.form-field>

        <x-globals::forms.form-field label-text="{{ __('label.project') }}" name="projectId">
            <x-globals::forms.select name="projectId" class="tw:w-full">
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
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        <x-globals::forms.form-field label-text="{{ __('label.todo_status') }}" name="status">
            <x-globals::forms.select id="status-select" name="status"
                    data-placeholder="{{ isset($statusLabels[$currentMilestone->status]) ? $statusLabels[$currentMilestone->status]['name'] : '' }}">
                @foreach($statusLabels as $key => $label)
                    <option value="{{ $key }}"
                        {{ $currentMilestone->status == $key ? "selected='selected'" : '' }}
                    >{{ e($label['name']) }}</option>
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        <x-globals::forms.form-field label-text="{{ __('label.dependent_on') }}" name="dependentMilestone">
            <x-globals::forms.select name="dependentMilestone">
                <option value="">{{ __('label.no_dependency') }}</option>
                @foreach($tpl->get('milestones') as $milestoneRow)
                    @if($milestoneRow->id !== $currentMilestone->id)
                        <option value="{{ $milestoneRow->id }}"
                            {{ $currentMilestone->milestoneid == $milestoneRow->id ? "selected='selected'" : '' }}
                        >{{ e($milestoneRow->headline) }}</option>
                    @endif
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        <x-globals::forms.form-field label-text="{{ __('label.owner') }}" name="editorId">
            <x-globals::forms.select :bare="true" data-placeholder="{{ __('input.placeholders.filter_by_user') }}"
                    name="editorId" class="user-select span11">
                <option value="">{{ __('dropdown.not_assigned') }}</option>
                @foreach($tpl->get('users') as $userRow)
                    <option value="{{ $userRow['id'] }}"
                        {{ $currentMilestone->editorId == $userRow['id'] ? "selected='selected'" : '' }}
                    >{{ e($userRow['firstname']) }} {{ e($userRow['lastname']) }}</option>
                @endforeach
            </x-globals::forms.select>
        </x-globals::forms.form-field>

        <x-globals::forms.form-field label-text="{{ __('label.color') }}" name="tags">
            <x-globals::forms.input :bare="true" type="text" name="tags" autocomplete="off" value="{{ $currentMilestone->tags }}" placeholder="{{ __('input.placeholders.pick_a_color') }}" class="simpleColorPicker" />
        </x-globals::forms.form-field>

        <x-globals::forms.form-field label-text="{{ __('label.planned_start_date') }}" name="editFrom">
            <x-globals::forms.date name="editFrom" id="milestoneEditFrom" value="{{ format($currentMilestone->editFrom)->date() }}" placeholder="{{ __('language.dateformat') }}" />
        </x-globals::forms.form-field>

        <x-globals::forms.form-field label-text="{{ __('label.planned_end_date') }}" name="editTo">
            <x-globals::forms.date name="editTo" id="milestoneEditTo" value="{{ format($currentMilestone->editTo)->date() }}" placeholder="{{ __('language.dateformat') }}" />
        </x-globals::forms.form-field>

        <div class="tw:flex tw:justify-between tw:items-center">
            <div>
                <x-globals::forms.button submit type="primary">{{ __('buttons.save') }}</x-globals::forms.button>
            </div>
            @if(isset($currentMilestone->id) && $currentMilestone->id != '')
                <div>
                    <a href="#/tickets/delMilestone/{{ $currentMilestone->id }}" class="btn btn-danger btn-sm">
                        <x-global::elements.icon name="delete" /> {{ __('buttons.delete') }}
                    </a>
                </div>
            @endif
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

</x-globals::actions.modal>

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

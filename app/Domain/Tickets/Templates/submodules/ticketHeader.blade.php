@php
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Sprints\Models\Sprints;

    $currentUrlPath = BASE_URL . '/' . str_replace('.', '/', Frontcontroller::getCurrentRoute());
    $currentSprintId = $tpl->get('currentSprint');
    $searchCriteria = $tpl->get('searchCriteria');
    $searchSprint = $searchCriteria['sprint'] ?? '';
    $sprints = $tpl->get('sprints');

    $sprint = false;
    $currentSprintId = $currentSprintId == '' ? 'all' : $currentSprintId;

    if ($currentSprintId == 'all') {
        $sprint = new Sprints;
        $sprint->id = 'all';
        $sprint->name = $tpl->__('links.all_todos');
    }

    if ($currentSprintId == 'backlog') {
        $sprint = new Sprints;
        $sprint->id = 'backlog';
        $sprint->name = $tpl->__('links.backlog');
    }

    if (is_array($tpl->get('sprints'))) {
        foreach ($tpl->get('sprints') as $sprintRow) {
            if ($sprintRow->id == $currentSprintId) {
                $sprint = $sprintRow;
                break;
            }
        }
    }
@endphp

@dispatchEvent('beforePageHeaderOpen')

<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')

    <div class="pageicon">
        <x-global::elements.icon name="push_pin" />
    </div>
    <div class="pagetitle">
        <h5>{!! e(session('currentProjectClient') ?? '' . ' // ' . session('currentProjectName') ?? '') !!}</h5>

        @if(
            ($tpl->get('currentSprint') !== false)
            && ($tpl->get('currentSprint') !== null)
            && count($tpl->get('sprints')) > 0
            && $currentSprintId != 'all'
            && $currentSprintId != 'backlog'
            && $login::userIsAtLeast($roles::$editor)
        )
            <x-globals::actions.dropdown-menu container-class="headerEditDropdown">
                <li><a href="#/sprints/editSprint/{{ $tpl->get('currentSprint') }}">{{ __('link.edit_sprint') }}</a></li>
                <li><a href="#/sprints/delSprint/{{ $tpl->get('currentSprint') }}" class="delete">{{ __('links.delete_sprint') }}</a></li>
            </x-globals::actions.dropdown-menu>
        @endif

        <h1>
            {{ __('headlines.todos') }}
            //
            <x-globals::actions.dropdown-menu variant="link" trailing-visual="arrow_drop_down" trigger-class="header-title-dropdown" align="end">
                <x-slot:label>
                    @if($sprint !== false)
                        {{ e($sprint->name) }}
                    @else
                        {{ __('label.select_sprint') }}
                    @endif
                </x-slot:label>
                    <li><a class="wikiModal inlineEdit" href="#/sprints/editSprint/"><x-global::elements.icon name="add" /> {{ __('links.create_sprint_no_icon') }}</a></li>
                    <li class="nav-header border"></li>
                    <li>
                        <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val('all'); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')">{{ __('links.all_todos') }}</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val('backlog'); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')">{{ __('links.backlog') }}</a>
                    </li>
                    @foreach($tpl->get('sprints') as $sprintRow)
                        <li>
                            <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val({{ $sprintRow->id }}); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')">{{ e($sprintRow->name) }}<br /><small>{{ sprintf(__('label.date_from_date_to'), format($sprintRow->startDate)->date(), format($sprintRow->endDate)->date()) }}</small></a>
                        </li>
                    @endforeach
            </x-globals::actions.dropdown-menu>
        </h1>
        <input type="hidden" name="sprintSelect" id="sprintSelect" value="{{ $currentSprintId }}" />
    </div>

    @dispatchEvent('beforePageHeaderClose')
</div>

@dispatchEvent('afterPageHeaderClose')

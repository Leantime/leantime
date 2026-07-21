@php
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Sprints\Models\Sprints;

    $currentUrlPath = BASE_URL . '/' . str_replace('.', '/', Frontcontroller::getCurrentRoute());

    $currentSprintId = $currentSprint;
    $searchSprint = $searchCriteria['sprint'] ?? '';

    $sprint = false;

    $currentSprintId = $currentSprintId == '' ? 'all' : $currentSprintId;
    if ($currentSprintId == 'all') {
        $sprint = new Sprints;
        $sprint->id = 'all';
        $sprint->name = __('links.all_todos');
    }

    if ($currentSprintId == 'backlog') {
        $sprint = new Sprints;
        $sprint->id = 'backlog';
        $sprint->name = __('links.backlog');
    }

    if (is_array($sprints)) {
        foreach ($sprints as $sprintRow) {
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
        <span class="fa fa-fw fa-thumb-tack"></span>
    </div>
    <div class="pagetitle">
        <h5>{{ session('currentProjectClient') ?? '' . ' // ' . session('currentProjectName') ?? '' }}</h5>

        @if (
            ($currentSprint !== false)
                && ($currentSprint !== null)
                && count($sprints) > 0
                && $currentSprintId != 'all'
                && $currentSprintId != 'backlog'
        )
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    {{-- Inherited (program-owned) sprints are managed at the program level, never edited
                         or deleted from a child project. The service IDOR-fences this server-side too.
                         $sprint can be false (stale/deleted currentSprint), so guard the object access. --}}
                    @if ($login::userIsAtLeast($roles::$editor) && (! is_object($sprint) || empty($sprint->isInherited)))
                        <li><a href="#/sprints/editSprint/{{ $currentSprint }}">{!! __('link.edit_sprint') !!}</a></li>
                        <li><a href="#/sprints/delSprint/{{ $currentSprint }}" class="delete">{!! __('links.delete_sprint') !!}</a></li>
                    @endif
                </ul>
            </span>
        @endif

        {{-- Migrated to the shared subject switcher (was a hand-rolled
             header-title-dropdown). The sprint menu items stay here — they're
             domain-specific — but the "To-Dos // <current> ▾" chrome is now the
             component. Zero visual change. --}}
        <x-global::subjectSwitcher
            :parent="__('headlines.todos')"
            :current="$sprint !== false ? $sprint->name : __('label.select_sprint')">
            @isset($boardSummary)
                <x-slot:subline>{{ sprintf(__('label.board_task_count'), $boardSummary->total) }}@if ($boardSummary->unassigned > 0) · {{ sprintf(__('label.board_unassigned'), $boardSummary->unassigned) }}@endif @if ($boardSummary->dueThisWeek > 0) · {{ sprintf(__('label.board_due_this_week'), $boardSummary->dueThisWeek) }}@endif @if ($boardSummary->lastUpdated !== null) · {{ sprintf(__('label.board_updated'), $boardSummary->lastUpdated->setToUserTimezone()->diffForHumans()) }}@endif</x-slot:subline>
            @endisset
            <li><a class="wikiModal inlineEdit" href="#/sprints/editSprint/"><i class="fa-solid fa-plus"></i> {!! __('links.create_sprint_no_icon') !!}</a></li>
            <li class='nav-header border'></li>
            <li>
                <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val('all'); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')">{!! __('links.all_todos') !!}</a>
            </li>
            <li>
                <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val('backlog'); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')">{!! __('links.backlog') !!}</a>
            </li>
            @foreach ($sprints as $sprintRow)
                <li>
                    <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val({{ $sprintRow->id }}); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')">{{ $tpl->escape($sprintRow->name) }}@if (! empty($sprintRow->isInherited)) <span class="label label-info">{{ __('label.program_sprint') }}</span>@endif<br /><small>{!! sprintf(__('label.date_from_date_to'), format($sprintRow->startDate)->date(), format($sprintRow->endDate)->date()) !!}</small></a>
                </li>
            @endforeach
        </x-global::subjectSwitcher>
        <input type="hidden" name="sprintSelect" id="sprintSelect" value="{{ $currentSprintId }}" />
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div><!--pageheader-->
@dispatchEvent('afterPageHeaderClose')

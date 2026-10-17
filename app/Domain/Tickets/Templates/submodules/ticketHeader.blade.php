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

{{-- Build "Client // Project" only from the parts that exist, so a
     missing client/project never leaves a stray " // ". (`.` binds
     tighter than `??`, so the old inline expression mis-grouped.) --}}
@php
    $headerParts = array_filter(
        [session('currentProjectClient'), session('currentProjectName')],
        fn ($part) => $part !== null && $part !== ''
    );
@endphp

<x-global::layout.page-header icon="fa fa-fw fa-thumb-tack" :eyebrow="implode(' / ', $headerParts)">
    <x-slot:switcher>
        <x-global::subjectSwitcher
            :parent="__('headlines.todos')"
            :current="$sprint !== false ? $sprint->name : __('dropdown.choose_sprint')">
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
                    <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val({{ $sprintRow->id }}); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')">{{ $tpl->escape($sprintRow->name) }}@if (! empty($sprintRow->isInherited)) <x-global::elements.badge tone="info">{{ __('label.program_sprint') }}</x-global::elements.badge>@endif<br /><small>{!! sprintf(__('label.date_from_date_to'), format($sprintRow->startDate)->date(), format($sprintRow->endDate)->date()) !!}</small></a>
                </li>
            @endforeach
        </x-global::subjectSwitcher>
    </x-slot:switcher>

    {{-- Right cluster: board stats + (for a real sprint view only) the sprint edit/delete ⋮ menu. --}}
    <x-slot:actions>
        @isset($boardSummary)
            @php
                // Board metrics as a one-line meta string; segments join with a
                // single " · " so it reads cleanly no matter which are present.
                $summaryParts = [sprintf(__('label.board_task_count'), $boardSummary->total)];
                if ($boardSummary->unassigned > 0) {
                    $summaryParts[] = sprintf(__('label.board_unassigned'), $boardSummary->unassigned);
                }
                if ($boardSummary->dueThisWeek > 0) {
                    $summaryParts[] = sprintf(__('label.board_due_this_week'), $boardSummary->dueThisWeek);
                }
                if ($boardSummary->lastUpdated !== null) {
                    $summaryParts[] = sprintf(__('label.board_updated'), $boardSummary->lastUpdated->setToUserTimezone()->diffForHumans());
                }
            @endphp
            <div class="pageheader-meta">{{ implode(' · ', $summaryParts) }}</div>
        @endisset
        @if (
            ($currentSprint !== false)
                && ($currentSprint !== null)
                && count($sprints) > 0
                && $currentSprintId != 'all'
                && $currentSprintId != 'backlog'
        )
            <x-global::actions.dropdown variant="header-menu">
                {{-- Inherited (program-owned) sprints are managed at the program level, never edited
                     or deleted from a child project. The service IDOR-fences this server-side too.
                     $sprint can be false (stale/deleted currentSprint), so guard the object access. --}}
                @if ($login::userIsAtLeast($roles::$editor) && (! is_object($sprint) || empty($sprint->isInherited)))
                    <li><a href="#/sprints/editSprint/{{ $currentSprint }}">{!! __('link.edit_sprint') !!}</a></li>
                    <li><a href="#/sprints/delSprint/{{ $currentSprint }}" class="delete">{!! __('links.delete_sprint') !!}</a></li>
                @endif

            </x-global::actions.dropdown>
        @endif
    </x-slot:actions>
</x-global::layout.page-header>
<input type="hidden" name="sprintSelect" id="sprintSelect" value="{{ $currentSprintId }}" />

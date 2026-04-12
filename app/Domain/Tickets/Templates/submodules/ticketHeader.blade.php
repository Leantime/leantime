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
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/sprints/editSprint/{{ $currentSprint }}">{!! __('link.edit_sprint') !!}</a></li>
                        <li><a href="#/sprints/delSprint/{{ $currentSprint }}" class="delete">{!! __('links.delete_sprint') !!}</a></li>
                    @endif
                </ul>
            </span>
        @endif

        <h1>
            {!! __('headlines.todos') !!}
            //
            <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    @if ($sprint !== false)
                        {{ $sprint->name }}
                    @else
                        {!! __('label.select_sprint') !!}
                    @endif
                    <i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu">
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
                            <a href="javascript:void(0);" onclick="jQuery('#sprintSelect').val({{ $sprintRow->id }}); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')">{{ $tpl->escape($sprintRow->name) }}<br /><small>{!! sprintf(__('label.date_from_date_to'), format($sprintRow->startDate)->date(), format($sprintRow->endDate)->date()) !!}</small></a>
                        </li>
                    @endforeach
                </ul>
            </span>

        </h1>
        <input type="hidden" name="sprintSelect" id="sprintSelect" value="{{ $currentSprintId }}" />
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div><!--pageheader-->
@dispatchEvent('afterPageHeaderClose')

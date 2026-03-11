@php
    $allCanvas = $tpl->get('allCanvas');
    $canvasTitle = '';
    $canvasLabels = $tpl->get('canvasLabels');

    // get canvas title
    foreach ($tpl->get('allCanvas') as $canvasRow) {
        if ($canvasRow['id'] == $tpl->get('currentCanvas')) {
            $canvasTitle = $canvasRow['title'];
            break;
        }
    }
@endphp

<x-globals::layout.page-header icon="lightbulb">
    <h5>{{ $tpl->escape((session('currentProjectClient') ?? '') . ' // ' . session('currentProjectName')) }}</h5>
    @if (count($allCanvas) > 0)
        <x-globals::actions.dropdown-menu container-class="headerEditDropdown" position="left">
            @if ($login::userIsAtLeast($roles::$editor))
                <li><a href="#/ideas/boardDialog/{{ $tpl->get('currentCanvas') }}">{!! $tpl->__('links.icon.edit') !!}</a></li>
                <li><a href="{{ BASE_URL }}/ideas/delCanvas/{{ $tpl->get('currentCanvas') }}" class="delete">{!! $tpl->__('links.icon.delete') !!}</a></li>
            @endif
        </x-globals::actions.dropdown-menu>
    @endif
    <h1>{{ $tpl->__('headlines.ideas') }}
        //
        @if (count($allCanvas) > 0)
            <x-globals::actions.dropdown-menu variant="link" trailing-visual="arrow_drop_down" :label="$tpl->escape($canvasTitle)" trigger-class="header-title-dropdown">
                @if ($login::userIsAtLeast($roles::$editor))
                    <li><a href="#/ideas/boardDialog">{!! $tpl->__('links.icon.create_new_board') !!}</a></li>
                @endif
                <li class="border"></li>
                @foreach ($tpl->get('allCanvas') as $canvasRow)
                    <li><a href="{{ BASE_URL }}/ideas/showBoards/{{ $canvasRow['id'] }}">{{ $tpl->escape($canvasRow['title']) }}</a></li>
                @endforeach
            </x-globals::actions.dropdown-menu>
        @endif
    </h1>
</x-globals::layout.page-header>

<div class="maincontent">
    <div class="maincontentinner tw:min-h-[350px]">
        {!! $tpl->displayNotification() !!}

        <div class="tw:flex tw:justify-between tw:items-center tw:flex-wrap tw:gap-2">
            <div>
                @if ($login::userIsAtLeast($roles::$editor))
                    @if (count($tpl->get('allCanvas')) > 0)
                        <x-globals::forms.button link="#/ideas/ideaDialog?type=idea" contentRole="primary" id="customersegment" leadingVisual="lightbulb">{{ $tpl->__('buttons.add_idea') }}</x-globals::forms.button>
                    @endif
                @endif
            </div>

            <div>
                <x-globals::actions.dropdown-menu variant="button" :label="__('label.idea_wall')" contentRole="default">
                    <li><a href="{{ BASE_URL }}/ideas/showBoards" class="active"><x-globals::elements.icon name="dashboard" /> {{ __('label.idea_wall') }}</a></li>
                    <li><a href="{{ BASE_URL }}/ideas/advancedBoards"><x-globals::elements.icon name="view_kanban" /> {{ __('label.idea_kanban') }}</a></li>
                </x-globals::actions.dropdown-menu>
            </div>
        </div>

        <div class="clearfix"></div>

        @if (count($tpl->get('allCanvas')) > 0)
            <div id="ideaMason" class="sortableTicketList tw:pt-2">

                @foreach ($tpl->get('canvasItems') as $row)
                    <div class="ticketBox" id="item_{{ $row['id'] }}" data-value="{{ $row['id'] }}">

                                @if ($login::userIsAtLeast($roles::$editor))
                                    <x-globals::actions.dropdown-menu class="tw:float-right">
                                        <li><a href="#/ideas/ideaDialog/{{ $row['id'] }}" data="item_{{ $row['id'] }}"> {{ $tpl->__('links.edit_canvas_item') }}</a></li>
                                        <li><a href="#/ideas/delCanvasItem/{{ $row['id'] }}" class="delete" data="item_{{ $row['id'] }}"> {{ $tpl->__('links.delete_canvas_item') }}</a></li>
                                    </x-globals::actions.dropdown-menu>
                                @endif

                                <h4><a href="#/ideas/ideaDialog/{{ $row['id'] }}"
                                       data="item_{{ $row['id'] }}">{{ $tpl->escape($row['description']) }}</a></h4>

                                <div class="mainIdeaContent">
                                    <div class="kanbanCardContent">
                                        <div class="kanbanContent tw:mb-5" style="max-height:none;">
                                            {!! $tpl->escapeMinimal($row['data']) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="clearfix tw:pb-2"></div>

                                {{-- Status chip — converted from deprecated actions.chip; saves via HTMX PATCH to /api/ideas --}}
                                @php
                                    $ideaHxVals = json_encode(['id' => (string) $row['id']]);
                                @endphp
                                <x-globals::forms.select
                                    variant="chip"
                                    name="box"
                                    :id="'status-chip-' . $row['id']"
                                    hx-patch="{{ BASE_URL }}/api/ideas"
                                    hx-trigger="change"
                                    hx-swap="none"
                                    hx-vals="{{ $ideaHxVals }}"
                                >
                                    @foreach ($canvasLabels as $key => $statusRow)
                                        @php
                                            $statusClass = $statusRow['class'] ?? '';
                                            $isHex = str_starts_with((string) $statusClass, '#');
                                            $chipState = $isHex ? 'state-default' : $statusClass;
                                            $chipStyle = $isHex ? 'background:' . $statusClass . ';' : '';
                                            $chipHtml = '<span class="chip-badge ' . $chipState . '"' . ($chipStyle ? ' style="' . $chipStyle . '"' : '') . '>' . e($statusRow['name']) . '</span>';
                                        @endphp
                                        <option value="{{ $key }}"
                                            {{ (string) $row['box'] === (string) $key ? 'selected' : '' }}
                                            data-chip-html="{{ $chipHtml }}"
                                        >{{ $statusRow['name'] }}</option>
                                    @endforeach
                                </x-globals::forms.select>


                                <x-globals::actions.user-select
                                    :entityId="$row['id']"
                                    :assignedUserId="$row['author']"
                                    :assignedName="$row['authorFirstname']"
                                    :users="$tpl->get('users')"
                                    :showNameLabel="false"
                                    :showArrowIcon="false"
                                    :showUnassign="false"
                                    dropdownClasses="right lastDropdown dropRight"
                                />

                                <div class="tw:float-right tw:mr-2">
                                    <a href="#/ideas/ideaDialog/{{ $row['id'] }}"
                                       data="item_{{ $row['id'] }}"
                                        {!! $row['commentCount'] == 0 ? 'style="color: grey;"' : '' !!}>
                                        <x-globals::elements.icon name="forum" /></a> <small>{{ $row['commentCount'] }}</small>
                                </div>

                        @if ($row['milestoneHeadline'] != '')
                            <br/>
                            <div hx-trigger="load"
                                 hx-indicator=".htmx-indicator"
                                 hx-target="this"
                                 hx-swap="innerHTML"
                                 hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row['milestoneId'] }}"
                                 aria-live="polite">
                                <div class="htmx-indicator" role="status">
                                    {{ $tpl->__('label.loading_milestone') }}
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach

            </div>
            @if (count($tpl->get('canvasItems')) == 0)
                <div class="center">
                    <div class="tw:w-[30%] svgContainer">
                        {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
                    </div>

                    <h3>{{ $tpl->__('headlines.have_an_idea') }}</h3><br />
                    {{ $tpl->__('subtitles.start_collecting_ideas') }}<br/><br/>
                </div>
            @endif
            <div class="clearfix"></div>

        @else
            <br/><br/>
            <div class="center">
                <div class="tw:w-[30%] svgContainer">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
                </div>

                <h3>{{ $tpl->__('headlines.have_an_idea') }}</h3><br />
                {{ $tpl->__('subtitles.start_collecting_ideas') }}<br/><br/>
                @if ($login::userIsAtLeast($roles::$editor))
                    <x-globals::forms.button link="javascript:void(0)" contentRole="primary" onclick="document.getElementById('addCanvas').showModal();">{!! $tpl->__('links.icon.create_new_board') !!}</x-globals::forms.button>
                @endif
            </div>

        @endif
        <!-- Modals -->

        <x-globals::actions.modal id="addCanvas" :title="$tpl->__('headlines.start_new_idea_board')">
            <form action="" method="post">
                <label>{{ $tpl->__('label.topic_idea_board') }}</label>
                <x-globals::forms.text-input name="canvastitle" placeholder="{{ $tpl->__('input.placeholders.name_for_idea_board') }}"
                       class="tw:w-[90%]" />
                <x-slot name="actions">
                    <x-globals::forms.button tag="button" contentRole="secondary" onclick="document.getElementById('addCanvas').close();">{{ $tpl->__('buttons.close') }}</x-globals::forms.button>
                    <x-globals::forms.button :submit="true" contentRole="secondary" name="newCanvas">{{ $tpl->__('buttons.create_board') }}</x-globals::forms.button>
                </x-slot>
            </form>
        </x-globals::actions.modal>

        <x-globals::actions.modal id="editCanvas" :title="$tpl->__('headlines.edit_board_name')">
            <form action="" method="post">
                <label>{{ $tpl->__('label.title_idea_board') }}</label>
                <x-globals::forms.text-input name="canvastitle" value="{{ $tpl->escape($canvasTitle) }}"
                       class="tw:w-[90%]" />
                <x-slot name="actions">
                    <x-globals::forms.button tag="button" contentRole="secondary" onclick="document.getElementById('editCanvas').close();">{{ $tpl->__('buttons.close') }}</x-globals::forms.button>
                    <x-globals::forms.button :submit="true" contentRole="secondary" name="editCanvas">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
                </x-slot>
            </form>
        </x-globals::actions.modal>

        <div class="clearfix"></div>

    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function () {

        leantime.ideasController.initMasonryWall();
        leantime.ideasController.initWallImageModals();

        @if ($login::userIsAtLeast($roles::$editor))
            leantime.ideasController.initUserDropdown();
        @else
            leantime.authController.makeInputReadonly(".maincontent");
        @endif

        @if (isset($_GET['showIdeaModal']))
            @php
                if ($_GET['showIdeaModal'] == '') {
                    $modalUrl = '&type=idea';
                } else {
                    $modalUrl = '/' . (int) $_GET['showIdeaModal'];
                }
            @endphp
            leantime.ideasController.openModalManually("{{ BASE_URL }}/ideas/ideaDialog{{ $modalUrl }}");
            window.history.pushState({}, document.title, '{{ BASE_URL }}/ideas/showBoards');
        @endif
    });

</script>

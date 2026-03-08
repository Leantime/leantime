@php
    $allCanvas = $tpl->get('allCanvas');
    $canvasLabels = $tpl->get('canvasLabels');
    $canvasTitle = '';

    // All states >0 (<1 is archive)
    $numberofColumns = count($tpl->get('canvasLabels'));
    $size = floor((100 / $numberofColumns) * 100) / 100;

    // get canvas title
    foreach ($tpl->get('allCanvas') as $canvasRow) {
        if ($canvasRow['id'] == $tpl->get('currentCanvas')) {
            $canvasTitle = $canvasRow['title'];
            break;
        }
    }
@endphp

<x-globals::layout.page-header icon="lightbulb">
    <h5>{{ $tpl->escape(session('currentProjectClient') . ' // ' . session('currentProjectName')) }}</h5>
    @if (count($allCanvas) > 0)
        <x-globals::actions.dropdown-menu container-class="headerEditDropdown" position="left">
            @if ($login::userIsAtLeast($roles::$editor))
                <li><a href="javascript:void(0)" onclick="document.getElementById('editCanvas').showModal();">{!! $tpl->__('links.icon.edit') !!}</a></li>
                <li><a href="{{ BASE_URL }}/ideas/delCanvas/{{ $tpl->get('currentCanvas') }}" class="delete">{!! $tpl->__('links.icon.delete') !!}</a></li>
            @endif
        </x-globals::actions.dropdown-menu>
    @endif
    <h1>{{ $tpl->__('headlines.idea_management') }}
        //
        @if (count($allCanvas) > 0)
            <x-globals::actions.dropdown-menu variant="link" trailing-visual="arrow_drop_down" :label="$tpl->escape($canvasTitle)" trigger-class="header-title-dropdown">
                @if ($login::userIsAtLeast($roles::$editor))
                    <li><a href="javascript:void(0)" onclick="document.getElementById('addCanvas').showModal();">{!! $tpl->__('links.icon.create_new_board') !!}</a></li>
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
    <div class="maincontentinner">
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
                <x-globals::actions.dropdown-menu variant="button" :label="__('label.idea_kanban')" contentRole="default">
                    <li><a href="{{ BASE_URL }}/ideas/showBoards"><x-globals::elements.icon name="dashboard" /> {{ __('label.idea_wall') }}</a></li>
                    <li><a href="{{ BASE_URL }}/ideas/advancedBoards" class="active"><x-globals::elements.icon name="view_kanban" /> {{ __('label.idea_kanban') }}</a></li>
                </x-globals::actions.dropdown-menu>
            </div>
        </div>

        @if (count($tpl->get('allCanvas')) > 0)
            <div id="sortableIdeaKanban" class="sortableTicketList tw:mt-4">

                <div class="row-fluid">

                    @foreach ($tpl->get('canvasLabels') as $key => $statusRow)
                    <div class="column" style="width:{{ $size }}%;">

                        <x-globals::elements.section-title variant="primary">
                            @if ($login::userIsAtLeast($roles::$manager))
                                <a href="#/setting/editBoxLabel?module=idealabels&label={{ $key }}"
                                   class="editHeadline"><x-globals::elements.icon name="edit" /></a>
                            @endif
                            {{ $tpl->escape($statusRow['name']) }}
                        </x-globals::elements.section-title>

                        <div class="contentInner status_{{ $key }}">

                            @foreach ($tpl->get('canvasItems') as $row)
                                @if ($row['box'] == $key)
                                    <div class="ticketBox moveable tw:p-4" id="item_{{ $row['id'] }}">

                                        <div class="tw:flex tw:justify-between tw:items-start tw:gap-2 tw:mb-3">
                                            <h4 class="tw:m-0 tw:flex-1 tw:min-w-0"><a href="#/ideas/ideaDialog/{{ $row['id'] }}"
                                                   data="item_{{ $row['id'] }}">{{ $tpl->escape($row['description']) }}</a></h4>
                                            @if ($login::userIsAtLeast($roles::$editor))
                                                <x-globals::actions.dropdown-menu>
                                                    <li><a href="#/ideas/ideaDialog/{{ $row['id'] }}" data="item_{{ $row['id'] }}"> {{ $tpl->__('links.edit_canvas_item') }}</a></li>
                                                    <li><a href="#/ideas/delCanvasItem/{{ $row['id'] }}" class="delete" data="item_{{ $row['id'] }}"> {{ $tpl->__('links.delete_canvas_item') }}</a></li>
                                                </x-globals::actions.dropdown-menu>
                                            @endif
                                        </div>

                                        <div class="mainIdeaContent tw:mb-4">
                                            <div class="kanbanCardContent">
                                                <div class="kanbanContent">
                                                    {!! $tpl->escapeMinimal($row['data']) !!}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tw:flex tw:items-center tw:justify-between tw:pt-3" style="border-top: 1px solid var(--main-border-color);">
                                            <div class="tw:flex tw:items-center tw:gap-1">
                                                <a href="#/ideas/ideaDialog/{{ $row['id'] }}"
                                                    data="item_{{ $row['id'] }}"
                                                    class="tw:flex tw:items-center tw:gap-1"
                                                    {!! $row['commentCount'] == 0 ? 'style="color: grey;"' : '' !!}>
                                                    <x-globals::elements.icon name="forum" />
                                                    <small>{{ $row['commentCount'] }}</small>
                                                </a>
                                            </div>

                                            <x-globals::actions.user-select
                                                :entityId="$row['id']"
                                                :assignedUserId="$row['author']"
                                                :assignedName="$row['authorFirstname']"
                                                :users="$tpl->get('users')"
                                                :showNameLabel="false"
                                                :showArrowIcon="false"
                                                :showUnassign="false"
                                                dropdownClasses="lastDropdown dropRight"
                                            />
                                        </div>

                                        @if ($row['milestoneHeadline'] != '')
                                            <div class="tw:mt-2" hx-trigger="load"
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
                                @endif
                            @endforeach

                        </div>

                    </div>

                    @endforeach

                </div>
            </div>
            <div class="clearfix"></div>

        @else
            <br/><br/>
            <div class="center">
                <div class="tw:w-1/2 svgContainer">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
                </div>

                <br/><h4>{{ $tpl->__('headlines.have_an_idea') }}</h4><br/>
                {{ $tpl->__('subtitles.start_collecting_ideas') }}<br/><br/>
                @if ($login::userIsAtLeast($roles::$editor))
                    <x-globals::forms.button link="javascript:void(0);" contentRole="primary" onclick="document.getElementById('addCanvas').showModal();">{{ $tpl->__('buttons.start_new_idea_board') }}</x-globals::forms.button>
                @endif
            </div>

        @endif
        <!-- Modals -->

        <x-globals::actions.modal id="addCanvas" :title="$tpl->__('headlines.start_new_idea_board')">
            <form action="" method="post">
                <label>{{ $tpl->__('label.topic_idea_board') }}</label>
                <x-globals::forms.text-input name="canvastitle"
                       placeholder="{{ $tpl->__('input.placeholders.name_for_idea_board') }}"
                       class="tw:w-full" />
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
                       class="tw:w-full" />
                <x-slot name="actions">
                    <x-globals::forms.button tag="button" contentRole="secondary" onclick="document.getElementById('editCanvas').close();">{{ $tpl->__('buttons.close') }}</x-globals::forms.button>
                    <x-globals::forms.button :submit="true" contentRole="secondary" name="editCanvas">{{ $tpl->__('buttons.save') }}</x-globals::forms.button>
                </x-slot>
            </form>
        </x-globals::actions.modal>

    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function () {

        leantime.ideasController.setKanbanHeights();

        @if ($login::userIsAtLeast($roles::$editor))
        var ideaStatusList = [@foreach ($canvasLabels as $key => $statusRow)'{{ $key }}',@endforeach];
            leantime.ideasController.initIdeaKanban(ideaStatusList);
            leantime.ideasController.initUserDropdown();
        @else
            leantime.authController.makeInputReadonly(".maincontent");
        @endif

    });

</script>

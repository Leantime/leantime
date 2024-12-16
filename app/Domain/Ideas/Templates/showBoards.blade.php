@extends($layout)
@section('content')

    @php
        $canvasTitle = '';

        //get canvas title
        foreach ($allCanvas as $canvasRow) {
            if ($canvasRow->id == $currentCanvas) {
                $canvasTitle = $canvasRow->title;
                break;
            }
        }
    @endphp

    <div class="pageheader">
        <div class="pageicon"><i class="far fa-lightbulb"></i></div>
        <div class="pagetitle">
            <h5>{{ session('currentProjectClient') ?? '' . ' // ' . session('currentProjectName') }}</h5>
            @if (count($allCanvas) > 0)
                <span class="dropdown dropdownWrapper headerEditDropdown">
                    <x-global::content.context-menu>
                        @if ($login::userIsAtLeast($roles::$editor))
                            <x-global::actions.dropdown.item href="#/ideas/boardDialog/{{ $currentCanvas }}">
                                {!! __('links.icon.edit') !!}
                            </x-global::actions.dropdown.item>

                            <x-global::actions.dropdown.item href="{{ BASE_URL }}/ideas/delCanvas/{{ $currentCanvas }}"
                                class="delete">
                                {!! __('links.icon.delete') !!}
                            </x-global::actions.dropdown.item>
                        @endif
                    </x-global::content.context-menu>

                </span>
            @endif
            <h1>{!! __('headlines.ideas') !!}
                //
                @if (count($allCanvas) > 0)
                    <span class="dropdown dropdownWrapper">
                        <x-global::actions.dropdown class="header-title-dropdown canvasSelector" align="start"
                            contentRole="menu">
                            <x-slot:labelText>
                                {{ $canvasTitle }}&nbsp;<i class="fa fa-caret-down"></i>
                            </x-slot:labelText>

                            <x-slot:menu>
                                @if ($login::userIsAtLeast($roles::$editor))
                                    <x-global::actions.dropdown.item href="#/ideas/boardDialog">
                                        {!! __('links.icon.create_new_board') !!}
                                    </x-global::actions.dropdown.item>
                                @endif

                                <x-global::actions.dropdown.item variant="border"></x-global::actions.dropdown.item>

                                @foreach ($allCanvas as $canvasRow)
                                    <x-global::actions.dropdown.item
                                        href="{{ BASE_URL }}/ideas/showBoards/{{ $canvasRow->id }}">
                                        {{ $canvasRow->title }}
                                    </x-global::actions.dropdown.item>
                                @endforeach
                            </x-slot:menu>
                        </x-global::actions.dropdown>

                    </span>
                @endif
            </h1>
        </div>
    </div><!--pageheader-->

    <div class="maincontent">
        <div class="maincontentinner" id="ideaBoards">

            <div class="row">
                <div class="col-md-4">
                    @if ($login::userIsAtLeast($roles::$editor))
                        @if (count($allCanvas) > 0)
                            <a href="#/ideas/ideaDialog?type=idea" class="btn btn-primary" id="customersegment">
                                <span class="far fa-lightbulb"></span>{!! __('buttons.add_idea') !!}
                            </a>
                        @endif
                    @endif
                </div>

                <div class="col-md-4 center">
                </div>
                <div class="col-md-4">
                    <div class="pull-right">
                        <div class="btn-group viewDropDown">
                            <x-global::actions.dropdown align="start" contentRole="ghost">
                                <x-slot:labelText>
                                    {!! __('buttons.idea_wall') !!} {!! __('links.view') !!}
                                </x-slot:labelText>

                                <x-slot:menu>
                                    <!-- Dropdown Items -->
                                    <x-global::actions.dropdown.item href="{{ BASE_URL }}/ideas/showBoards"
                                        class="active">
                                        {!! __('buttons.idea_wall') !!}
                                    </x-global::actions.dropdown.item>

                                    <x-global::actions.dropdown.item href="{{ BASE_URL }}/ideas/advancedBoards">
                                        {!! __('buttons.idea_kanban') !!}
                                    </x-global::actions.dropdown.item>
                                </x-slot:menu>
                            </x-global::actions.dropdown>

                        </div>
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>
            @if (count($allCanvas) > 0)
                <div id="ideaMason" class="sortableTicketList flex flex-wrap" style="padding-top:10px;">
                    @foreach ($canvasItems as $row)
                        <div class="ticketBox ml-3" id="item_{{ $row->id }}" data-value="{{ $row->id }}">
                            <div class="row">
                                <div class="col-md-12">
                                    @if ($login::userIsAtLeast($roles::$editor))
                                        <div class="inlineDropDownContainer" style="float:right;">
                                            <x-global::content.context-menu>
                                                <!-- Dropdown Header -->
                                                <x-global::actions.dropdown.item variant="header">
                                                    {!! __('subtitles.edit') !!}
                                                </x-global::actions.dropdown.item>

                                                <!-- Edit Item -->
                                                <x-global::actions.dropdown.item
                                                    href="#/ideas/ideaDialog/{{ $row->id }}" :data="'item_' . $row->id">
                                                    {!! __('links.edit_canvas_item') !!}
                                                </x-global::actions.dropdown.item>

                                                <!-- Delete Item -->
                                                <x-global::actions.dropdown.item
                                                    href="#/ideas/delCanvasItem/{{ $row->id }}" class="delete"
                                                    :data="'item_' . $row->id">
                                                    {!! __('links.delete_canvas_item') !!}
                                                </x-global::actions.dropdown.item>
                                            </x-global::content.context-menu>

                                        </div>
                                    @endif

                                    <h4><a href="#/ideas/ideaDialog/{{ $row->id }}"
                                            data="item_{{ $row->id }}">{{ $row->description }}</a></h4>

                                    <div class="mainIdeaContent">
                                        <div class="kanbanCardContent">
                                            <div class="kanbanContent" style="margin-bottom: 20px">
                                                {!! $row->data !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="clearfix" style="padding-bottom: 8px;"></div>

                                    <div class="dropdown ticketDropdown statusDropdown show firstDropdown colorized">
                                        <x-global::actions.dropdown contentRole="link" position="bottom" align="start"
                                            class="f-left status {{ $canvasLabels[$row->box]['class'] }}">
                                            <!-- Dropdown Button -->
                                            <x-slot:labelText>
                                                <span class="text">{{ $canvasLabels[$row->box]['name'] }}</span>&nbsp;<i
                                                    class="fa fa-caret-down" aria-hidden="true"></i>
                                            </x-slot:labelText>

                                            <!-- Menu Slot -->
                                            <x-slot:menu>
                                                <!-- Dropdown Header -->
                                                <x-global::actions.dropdown.item variant="header-border">
                                                    {!! __('dropdown.choose_status') !!}
                                                </x-global::actions.dropdown.item>

                                                <!-- Dropdown Items -->
                                                @foreach ($canvasLabels as $key => $label)
                                                    <x-global::actions.dropdown.item href="javascript:void(0);"
                                                        class="{{ $label['class'] }}" :data-label="$label['name']" :data-value="$row->id . '_' . $key . '_' . $label['class']"
                                                        id="ticketStatusChange{{ $row->id }}{{ $key }}">
                                                        {{ $label['name'] }}
                                                    </x-global::actions.dropdown.item>
                                                @endforeach
                                            </x-slot:menu>
                                        </x-global::actions.dropdown>
                                    </div>

                                    <div
                                        class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                        <x-global::actions.dropdown contentRole="link" position="bottom" align="start"
                                            class="f-left">
                                            <!-- Dropdown Trigger -->
                                            <x-slot:labelText>
                                                <span class="text">
                                                    @if ($row->authorFirstname != '')
                                                        <span id='userImage{{ $row->id }}'>
                                                            <img src='{{ BASE_URL }}/api/users?profileImage={{ $row->author }}'
                                                                width='25' style='vertical-align: middle;' />
                                                        </span>
                                                        <span id='user{{ $row->id }}'></span>
                                                    @else
                                                        <span id='userImage{{ $row->id }}'>
                                                            <img src='{{ BASE_URL }}/api/users?profileImage=false'
                                                                width='25' style='vertical-align: middle;' />
                                                        </span>
                                                        <span id='user{{ $row->id }}'></span>
                                                    @endif
                                                </span>
                                            </x-slot:labelText>

                                            <!-- Menu Slot -->
                                            <x-slot:menu>
                                                <!-- Dropdown Header -->
                                                <x-global::actions.dropdown.item variant="header-border">
                                                    {!! __('dropdown.choose_user') !!}
                                                </x-global::actions.dropdown.item>

                                                <!-- Dropdown Items -->
                                                @foreach ($users as $user)
                                                    <x-global::actions.dropdown.item href="javascript:void(0);"
                                                        :data-label="sprintf(
                                                            __('text.full_name'),
                                                            $user['firstname'],
                                                            $user['lastname'],
                                                        )" :data-value="$row->id .
                                                            '_' .
                                                            $user['id'] .
                                                            '_' .
                                                            $user['profileId']"
                                                        id="userStatusChange{{ $row->id }}{{ $user['id'] }}">
                                                        <img src='{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}'
                                                            width='25'
                                                            style='vertical-align: middle; margin-right:5px;' />
                                                        {{ sprintf(__('text.full_name'), $user['firstname'], $user['lastname']) }}
                                                    </x-global::actions.dropdown.item>
                                                @endforeach
                                            </x-slot:menu>
                                        </x-global::actions.dropdown>

                                    </div>

                                    <div class="pull-right" style="margin-right:10px;">
                                        <a href="#/ideas/ideaDialog/{{ $row->id }}" class=""
                                            data="item_{{ $row->id }}"
                                            {{ $row->commentCount == 0 ? 'style="color: grey;"' : '' }}>
                                            <span class="fas fa-comments"></span>
                                        </a>
                                        <small>{{ $row->commentCount }}</small>
                                    </div>
                                </div>
                            </div>

                            @if ($row->milestoneHeadline != '')
                                <br />
                                <div hx-trigger="load" hx-indicator=".htmx-indicator"
                                    hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row->milestoneId }}">
                                    <div class="htmx-indicator">
                                        {!! __('label.loading_milestone') !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if (count($canvasItems) == 0)
                    <div class='center'>
                        <div style='width:30%' class='svgContainer'>
                            {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
                        </div>
                        <h3>{!! __('headlines.have_an_idea') !!}</h3><br />
                        {!! __('subtitles.start_collecting_ideas') !!}<br /><br />
                    </div>
                @endif
                <div class="clearfix"></div>
            @else
                <br /><br />
                <div class='center'>
                    <div style='width:30%' class='svgContainer'>
                        {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
                    </div>
                    <h3>{!! __('headlines.have_an_idea') !!}</h3><br />
                    {!! __('subtitles.start_collecting_ideas') !!}<br /><br />
                    @if ($login::userIsAtLeast($roles::$editor))
                        <a href="javascript:void(0)" class="addCanvasLink btn btn-primary">{!! __('links.icon.create_new_board') !!}</a>
                    @endif
                </div>
            @endif

            <!-- Modals -->
            <div class="modal fade bs-example-modal-lg" id="addCanvas">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="" method="post">
                            <div class="modal-header">
                            <x-global::forms.button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-hidden="true">
                                &times;
                            </x-global::forms.button>

                                <h4 class="modal-title">{!! __('headlines.start_new_idea_board') !!}</h4>
                            </div>
                            <div class="modal-body">
                                <x-global::forms.text-input
                                    type="text"
                                    name="canvastitle"
                                    placeholder="{{ __('input.placeholders.name_for_idea_board') }}"
                                    labelText="{{ __('label.topic_idea_board') }}"
                                    variant="title"
                                 />

                            </div>
                            <div class="modal-footer">
                                <x-global::forms.button type="button" content-role="default" data-dismiss="modal">
                                    {!! __('buttons.close') !!}
                                </x-global::forms.button>

                                <x-global::forms.button type="submit" content-role="default" name="newCanvas">
                                    {!! __('buttons.create_board') !!}
                                </x-global::forms.button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade bs-example-modal-lg" id="editCanvas">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="" method="post">
                            <div class="modal-header">
                            <x-global::forms.button
                                type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-hidden="true">
                                &times;
                            </x-global::forms.button>

                                <h4 class="modal-title">{!! __('headlines.edit_board_name') !!}</h4>
                            </div>
                            <div class="modal-body">
                            <x-global::forms.text-input
                                type="text"
                                name="canvastitle"
                                value="{{ $canvasTitle }}"
                                labelText="{{ __('label.title_idea_board') }}"
                                variant="title"
                            />

                            </div>
                            <div class="modal-footer">
                                <x-global::forms.button type="button" content-role="default" data-dismiss="modal">
                                    {!! __('buttons.close') !!}
                                </x-global::forms.button>

                                <x-global::forms.button type="submit" content-role="default" name="editCanvas">
                                    {!! __('buttons.save') !!}
                                </x-global::forms.button>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script type="text/javascript">
        jQuery(document).ready(function() {
            //new SlimSelect({ select: '#searchCanvas' });

            leantime.ideasController.initMasonryWall();
            leantime.ideasController.initBoardControlModal();
            leantime.ideasController.initWallImageModals();

            @if ($login::userIsAtLeast($roles::$editor))
            leantime.canvasController.initUserDropdown('ideas');
            leantime.canvasController.initStatusDropdown('ideas');
            @else
                leantime.authController.makeInputReadonly(".maincontentinner");
            @endif

            @if (isset($_GET['showIdeaModal']))
                @php
                    if ($_GET['showIdeaModal'] == '') {
                        $modalUrl = '&type=idea';
                    } else {
                        $modalUrl = '/' . (int) $_GET['showIdeaModal'];
                    }
                @endphp

                leantime.ideasController.openModalManually(
                    "{{ BASE_URL }}/ideas/ideaDialog{!! $modalUrl !!}");
                window.history.pushState({}, document.title, '{{ BASE_URL }}/ideas/showBoards');
            @endif
        });
    </script>

@endsection

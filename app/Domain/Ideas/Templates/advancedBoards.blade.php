@extends($layout)
@section('content')

    @php
        $canvasTitle = '';

        //All states >0 (<1 is archive)
        $numberofColumns = count($canvasLabels);
        $size = floor((100 / $numberofColumns) * 100) / 100;

        //get canvas title
        foreach ($allCanvas as $canvasRow) {
            if ($canvasRow->title == $currentCanvas) {
                $canvasTitle = $canvasRow->title;
                break;
            }
        }

    @endphp

    <div class="pageheader">
        <div class="pageicon"><i class="far fa-lightbulb"></i></div>
        <div class="pagetitle">
            <h5><?php $tpl->e(session('currentProjectClient') . ' // ' . session('currentProjectName')); ?></h5>
            <h5>{{ session('currentProjectClient') . ' // ' . session('currentProjectName') }}</h5>
            @if (count($allCanvas) > 0)
                <span class="dropdown dropdownWrapper headerEditDropdown">
                    @php
                        $labelText =
                            '<a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>';
                    @endphp

                    <x-global::content.context-menu :labelText="$labelText" align="start" contentRole="menu">
                        @if ($login::userIsAtLeast($roles::$editor))
                            <x-global::actions.dropdown.item href="javascript:void(0)" class="editCanvasLink">
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
            <h1>{!! __('headlines.idea_management') !!}
                //
                @if (count($allCanvas) > 0)
                    <span class="dropdown dropdownWrapper">
                        <x-global::actions.dropdown :labelText="html_entity_decode($labelText)" class="canvasSelector" align="start"
                            contentRole="ghost">
                            <x-slot:labelText>
                                <a href="javascript:void(0);" class="dropdown-toggle header-title-dropdown"
                                    data-toggle="dropdown" style="max-width:200px;">
                                    {{ $canvasTitle }}&nbsp;<i class="fa fa-caret-down"></i>
                                </a>
                            </x-slot:labelText>
                            <x-slot:menu>
                                @if ($login::userIsAtLeast($roles::$editor))
                                    <x-global::actions.dropdown.item href="javascript:void(0)" class="addCanvasLink">
                                        {!! __('links.icon.create_new_board') !!}
                                    </x-global::actions.dropdown.item>
                                @endif

                                <li class="border"></li>

                                @foreach ($allCanvas as $canvasRow)
                                    <x-global::actions.dropdown.item
                                        href="{{ BASE_URL }}/ideas/showBoards/{{ $canvasRow->title }}">
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
        <div class="maincontentinner">

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
                            <x-global::actions.dropdown class="btn btn-default dropdown-toggle" align="start"
                                contentRole="ghost">
                                <x-slot:labelText>
                                    {!! __('buttons.idea_kanban') !!} {!! __('links.view') !!}
                                </x-slot:labelText>

                                <x-slot:menu>
                                    <!-- Dropdown Items -->
                                    <x-global::actions.dropdown.item href="{{ BASE_URL }}/ideas/showBoards">
                                        {!! __('buttons.idea_wall') !!}
                                    </x-global::actions.dropdown.item>

                                    <x-global::actions.dropdown.item href="{{ BASE_URL }}/ideas/advancedBoards"
                                        class="active">
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
                <div id="sortableIdeaKanban" class="sortableTicketList">
                    <div class="row-fluid">
                        @foreach ($canvasLabels as $key => $statusRow)
                            <div class="column" style="width:{{ $size }}%;">
                                <h4 class="widgettitle title-primary">
                                    @if ($login::userIsAtLeast($roles::$manager))
                                        <a href="#/setting/editBoxLabel?module=idealabels&label={{ $key }}"
                                            class="editHeadline"><i class="fas fa-edit"></i></a>
                                    @endif
                                    {{ $statusRow['name'] }}
                                </h4>
                                <div class="contentInner status_{{ $key }}">
                                    @foreach ($canvasItems as $row)
                                        @if ($row->box == $key)
                                            <div class="ticketBox moveable" id="item_{{ $row->id }}">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        @if ($login::userIsAtLeast($roles::$editor))
                                                            <div class="inlineDropDownContainer" style="float:right;">
                                                                <x-global::content.context-menu>
                                                                    <x-global::actions.dropdown.item class="header">
                                                                        {!! __('subtitles.edit') !!}
                                                                    </x-global::actions.dropdown.item>
                                                                    <x-global::actions.dropdown.item
                                                                        href="#/ideas/ideaDialog/{{ $row->id }}"
                                                                        data="item_{{ $row->id }}">
                                                                        {!! __('links.edit_canvas_item') !!}
                                                                    </x-global::actions.dropdown.item>
                                                                    <x-global::actions.dropdown.item
                                                                        href="#/ideas/delCanvasItem/{{ $row->id }}"
                                                                        class="delete" data="item_{{ $row->id }}">
                                                                        {!! __('links.delete_canvas_item') !!}
                                                                    </x-global::actions.dropdown.item>
                                                                </x-global::content.context-menu>
                                                            </div>
                                                        @endif
                                                        <h4><a href="{{ BASE_URL }}/ideas/advancedBoards/#/ideas/ideaDialog/{{ $row->id }}"
                                                                class=""
                                                                data="item_{{ $row->id }}">{{ $row->description }}</a>
                                                        </h4>
                                                        <div class="mainIdeaContent">
                                                            <div class="kanbanCardContent">
                                                                <div class="kanbanContent" style="margin-bottom: 20px">
                                                                    {!! $row->data !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="clearfix" style="padding-bottom: 8px;"></div>
                                                        <div
                                                            class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                            @php
                                                                $userImageUrl =
                                                                    $row->authorFirstname != ''
                                                                        ? BASE_URL .
                                                                            '/api/users?profileImage=' .
                                                                            $row->author
                                                                        : BASE_URL . '/api/users?profileImage=false';
                                                            @endphp

                                                            <x-global::actions.dropdown class="f-left" align="start"
                                                                contentRole="menu">
                                                                <!-- Dropdown Trigger -->
                                                                <x-slot:labelText>
                                                                    <span class="text">
                                                                        <span id="userImage{{ $row->id }}">
                                                                            <img src="{{ $userImageUrl }}" width="25"
                                                                                style="vertical-align: middle;" />
                                                                        </span>
                                                                        <span id="user{{ $row->id }}"></span>
                                                                    </span>
                                                                </x-slot:labelText>

                                                                <!-- Menu Slot -->
                                                                <x-slot:menu>
                                                                    <!-- Dropdown Header -->
                                                                    <x-global::actions.dropdown.item
                                                                        variant="header-border">
                                                                        {!! __('dropdown.choose_user') !!}
                                                                    </x-global::actions.dropdown.item>

                                                                    <!-- Dynamic User Items -->
                                                                    @foreach ($users as $user)
                                                                        <x-global::actions.dropdown.item
                                                                            href="javascript:void(0);" :data-label="sprintf(
                                                                                __('text.full_name'),
                                                                                $user['firstname'],
                                                                                $user['lastname'],
                                                                            )"
                                                                            :data-value="$row->id .
                                                                                '_' .
                                                                                $user['id'] .
                                                                                '_' .
                                                                                $user['profileId']"
                                                                            id="userStatusChange{{ $row->id . $user['id'] }}">
                                                                            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}"
                                                                                width="25"
                                                                                style="vertical-align: middle; margin-right:5px;" />
                                                                            {{ sprintf(__('text.full_name'), $user['firstname'], $user['lastname']) }}
                                                                        </x-global::actions.dropdown.item>
                                                                    @endforeach
                                                                </x-slot:menu>
                                                            </x-global::actions.dropdown>
                                                        </div>
                                                        <div class="pull-right" style="margin-right:10px;">
                                                            <a href="#/ideas/ideaDialog/{{ $row->id }}"
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
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="clearfix"></div>
            @else
                <br /><br />
                <div class='center'>
                    <div style='width:50%' class='svgContainer'>
                        {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
                    </div>
                    <br />
                    <h4>{!! __('headlines.have_an_idea') !!}</h4><br />
                    {!! __('subtitles.start_collecting_ideas') !!}<br /><br />
                    @if ($login::userIsAtLeast($roles::$editor))
                        <a href="javascript:void(0);" class="addCanvasLink btn btn-primary">{!! __('buttons.start_new_idea_board') !!}</a>
                    @endif
                </div>
            @endif
            <!-- Modals -->


            <div class="modal fade bs-example-modal-lg" id="addCanvas">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="" method="post">
                            <div class="modal-header">
                                <x-global::forms.button  type="button" class="close" data-dismiss="modal" aria-hidden="true" content-role="secondary">
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
                                <x-global::forms.button type="button" class="btn btn-default" data-dismiss="modal" content-role="secondary">
                                    {!! __('buttons.close') !!}
                                </x-global::forms.button>

                                <x-global::forms.button type="submit" class="btn btn-default" name="newCanvas">
                                    {!! __('buttons.create_board') !!}
                                </x-global::forms.button>

                            </div>
                        </form>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

            <div class="modal fade bs-example-modal-lg" id="editCanvas">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="" method="post">
                            <div class="modal-header">
                                <x-global::forms.button type="button" class="close" data-dismiss="modal"
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
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->


        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {

            leantime.ideasController.initBoardControlModal();
            leantime.ideasController.setKanbanHeights();

            @if ($login::userIsAtLeast($roles::$editor))
                var ideaStatusList = [
                    @foreach ($canvasLabels as $key => $statusRow)
                        '{{ $key }}',
                    @endforeach
                ];
                leantime.ideasController.initIdeaKanban(ideaStatusList);
                leantime.canvasController.initUserDropdown('ideas');
            @else
                leantime.authController.makeInputReadonly(".maincontentinner");
            @endif

        });
    </script>

@endsection

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

<div class="pageheader">
    <div class="pageicon"><i class="far fa-lightbulb"></i></div>
    <div class="pagetitle">
        <h5>{{ $tpl->escape(session('currentProjectClient') . ' // ' . session('currentProjectName')) }}</h5>
        @if (count($allCanvas) > 0)
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="javascript:void(0)" class="editCanvasLink ">{!! $tpl->__('links.icon.edit') !!}</a></li>
                        <li><a href="{{ BASE_URL }}/ideas/delCanvas/{{ $tpl->get('currentCanvas') }}" class="delete">{!! $tpl->__('links.icon.delete') !!}</a></li>
                    @endif
                </ul>
            </span>
        @endif
        <h1>{{ $tpl->__('headlines.idea_management') }}
            //
            @if (count($allCanvas) > 0)
                <span class="dropdown dropdownWrapper">
                    <a href="javascript:void(0);" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown" style="max-width:200px;">
                        {{ $tpl->escape($canvasTitle) }}&nbsp;<i class="fa fa-caret-down"></i>
                    </a>

                    <ul class="dropdown-menu canvasSelector">
                        @if ($login::userIsAtLeast($roles::$editor))
                            <li><a href="javascript:void(0)" class="addCanvasLink">{!! $tpl->__('links.icon.create_new_board') !!}</a></li>
                        @endif
                        <li class="border"></li>
                        @foreach ($tpl->get('allCanvas') as $canvasRow)
                            <li><a href="{{ BASE_URL }}/ideas/showBoards/{{ $canvasRow['id'] }}">{{ $tpl->escape($canvasRow['title']) }}</a></li>
                        @endforeach
                    </ul>
                </span>
            @endif
        </h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">
        {!! $tpl->displayNotification() !!}

        <div class="tw:flex tw:justify-between tw:items-center tw:flex-wrap tw:gap-2">
            <div>
                @if ($login::userIsAtLeast($roles::$editor))
                    @if (count($tpl->get('allCanvas')) > 0)
                        <x-global::button link="#/ideas/ideaDialog?type=idea" type="primary" id="customersegment" icon="far fa-lightbulb">{{ $tpl->__('buttons.add_idea') }}</x-global::button>
                    @endif
                @endif
            </div>

            <div>
                <div class="btn-group viewDropDown">
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{!! $tpl->__('buttons.idea_kanban') !!} {!! $tpl->__('links.view') !!}</button>
                    <ul class="dropdown-menu">
                        <li><a href="{{ BASE_URL }}/ideas/showBoards">{!! $tpl->__('buttons.idea_wall') !!}</a></li>
                        <li><a href="{{ BASE_URL }}/ideas/advancedBoards" class="active">{!! $tpl->__('buttons.idea_kanban') !!}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>
        @if (count($tpl->get('allCanvas')) > 0)
            <div id="sortableIdeaKanban" class="sortableTicketList">

                <div class="tw:flex tw:flex-wrap">

                    @foreach ($tpl->get('canvasLabels') as $key => $statusRow)
                    <div class="column" style="width:{{ $size }}%;">

                        <h4 class="widgettitle title-primary">
                            @if ($login::userIsAtLeast($roles::$manager))
                                <a href="#/setting/editBoxLabel?module=idealabels&label={{ $key }}"
                                   class="editHeadline"><i class="fas fa-edit"></i></a>
                            @endif
                            {{ $tpl->escape($statusRow['name']) }}
                        </h4>

                        <div class="contentInner status_{{ $key }}">

                            @foreach ($tpl->get('canvasItems') as $row)
                                @if ($row['box'] == $key)
                                    <div class="ticketBox moveable" id="item_{{ $row['id'] }}">

                                                @if ($login::userIsAtLeast($roles::$editor))
                                                    <div class="inlineDropDownContainer" style="float:right;">

                                                        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                        </a>
                                                        &nbsp;&nbsp;&nbsp;
                                                        <ul class="dropdown-menu">
                                                            <li class="nav-header">{{ $tpl->__('subtitles.edit') }}</li>
                                                            <li><a href="#/ideas/ideaDialog/{{ $row['id'] }}" class="" data="item_{{ $row['id'] }}"> {{ $tpl->__('links.edit_canvas_item') }}</a></li>
                                                            <li><a href="#/ideas/delCanvasItem/{{ $row['id'] }}" class="delete" data="item_{{ $row['id'] }}"> {{ $tpl->__('links.delete_canvas_item') }}</a></li>
                                                        </ul>
                                                    </div>
                                                @endif

                                                <h4><a href="{{ BASE_URL }}/ideas/advancedBoards/#/ideas/ideaDialog/{{ $row['id'] }}" class=""
                                                       data="item_{{ $row['id'] }}">{{ $tpl->escape($row['description']) }}</a></h4>

                                                <div class="mainIdeaContent">
                                                    <div class="kanbanCardContent">
                                                        <div class="kanbanContent" style="margin-bottom: 20px">
                                                            {!! $tpl->escapeMinimal($row['data']) !!}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="clearfix" style="padding-bottom: 8px;"></div>

                                                <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                    <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text">
                                                            @if ($row['authorFirstname'] != '')
                                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $row['author'] }}" width="25" style="vertical-align: middle;"/></span><span id="user{{ $row['id'] }}"></span>
                                                            @else
                                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage=false" width="25" style="vertical-align: middle;"/></span><span id="user{{ $row['id'] }}"></span>
                                                            @endif
                                                        </span>
                                                    </a>
                                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                                        <li class="nav-header border">{{ $tpl->__('dropdown.choose_user') }}</li>
                                                        @foreach ($tpl->get('users') as $user)
                                                            <li class="dropdown-item">
                                                                <a href="javascript:void(0);" data-label="{{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}" data-value="{{ $row['id'] }}_{{ $user['id'] }}_{{ $user['profileId'] }}" id="userStatusChange{{ $row['id'] }}{{ $user['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}" width="25" style="vertical-align: middle; margin-right:5px;"/>{{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}</a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>

                                                <div class="tw:float-right" style="margin-right:10px;">
                                                    <a href="#/ideas/ideaDialog/{{ $row['id'] }}"
                                                        data="item_{{ $row['id'] }}"
                                                        {!! $row['commentCount'] == 0 ? 'style="color: grey;"' : '' !!}>
                                                        <span class="fas fa-comments"></span></a> <small>{{ $row['commentCount'] }}</small>
                                                </div>

                                        @if ($row['milestoneHeadline'] != '')
                                            <br/>
                                            <div hx-trigger="load"
                                                 hx-indicator=".htmx-indicator"
                                                 hx-target="this"
                                                 hx-swap="innerHTML"
                                                 hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row['milestoneId'] }}">
                                                <div class="htmx-indicator">
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
            <div class="tw:text-center">
                <div style="width:50%" class="svgContainer">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
                </div>

                <br/><h4>{{ $tpl->__('headlines.have_an_idea') }}</h4><br/>
                {{ $tpl->__('subtitles.start_collecting_ideas') }}<br/><br/>
                @if ($login::userIsAtLeast($roles::$editor))
                    <x-global::button link="javascript:void(0);" type="primary" class="addCanvasLink">{{ $tpl->__('buttons.start_new_idea_board') }}</x-global::button>
                @endif
            </div>

        @endif
        <!-- Modals -->

        <div class="modal fade bs-example-modal-lg" id="addCanvas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">{{ $tpl->__('headlines.start_new_idea_board') }}</h4>
                        </div>
                        <div class="modal-body">
                            <label>{{ $tpl->__('label.topic_idea_board') }}</label>
                            <x-global::forms.input name="canvastitle"
                                   placeholder="{{ $tpl->__('input.placeholders.name_for_idea_board') }}"
                                   style="width:90%" />
                        </div>
                        <div class="modal-footer">
                            <x-global::button tag="button" type="secondary" data-dismiss="modal">{{ $tpl->__('buttons.close') }}</x-global::button>
                            <x-global::button submit type="secondary" name="newCanvas">{{ $tpl->__('buttons.create_board') }}</x-global::button>
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
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">{{ $tpl->__('headlines.edit_board_name') }}</h4>
                        </div>
                        <div class="modal-body">
                            <label>{{ $tpl->__('label.title_idea_board') }}</label>
                            <x-global::forms.input name="canvastitle" value="{{ $tpl->escape($canvasTitle) }}"
                                   style="width:90%" />
                        </div>
                        <div class="modal-footer">
                            <x-global::button tag="button" type="secondary" data-dismiss="modal">{{ $tpl->__('buttons.close') }}</x-global::button>
                            <x-global::button submit type="secondary" name="editCanvas">{{ $tpl->__('buttons.save') }}</x-global::button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function () {

        leantime.ideasController.initBoardControlModal();
        leantime.ideasController.setKanbanHeights();

        @if ($login::userIsAtLeast($roles::$editor))
        var ideaStatusList = [@foreach ($canvasLabels as $key => $statusRow)'{{ $key }}',@endforeach];
            leantime.ideasController.initIdeaKanban(ideaStatusList);
            leantime.ideasController.initUserDropdown();
        @else
            leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

    });

</script>

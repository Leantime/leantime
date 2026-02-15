@php
    use Leantime\Domain\Comments\Repositories\Comments;

    $canvasTypes = $tpl->get('canvasTypes');
    $canvasItems = $tpl->get('canvasItems');
    $statusLabels = $statusLabels ?? $tpl->get('statusLabels');
    $relatesLabels = $relatesLabels ?? $tpl->get('relatesLabels');
@endphp

<h4 class="widgettitle title-primary">
    @if (isset($canvasTypes[$elementName]['icon']))
        <i class="fas {{ $canvasTypes[$elementName]['icon'] }}"></i>
    @endif
    {{ $canvasTypes[$elementName]['title'] }}
</h4>
<div class="contentInner even status_{{ $elementName }}"
     {!! isset($canvasTypes[$elementName]['color']) ? 'style="background: ' . $canvasTypes[$elementName]['color'] . ';"' : '' !!}>

    @foreach ($canvasItems as $row)
        @php
            $filterStatus = $filter['status'] ?? 'all';
            $filterRelates = $filter['relates'] ?? 'all';
        @endphp

        @if ($row['box'] === $elementName && ($filterStatus == 'all' || $filterStatus == $row['status']) && ($filterRelates == 'all' || $filterRelates == $row['relates']))
            @php
                $comments = app()->make(Comments::class);
                $nbcomments = $comments->countComments(moduleId: $row['id']);
            @endphp

            <div class="ticketBox" id="item_{{ $row['id'] }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="inlineDropDownContainer" style="float:right;">
                            @if ($login::userIsAtLeast($roles::$editor))
                                <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                </a>
                            @endif

                            @if ($login::userIsAtLeast($roles::$editor))
                                &nbsp;&nbsp;&nbsp;
                                <ul class="dropdown-menu">
                                    <li class="nav-header">{{ $tpl->__('subtitles.edit') }}</li>
                                    <li><a href="#/{{ $canvasName }}canvas/editCanvasItem/{{ $row['id'] }}"
                                           data="item_{{ $row['id'] }}"> {{ $tpl->__('links.edit_canvas_item') }}</a></li>
                                    <li><a href="#/{{ $canvasName }}canvas/delCanvasItem/{{ $row['id'] }}"
                                           class="delete"
                                           data="item_{{ $row['id'] }}"> {{ $tpl->__('links.delete_canvas_item') }}</a></li>
                                </ul>
                            @endif
                        </div>

                        <h4><a href="#/{{ $canvasName }}canvas/editCanvasItem/{{ $row['id'] }}"
                               data="item_{{ $row['id'] }}">{{ $tpl->escape($row['description']) }}</a></h4>

                        @if ($row['conclusion'] != '')
                            <small>{!! $tpl->convertRelativePaths($row['conclusion']) !!}</small>
                        @endif

                        <div class="clearfix" style="padding-bottom: 8px;"></div>

                        @if (! empty($statusLabels))
                            <div class="dropdown ticketDropdown statusDropdown colorized show firstDropdown">
                                <a class="dropdown-toggle f-left status label-{{ $statusLabels[$row['status']]['dropdown'] }}"
                                   href="javascript:void(0);" role="button"
                                   id="statusDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="text">{{ $statusLabels[$row['status']]['title'] }}</span> <i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                                    <li class="nav-header border">{{ $tpl->__('dropdown.choose_status') }}</li>
                                    @foreach ($statusLabels as $key => $data)
                                        @if ($data['active'] || true)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);" class="label-{{ $data['dropdown'] }}"
                                                   data-label="{{ $data['title'] }}" data-value="{{ $row['id'] }}/{{ $key }}"
                                                   id="ticketStatusChange{{ $row['id'] }}{{ $key }}">{{ $data['title'] }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (! empty($relatesLabels))
                            <div class="dropdown ticketDropdown relatesDropdown colorized show firstDropdown">
                                <a class="dropdown-toggle f-left relates label-{{ $relatesLabels[$row['relates']]['dropdown'] }}"
                                   href="javascript:void(0);" role="button"
                                   id="relatesDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true"
                                   aria-expanded="false">
                                    <span class="text">{{ $relatesLabels[$row['relates']]['title'] }}</span> <i class="fa fa-caret-down" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="relatesDropdownMenuLink{{ $row['id'] }}">
                                    <li class="nav-header border">{{ $tpl->__('dropdown.choose_relates') }}</li>
                                    @foreach ($relatesLabels as $key => $data)
                                        @if ($data['active'] || true)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);" class="label-{{ $data['dropdown'] }}"
                                                   data-label="{{ $data['title'] }}"
                                                   data-value="{{ $row['id'] }}/{{ $key }}"
                                                   id="ticketRelatesChange{{ $row['id'] }}{{ $key }}">{{ $data['title'] }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                            <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink{{ $row['id'] }}"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text">
                                    @if ($row['authorFirstname'] != '')
                                        <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $row['author'] }}" width="25" style="vertical-align: middle;" /></span><span id="user{{ $row['id'] }}"></span>
                                    @else
                                        <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage=false" width="25" style="vertical-align: middle;" /></span><span id="user{{ $row['id'] }}"></span>
                                    @endif
                                </span>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                <li class="nav-header border">{{ $tpl->__('dropdown.choose_user') }}</li>
                                @foreach ($tpl->get('users') as $user)
                                    <li class="dropdown-item">
                                        <a href="javascript:void(0);"
                                           data-label="{{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}"
                                           data-value="{{ $row['id'] }}_{{ $user['id'] }}_{{ $user['profileId'] }}"
                                           id="userStatusChange{{ $row['id'] }}{{ $user['id'] }}">
                                            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}&v={{ $user['modified'] }}" width="25" style="vertical-align: middle; margin-right:5px;" />
                                            {{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="pull-right" style="margin-right:10px;">
                            <span class="fas fa-comments"></span> <small>{{ $nbcomments }}</small>
                        </div>
                    </div>
                </div>

                @if ($row['milestoneHeadline'] != '')
                    <br />
                    <div hx-trigger="load"
                         hx-indicator=".htmx-indicator"
                         hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row['milestoneId'] }}">
                        <div class="htmx-indicator">
                            {{ $tpl->__('label.loading_milestone') }}
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endforeach

    <br />
    @if ($login::userIsAtLeast($roles::$editor))
        <a href="#/{{ $canvasName }}canvas/editCanvasItem?type={{ $elementName }}"
           class="" id="{{ $elementName }}"
           style="padding-bottom: 10px;">{!! $tpl->__('links.add_new_canvas_item') !!}</a>
    @endif
</div>

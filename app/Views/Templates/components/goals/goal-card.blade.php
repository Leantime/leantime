@props([
    'row' => [],
    'statusLabels' => [],
    'relatesLabels' => [],
    'users' => [],
    'elementName' => 'goal',
])

@php
    $comments = app()->make(\Leantime\Domain\Comments\Repositories\Comments::class);
    $nbcomments = $comments->countComments(moduleId: $row['id']);

    $percentDone = $row['goalProgress'];
    $metricTypeFront = '';
    $metricTypeBack = '';
    if ($row['metricType'] == 'percent') {
        $metricTypeBack = '%';
    } elseif ($row['metricType'] == 'currency') {
        $metricTypeFront = __('language.currency');
    }
@endphp

<div class="ticketBox" id="item_{{ $row['id'] }}">
    @if ($login::userIsAtLeast($roles::$editor))
        <x-globals::actions.dropdown-menu class="pull-right">
            <li><a href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}"
                    data="item_{{ $row['id'] }}">
                    {!! __('links.edit_canvas_item') !!}</a></li>
            <li><a href="#/goalcanvas/delCanvasItem/{{ $row['id'] }}"
                    data="item_{{ $row['id'] }}">
                {!! __('links.delete_canvas_item') !!}</a></li>
        </x-globals::actions.dropdown-menu>
    @endif

    <h4><strong>Goal:</strong> <a
            href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}"
            data="item_{{ $row['id'] }}">{{ $row['title'] }}</a>
    </h4>
    <br />
    <strong>Metric:</strong> {{ $row['description'] }}
    <br /><br />

    <x-global::progress :value="$percentDone" />
    <div class="row tw:pb-0">
        <div class="col-md-4">
            <small>Start:<br />{{ $metricTypeFront . $row['startValue'] . $metricTypeBack }}</small>
        </div>
        <div class="col-md-4 center">
            <small>{{ __('label.current') }}:<br />{{ $metricTypeFront . $row['currentValue'] . $metricTypeBack }}</small>
        </div>
        <div class="col-md-4 align-right">
            <small>{{ __('label.goal') }}:<br />{{ $metricTypeFront . $row['endValue'] . $metricTypeBack }}</small>
        </div>
    </div>

    <div class="clearfix tw:pb-2"></div>

    @if (!empty($statusLabels))
        <div class="dropdown ticketDropdown statusDropdown colorized firstDropdown">
            <a href="javascript:void(0)" class="dropdown-toggle f-left status label-{{ $row['status'] != '' ? $statusLabels[$row['status']]['dropdown'] : '' }}" data-toggle="dropdown"
                id="statusDropdownMenuLink{{ $row['id'] }}">
                <span class="text">{{ $row['status'] != '' ? $statusLabels[$row['status']]['title'] : '' }}</span>
                <x-global::elements.icon name="arrow_drop_down" />
            </a>
            <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                <li class="nav-header border">{{ __('dropdown.choose_status') }}</li>
                @foreach ($statusLabels as $key => $data)
                    @if ($data['active'] || true)
                        <li class='dropdown-item'>
                            <a href="javascript:void(0);"
                                onclick="document.activeElement.blur();"
                                class="label-{{ $data['dropdown'] }}"
                                data-label='{{ $data['title'] }}'
                                data-value="{{ $row['id'] . '/' . $key }}"
                                id="ticketStatusChange{{ $row['id'] . $key }}">{{ $data['title'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    @if (!empty($relatesLabels))
        <div class="dropdown ticketDropdown relatesDropdown colorized firstDropdown">
            <a href="javascript:void(0)" class="dropdown-toggle f-left relates label-{{ $relatesLabels[$row['relates']]['dropdown'] }}" data-toggle="dropdown"
                id="relatesDropdownMenuLink{{ $row['id'] }}">
                <span class="text">{{ $relatesLabels[$row['relates']]['title'] }}</span>
                <x-global::elements.icon name="arrow_drop_down" />
            </a>
            <ul class="dropdown-menu" aria-labelledby="relatesDropdownMenuLink{{ $row['id'] }}">
                <li class="nav-header border">{{ __('dropdown.choose_relates') }}</li>
                @foreach ($relatesLabels as $key => $data)
                    @if ($data['active'] || true)
                        <li class='dropdown-item'>
                            <a href="javascript:void(0);"
                                onclick="document.activeElement.blur();"
                                class="label-{{ $data['dropdown'] }}"
                                data-label='{{ $data['title'] }}'
                                data-value="{{ $row['id'] . '/' . $key }}"
                                id="ticketRelatesChange{{ $row['id'] . $key }}">{{ $data['title'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dropdown ticketDropdown userDropdown noBg right lastDropdown dropRight">
        <a href="javascript:void(0)" class="dropdown-toggle f-left" data-toggle="dropdown"
            id="userDropdownMenuLink{{ $row['id'] }}">
            <span class="text">
                @if ($row['authorFirstname'] != '')
                    <span id='userImage{{ $row['id'] }}'>
                        <img src='{{ BASE_URL }}/api/users?profileImage={{ $row['author'] }}'
                            width='25'
                            class="tw:align-middle" />
                    </span>
                    <span id='user{{ $row['id'] }}'></span>
                @else
                    <span id='userImage{{ $row['id'] }}'>
                        <img src='{{ BASE_URL }}/api/users?profileImage=false'
                            width='25'
                            class="tw:align-middle" />
                    </span>
                    <span id='user{{ $row['id'] }}'></span>
                @endif
            </span>
        </a>
        <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
            <li class="nav-header border">{{ __('dropdown.choose_user') }}</li>
            @foreach ($users as $user)
                <li class='dropdown-item'>
                    <a href='javascript:void(0);'
                        onclick="document.activeElement.blur();"
                        data-label='{{ sprintf(__('text.full_name'), $user['firstname'], $user['lastname']) }}'
                        data-value='{{ $row['id'] . '_' . $user['id'] . '_' . $user['profileId'] }}'
                        id='userStatusChange{{ $row['id'] . $user['id'] }}'>
                        <img src='{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}'
                            width='25'
                            class="tw:align-middle tw:mr-1" />
                        {{ sprintf(__('text.full_name'), $user['firstname'], $user['lastname']) }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="right tw:mr-2.5">
        <a href="#/goalcanvas/editCanvasComment/{{ $row['id'] }}"
            class="commentCountLink"
            data="item_{{ $row['id'] }}"><x-global::elements.icon name="forum" /></a>
        <small>{{ $nbcomments }}</small>
    </div>

    @if ($row['milestoneHeadline'] != '')
        <br />
        <div hx-trigger="load" hx-indicator=".htmx-indicator"
            hx-target="this" hx-swap="innerHTML"
            hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row['milestoneId'] }}"
            aria-live="polite">
            <div class="htmx-indicator" role="status">
                {{ __('label.loading_milestone') }}
            </div>
        </div>
    @endif
</div>

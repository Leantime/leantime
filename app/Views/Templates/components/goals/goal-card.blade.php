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

<div class="ticketBox" style="padding: 20px 20px 24px;" id="item_{{ $row['id'] }}">
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

    <div style="margin-top: 16px;">
        <strong>Metric:</strong> {{ $row['description'] }}
    </div>

    <div style="margin-top: 20px;">
        <x-globals::feedback.progress :value="$percentDone" />
    </div>
    <div class="row" style="margin-top: 6px;">
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

    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 20px; line-height: 25px;">
        <div style="display: flex; align-items: center; gap: 6px;">
            @if (!empty($statusLabels))
                <div class="dropdown ticketDropdown statusDropdown colorized firstDropdown" style="margin: 0;">
                    <a href="javascript:void(0)" class="dropdown-toggle f-left status label-{{ $row['status'] != '' ? $statusLabels[$row['status']]['dropdown'] : '' }}" data-toggle="dropdown"
                        style="line-height: 25px;"
                        id="statusDropdownMenuLink{{ $row['id'] }}">
                        <span class="text">{{ $row['status'] != '' ? $statusLabels[$row['status']]['title'] : '' }}</span>
                        <x-globals::elements.icon name="arrow_drop_down" />
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
                <div class="dropdown ticketDropdown relatesDropdown colorized firstDropdown" style="margin: 0;">
                    <a href="javascript:void(0)" class="dropdown-toggle f-left relates label-{{ $relatesLabels[$row['relates']]['dropdown'] }}" data-toggle="dropdown"
                        style="line-height: 25px;"
                        id="relatesDropdownMenuLink{{ $row['id'] }}">
                        <span class="text">{{ $relatesLabels[$row['relates']]['title'] }}</span>
                        <x-globals::elements.icon name="arrow_drop_down" />
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
        </div>

        <div style="display: flex; align-items: center; gap: 12px; line-height: 25px;">
            <a href="#/goalcanvas/editCanvasComment/{{ $row['id'] }}"
                class="commentCountLink"
                style="display: inline-flex; align-items: center; gap: 4px; line-height: 25px;"
                data="item_{{ $row['id'] }}"><x-globals::elements.icon name="forum" /><small>{{ $nbcomments }}</small></a>

            <x-globals::actions.user-select
                :entityId="$row['id']"
                :assignedUserId="$row['author']"
                :assignedName="$row['authorFirstname']"
                :users="$users"
                :showNameLabel="false"
                :showArrowIcon="false"
                :showUnassign="false"
                dropdownClasses="lastDropdown dropRight"
            />
        </div>
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

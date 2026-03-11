@php
    use Leantime\Domain\Comments\Repositories\Comments;

    $canvasTypes = $tpl->get('canvasTypes');
    $canvasItems = $tpl->get('canvasItems');
    $statusLabels = $statusLabels ?? $tpl->get('statusLabels');
    $relatesLabels = $relatesLabels ?? $tpl->get('relatesLabels');
@endphp

<x-globals::elements.section-title variant="primary">
    @if (!empty($canvasTypes[$elementName]['icon']))
        <x-globals::elements.icon :name="$canvasTypes[$elementName]['icon']" />
    @endif
    {{ $canvasTypes[$elementName]['title'] }}
</x-globals::elements.section-title>
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
                @if ($login::userIsAtLeast($roles::$editor))
                    <x-globals::actions.dropdown-menu class="tw:float-right" position="left">
                        <li><a href="#/{{ $canvasName }}canvas/editCanvasItem/{{ $row['id'] }}"
                               data="item_{{ $row['id'] }}"><x-globals::elements.icon name="edit" /> {{ $tpl->__('links.edit_canvas_item') }}</a></li>
                        <li><a href="#/{{ $canvasName }}canvas/delCanvasItem/{{ $row['id'] }}"
                               class="delete"
                               data="item_{{ $row['id'] }}"><x-globals::elements.icon name="delete" /> {{ $tpl->__('links.delete_canvas_item') }}</a></li>
                    </x-globals::actions.dropdown-menu>
                @endif

                <h4><a href="#/{{ $canvasName }}canvas/editCanvasItem/{{ $row['id'] }}"
                       data="item_{{ $row['id'] }}">{{ $tpl->escape($row['description']) }}</a></h4>

                @if ($row['conclusion'] != '')
                    <small>{!! $tpl->convertRelativePaths($row['conclusion']) !!}</small>
                @endif

                <div class="clearfix tw:pb-2"></div>

                @if (! empty($statusLabels))
                    <div class="dropdown ticketDropdown statusDropdown colorized firstDropdown">
                        <a href="javascript:void(0)" class="dropdown-toggle f-left status label-{{ $statusLabels[$row['status']]['dropdown'] }}" data-toggle="dropdown"
                           id="statusDropdownMenuLink{{ $row['id'] }}">
                            <span class="text">{{ $statusLabels[$row['status']]['title'] }}</span> <x-globals::elements.icon name="arrow_drop_down" />
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                            <li class="nav-header border">{{ $tpl->__('dropdown.choose_status') }}</li>
                            @foreach ($statusLabels as $key => $data)
                                @if ($data['active'] || true)
                                    <li class="dropdown-item">
                                        <a href="javascript:void(0);" onclick="document.activeElement.blur();" class="label-{{ $data['dropdown'] }}"
                                           data-label="{{ $data['title'] }}" data-value="{{ $row['id'] }}/{{ $key }}"
                                           id="ticketStatusChange{{ $row['id'] }}{{ $key }}">{{ $data['title'] }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (! empty($relatesLabels))
                    <div class="dropdown ticketDropdown relatesDropdown colorized firstDropdown">
                        <a href="javascript:void(0)" class="dropdown-toggle f-left relates label-{{ $relatesLabels[$row['relates']]['dropdown'] }}" data-toggle="dropdown"
                           id="relatesDropdownMenuLink{{ $row['id'] }}">
                            <span class="text">{{ $relatesLabels[$row['relates']]['title'] }}</span> <x-globals::elements.icon name="arrow_drop_down" />
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="relatesDropdownMenuLink{{ $row['id'] }}">
                            <li class="nav-header border">{{ $tpl->__('dropdown.choose_relates') }}</li>
                            @foreach ($relatesLabels as $key => $data)
                                @if ($data['active'] || true)
                                    <li class="dropdown-item">
                                        <a href="javascript:void(0);" onclick="document.activeElement.blur();" class="label-{{ $data['dropdown'] }}"
                                           data-label="{{ $data['title'] }}"
                                           data-value="{{ $row['id'] }}/{{ $key }}"
                                           id="ticketRelatesChange{{ $row['id'] }}{{ $key }}">{{ $data['title'] }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

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
                <div class="pull-right tw:mr-2">
                    <x-globals::elements.icon name="forum" /> <small>{{ $nbcomments }}</small>
                </div>

                @if ($row['milestoneHeadline'] != '')
                    <div class="clearfix"></div>
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
        @endif
    @endforeach

    <br />
    @if ($login::userIsAtLeast($roles::$editor))
        <a href="#/{{ $canvasName }}canvas/editCanvasItem?type={{ $elementName }}"
           id="{{ $elementName }}"
           class="tw:pb-2 tw:block"><x-globals::elements.icon name="add" /> {{ __('links.add_new_canvas_item') }}</a>
    @endif
</div>

<div class="ticketBox fixed priority-border-{{ $row['priority'] }}" data-val="{{ $row['id'] }}">
    <div class="row">
        <div class="col-md-8 titleContainer">
            @if($cardType == "full")
                <small>{{ $row['projectName'] }}</small><br />
                @if($row['dependingTicketId'] > 0)
                    <a href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}">{{ $row['parentHeadline'] }}</a> //
                @endif
            @endif
            <strong><a href="#/tickets/showTicket/{{ $row['id'] }}" >{{ $row['headline'] }}</a></strong>

        </div>
        <div class="col-md-4 timerContainer" style="padding:5px 15px;" id="timerContainer-{{ $row['id'] }}">

            @include("tickets::partials.ticketsubmenu", ["ticket" => $row, "onTheClock" => $onTheClock])
            @if($cardType == "full")
                <div class="scheduler pull-right">
                    @if( $row['editFrom'] != "0000-00-00 00:00:00" && $row['editFrom'] != "1969-12-31 00:00:00")
                        <i class="fa-solid fa-calendar-check infoIcon tw:mr-xs" style="color:var(--accent2)" data-tippy-content="{{ __('text.schedule_to_start_on') }} {{ format($row['editFrom'])->date() }}"></i>
                    @else
                        <i class="fa-regular fa-calendar-xmark infoIcon tw:mr-xs" data-tippy-content="{{ __('text.not_scheduled_drag_ai') }}"></i>
                    @endif
                </div>
            @endif
        </div>
    </div>
    <div class="row">

            <div class="col-md-4" style="padding:0 15px;">
                @if($cardType == "full")
                    <i class="fa-solid fa-business-time infoIcon" data-tippy-content=" {{ __("label.due") }}"></i>
                    <input type="text" title="{{ __("label.due") }}" value="{{ format($row['dateToFinish'])->date(__("text.anytime")) }}" class="duedates secretInput" style="margin-left:0px;" data-id="{{ $row['id'] }}" name="date" />
                @endif
            </div>

        <div class="col-md-8 dropdownContainer" style="padding-top:5px;">
            <div class="dropdown ticketDropdown statusDropdown colorized show right ">
                <a class="dropdown-toggle f-left status {{ $statusLabels[$row['status']]["class"] }}" href="javascript:void(0);" role="button" id="statusDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">
                                                                @if(isset($statusLabels[$row['status']]))
                                                                    {{ $statusLabels[$row['status']]["name"] }}
                                                                @else
                                                                    unknown
                                                                @endif
                                                            </span>
                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu pull-right" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                    <li class="nav-header border">{{ __("dropdown.choose_status") }}</li>

                    @foreach ($statusLabels as $key => $label)
                        <li class='dropdown-item'>
                            <a href='javascript:void(0);'
                               class='{{ $label["class"] }}'
                               data-label='{{ $label["name"] }}'
                               data-value='{{ $row['id'] }}_{{ $key }}_{{ $label["class"] }}'
                               id='ticketStatusChange{{$row['id'] . $key }}'>
                                {{  $label["name"] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <?php /*
                                                        <div class="dropdown ticketDropdown effortDropdown show right">
                                                            <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">
                                                                @if ($row['storypoints'] != '' && $row['storypoints'] > 0)
                                                                    {{ $efforts["" . $row['storypoints']] ?? $row['storypoints'] }}
                                                                @else
                                                                    {{ __("label.story_points_unkown") }}
                                                                @endif
                                                            </span>
                                                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink{{ $row['id'] }}">
                                                                <li class="nav-header border">{{ __("dropdown.how_big_todo") }}</li>
                                                                @foreach($efforts as $effortKey => $effortValue)
                                                                    <li class='dropdown-item'>
                                                                        <a href='javascript:void(0);'
                                                                           data-value='{{ $row['id'] . "_" . $effortKey }}'
                                                                           id='ticketEffortChange{{ $row['id'] . $effortKey }}'>
                                                                            {{ $effortValue }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    */ ?>
            @if($cardType == "full")
                <div class="dropdown ticketDropdown milestoneDropdown colorized show right tw:mr-sm">
                <a style="background-color:{{ $row['milestoneColor'] }}"
                   class="dropdown-toggle f-left  label-default milestone"
                   href="javascript:void(0);"
                   role="button" id="milestoneDropdownMenuLink{{ $row['id'] }}"
                   data-toggle="dropdown"
                   aria-haspopup="true"
                   aria-expanded="false">
                                                                <span class="text">
                                                                    @if($row['milestoneid'] != "" && $row['milestoneid'] != 0)
                                                                        {{ $row['milestoneHeadline'] }}
                                                                    @else
                                                                        {{  __("label.no_milestone") }}
                                                                    @endif
                                                                </span>
                    &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu pull-right" aria-labelledby="milestoneDropdownMenuLink{{ $row['id'] }}">
                    <li class="nav-header border">{{ __("dropdown.choose_milestone") }}</li>
                    <li class='dropdown-item'>
                        <a style='background-color:#b0b0b0'
                           href='javascript:void(0);'
                           data-label="{{__("label.no_milestone") }}"
                           data-value='{{ $row['id'] }}_0_#b0b0b0'>
                            {{ __("label.no_milestone") }}
                        </a>
                    </li>
                    @if(isset($milestones))
                        @foreach($milestones as $milestone)
                            @if(is_object($milestone))
                                <li class='dropdown-item'>
                                    <a href='javascript:void(0);'
                                       data-label='{{ $milestone->headline }}'
                                       data-value='{{ $row['id'] }}_{{ $milestone->id }}_{{ $milestone->tags }}'
                                       id='ticketMilestoneChange{{ $row['id'] . $milestone->id }}'
                                       style='background-color:{{ $milestone->tags }}'>
                                        {{ $milestone->headline }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>

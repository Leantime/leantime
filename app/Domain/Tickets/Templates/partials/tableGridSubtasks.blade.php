{{--
    Subtask rows rendered inside DataTables row().child() expansion.
    Displayed as a mini-table matching the parent's column structure.
--}}
@php
    $parentTicketId = $tpl->get('parentTicketId');
    $subtasks = $tpl->get('subtasks');
    $statusLabels = $tpl->get('statusLabels');
    $efforts = $tpl->get('efforts');
@endphp

<div class="subtask-child-rows" data-parent-id="{{ $parentTicketId }}">
    <table class="table subtask-table" style="margin:0; background: var(--layered-background);">
        @if(is_array($subtasks) && count($subtasks) > 0)
            @foreach($subtasks as $subtask)
                <tr class="subtask-row" data-id="{{ $subtask['id'] }}" data-parent-id="{{ $parentTicketId }}">
                    {{-- Indent spacer --}}
                    <td style="width:60px; text-align:right; padding-left:30px;">
                        <i class="fa fa-diagram-successor" style="color:var(--accent2); opacity:0.6;"></i>
                    </td>

                    {{-- ID --}}
                    <td style="width:50px;">
                        <a href="#/tickets/showTicket/{{ $subtask['id'] }}" class="ticketModal" preload="mouseover">
                            #{{ $subtask['id'] }}
                        </a>
                    </td>

                    {{-- Title (click-to-edit) --}}
                    <td>
                        <span class="subtask-title-text" data-ticket-id="{{ $subtask['id'] }}" style="cursor:pointer;">
                            {{ $tpl->escape($subtask['headline']) }}
                        </span>
                        <input type="text" class="subtask-title-input secretInput" data-ticket-id="{{ $subtask['id'] }}"
                               value="{{ $tpl->escape($subtask['headline']) }}" style="display:none; width:80%;" />
                    </td>

                    {{-- Status --}}
                    <td style="width:120px;">
                        @php
                            $stClass = $statusLabels[$subtask['status']]['class'] ?? 'label-default';
                            $stName = $statusLabels[$subtask['status']]['name'] ?? 'New';
                        @endphp
                        <div class="dropdown ticketDropdown statusDropdown colorized show">
                            <a class="dropdown-toggle status {{ $stClass }} f-left"
                               href="javascript:void(0);" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text">{{ $stName }}</span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="nav-header border">{!! $tpl->__('dropdown.choose_status') !!}</li>
                                @foreach($statusLabels as $key => $label)
                                    <li class="dropdown-item">
                                        <a href="javascript:void(0);"
                                           class="{{ $label['class'] }}"
                                           data-label="{{ $tpl->escape($label['name']) }}"
                                           data-value="{{ $subtask['id'] }}_{{ $key }}_{{ $label['class'] }}">
                                            {{ $tpl->escape($label['name']) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </td>

                    {{-- Due Date --}}
                    <td style="width:110px;">
                        @php
                            if ($subtask['dateToFinish'] == '0000-00-00 00:00:00' || $subtask['dateToFinish'] == '1969-12-31 00:00:00') {
                                $stDate = $tpl->__('text.anytime');
                            } else {
                                $stDateObj = new DateTime($subtask['dateToFinish']);
                                $stDate = $stDateObj->format($tpl->__('language.dateformat'));
                            }
                        @endphp
                        <input type="text" title="{{ $tpl->__('label.due') }}"
                               value="{{ $stDate }}"
                               class="quickDueDates secretInput"
                               data-id="{{ $subtask['id'] }}" name="date" style="width:90px;" />
                    </td>

                    {{-- Planned Hours --}}
                    <td style="width:80px;">
                        <input type="text" value="{{ $tpl->e($subtask['planHours'] ?? '') }}"
                               name="planHours" class="small-input secretInput"
                               onchange="leantime.ticketsController.updatePlannedHours(this, '{{ $subtask['id'] }}');" />
                    </td>

                    {{-- Spacer for remaining columns --}}
                    <td></td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="7" style="padding-left:60px; color:var(--primary-font-color); opacity:0.6;">
                    <i class="fa fa-info-circle"></i> {{ $tpl->__('text.no_subtasks') ?? 'No subtasks yet' }}
                </td>
            </tr>
        @endif

        {{-- Quick-add subtask row --}}
        <tr class="add-subtask-row">
            <td style="width:60px; text-align:right; padding-left:30px;">
                <i class="fa fa-plus-circle" style="color:var(--accent2);"></i>
            </td>
            <td colspan="6">
                <input type="text" class="add-subtask-input secretInput" data-parent-id="{{ $parentTicketId }}"
                       placeholder="{{ $tpl->__('links.add_subtask') ?? 'Add subtask' }}... (Enter to save)"
                       style="width:60%; border:none;" />
            </td>
        </tr>
    </table>
</div>

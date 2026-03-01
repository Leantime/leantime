@php
    use Leantime\Core\Support\FromFormat;
@endphp

<!-- page header -->
<div class="pageheader">
    <div class="pageicon"><x-global::elements.icon name="schedule" /></div>
    <div class="pagetitle">
        <h5>{{ __('headline.overview') }}</h5>
        <h1>{{ __('headline.my_timesheets') }}</h1>
    </div>
</div>
<!-- page header -->

<div class="maincontent">
    <div class="maincontentinner">
        {!! $tpl->displayNotification() !!}

        <form action="{{ BASE_URL }}/timesheets/showMyList" method="post" id="timesheetListForm" name="timesheetListForm">
            <div class="tw:flex tw:items-center tw:flex-wrap tw:gap-2 tw:mb-4">
                <div class="tw:flex tw:items-center tw:gap-1">
                    <span>{{ __('label.date_from') }}</span>
                    <input type="text"
                           id="dateFrom"
                           class="dateFrom"
                           name="dateFrom"
                           autocomplete="off"
                           value="{{ $tpl->get('dateFrom')->formatDateForUser() }}"
                           style="width:110px;" />
                    <span>{{ __('label.until') }}</span>
                    <input type="text"
                           id="dateTo"
                           class="dateTo"
                           name="dateTo"
                           autocomplete="off"
                           value="{{ $tpl->get('dateTo')->formatDateForUser() }}"
                           style="width:110px;" />
                    <x-globals::forms.select :bare="true" id="kind" name="kind" onchange="submit();">
                        <option value="all">{{ __('label.all_types') }}</option>
                        @foreach ($tpl->get('kind') as $key => $row)
                            <option value="{{ $key }}"
                                @if ($key == $tpl->get('actKind'))
                                    selected="selected"
                                @endif
                            >{{ __($row) }}</option>
                        @endforeach
                    </x-globals::forms.select>
                    <x-globals::forms.button submit type="primary" class="reload">{{ __('buttons.search') }}</x-globals::forms.button>
                </div>

                <div class="tw:flex-1"></div>

                <x-globals::elements.link-dropdown :label="__('links.list_view')">
                    <li><a href="{{ BASE_URL }}/timesheets/showMy">{!! __('links.week_view') !!}</a></li>
                    <li><a href="{{ BASE_URL }}/timesheets/showMyList" class="active">{!! __('links.list_view') !!}</a></li>
                </x-globals::elements.link-dropdown>

                <x-globals::forms.button link="javascript:void(0);" type="primary" id="addHoursBtn"><x-global::elements.icon name="add" /> {{ __('label.add_hours') }}</x-globals::forms.button>
            </div>

            <style>
                #myTimesheetList th,
                #myTimesheetList td { padding: 8px; vertical-align: middle; }
                #myTimesheetList .form-group { margin: 0; }
                #myTimesheetList .newEntryRow input[type="text"],
                #myTimesheetList .newEntryRow select { margin: 0; }
                /* Force SlimSelect dropdowns to fill their cells */
                #myTimesheetList .newEntryRow .form-group { width: 100%; }
                #myTimesheetList .newEntryRow .ss-main {
                    display: block !important;
                    width: 100% !important;
                }
                /* New entry row visual separator */
                #myTimesheetList .newEntryRow {
                    border-top: 2px solid var(--main-border-color);
                }
                #myTimesheetList .newEntryRow td {
                    padding-top: 12px;
                }
                /* Also truncate existing-row project/ticket links */
                #myTimesheetList td a {
                    display: block;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                /* Make Ticket (To-Do) titles the primary focal point */
                #myTimesheetList .timesheetRow td:nth-child(3) a {
                    font-weight: 600;
                    color: var(--primary-font-color);
                }
                /* Dim Date column as secondary context */
                #myTimesheetList .timesheetRow td:nth-child(1) {
                    color: var(--secondary-font-color);
                    font-size: var(--font-size-s);
                }
                /* Dim Type column (repetitive info) */
                #myTimesheetList .timesheetRow td:nth-child(4) {
                    color: var(--secondary-font-color);
                    font-size: var(--font-size-s);
                }
                /* Dim zero-value and disabled hour cells */
                #myTimesheetList .hourCell[value="0"],
                #myTimesheetList .hourCell[disabled] {
                    color: var(--secondary-font-color);
                    opacity: 0.4;
                }
            </style>
            <div style="overflow-x: auto;">
            <table cellpadding="0" width="100%" class="table table-bordered display timesheetTable" id="myTimesheetList">
                <colgroup>
                    <col width="110px" />
                    <col width="200px" />
                    <col width="200px" />
                    <col width="160px" />
                    <col width="70px" />
                    <col width="140px" />
                    <col width="90px" />
                </colgroup>
                <thead>
                    <tr>
                        <th>{{ __('label.date') }}</th>
                        <th>{{ __('label.project') }}</th>
                        <th>{{ __('label.ticket') }}</th>
                        <th>{{ __('label.type') }}</th>
                        <th>{{ __('label.hours') }}</th>
                        <th>{{ __('label.description') }}</th>
                        <th>{{ __('label.billing') }}</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Existing timesheet entries --}}
                    @php $sum = 0; @endphp
                    @if (is_array($tpl->get('allTimesheets')))
                        @foreach ($tpl->get('allTimesheets') as $row)
                            @php
                                $sum += $row['hours'];
                                $isLocked = ($row['invoicedComp'] == '1' || $row['paid'] == '1');
                                $inputNameKey = $row['ticketId'] . '|' . $row['kind'] . '|' . format($row['workDate'])->date() . '|' . format($row['workDate'])->timestamp();
                            @endphp
                            <tr class="timesheetRow">
                                <td>{{ format($row['workDate'])->date() }}</td>
                                <td>
                                    <a href="{{ BASE_URL }}/projects/showProject/{{ $row['projectId'] }}">{{ e($row['name']) }}</a>
                                </td>
                                <td>
                                    <a href="#/tickets/showTicket/{{ $row['ticketId'] }}">{{ e($row['headline']) }}</a>
                                </td>
                                <td>{{ __($tpl->get('kind')[$row['kind']] ?? '') }}</td>
                                <td>
                                    <input type="text"
                                           class="hourCell"
                                           name="{{ $inputNameKey }}"
                                           value="{{ e($row['hours'] ?: 0) }}"
                                           style="width:50px; text-align:right;"
                                           @if ($isLocked) disabled="disabled" @endif
                                    />
                                </td>
                                <td style="max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                                    @if (!empty($row['description'])) data-tippy-content="{{ e($row['description']) }}" @endif
                                >{{ e($row['description']) }}</td>
                                <td>
                                    @if ($row['paid'] == '1')
                                        <span class="badge badge-success">{{ __('label.paid') }}</span>
                                    @elseif ($row['invoicedComp'] == '1')
                                        <span class="badge badge-info">{{ __('label.approved') }}</span>
                                    @elseif ($row['invoicedEmpl'] == '1')
                                        <span class="badge badge-warning">{{ __('label.invoiced') }}</span>
                                    @else
                                        <span class="badge">{{ __('label.pending') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- New entry row (always visible at bottom, like week view) --}}
                    <tr class="timesheetRow newEntryRow" id="newEntryRow">
                        <td>
                            <input type="text"
                                   id="newEntryDate"
                                   name="newDate"
                                   placeholder="{{ __('language.dateformat') }}"
                                   autocomplete="off"
                                   style="width:100px" />
                        </td>
                        <td>
                            <div class="form-group" id="projectSelect">
                                <x-globals::forms.select :bare="true" name="projectId" data-placeholder="{{ __('input.placeholders.choose_project') }}" class="project-select" style="width:100%;">
                                    <option value="">{{ __('input.placeholders.choose_project') }}</option>
                                    @foreach ($tpl->get('allProjects') as $projectRow)
                                        {!! sprintf(
                                            $tpl->dispatchTplFilter(
                                                'client_product_format',
                                                '<option value="%s">%s / %s</option>'
                                            ),
                                            ...$tpl->dispatchTplFilter(
                                                'client_product_values',
                                                [
                                                    $projectRow['id'],
                                                    $tpl->escape($projectRow['clientName']),
                                                    $tpl->escape($projectRow['name']),
                                                ]
                                            )
                                        ) !!}
                                    @endforeach
                                </x-globals::forms.select>
                            </div>
                        </td>
                        <td>
                            <div class="form-group" id="ticketSelect">
                                <x-globals::forms.select :bare="true" data-placeholder="{{ __('input.placeholders.choose_todo') }}" class="ticket-select" name="newTicketId" style="width:100%;">
                                    <option value="">{{ __('input.placeholders.choose_todo') }}</option>
                                    @foreach ($tpl->get('allTickets') as $ticketRow)
                                        {!! sprintf(
                                            $tpl->dispatchTplFilter(
                                                'todo_format',
                                                '<option value="%1$s" data-value="%2$s" class="project_%2$s">%1$s / %3$s</option>'
                                            ),
                                            ...$tpl->dispatchTplFilter(
                                                'todo_values',
                                                [
                                                    $ticketRow['id'],
                                                    $ticketRow['projectId'],
                                                    $tpl->escape($ticketRow['headline']),
                                                ]
                                            )
                                        ) !!}
                                    @endforeach
                                </x-globals::forms.select>
                            </div>
                        </td>
                        <td>
                            <x-globals::forms.select :bare="true" class="kind-select" name="newKindId" style="width:100%;">
                                @foreach ($tpl->get('kind') as $key => $kindRow)
                                    <option value="{{ $key }}">{{ __($kindRow) }}</option>
                                @endforeach
                            </x-globals::forms.select>
                        </td>
                        <td>
                            <input type="text"
                                   class="hourCell"
                                   id="newEntryHours"
                                   name="newHours"
                                   value="0"
                                   style="width:50px; text-align:right;" />
                        </td>
                        <td>
                            <input type="text" name="newDescription" placeholder="{{ __('label.description') }}" style="width:100%;" />
                        </td>
                        <td>--</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="font-weight:bold;">
                        <td colspan="4" style="text-align:right; padding-right:10px;">{{ __('label.total') }}</td>
                        <td id="listTotalHours" style="text-align:right; padding-right:12px;">{{ $sum }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
            </div>
            <div class="tw:flex tw:justify-end tw:mt-4">
                <x-globals::forms.button submit type="primary" name="saveTimeSheet" class="saveTimesheetBtn">{{ __('buttons.save') }}</x-globals::forms.button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        leantime.dateController.initDateRangePicker(".dateFrom", ".dateTo", 1);

        // Init Chosen.js for project and ticket selectors
        var chosenTicketOpts = { no_results_text: "No to-dos found for this project" };
        jQuery(".project-select").chosen();
        jQuery(".ticket-select").chosen(chosenTicketOpts);

        // Add Hours button scrolls to and highlights the new entry row
        jQuery("#addHoursBtn").click(function () {
            var $row = jQuery("#newEntryRow");
            jQuery("html, body").animate({ scrollTop: $row.offset().top - 100 }, 200);
            $row.css("background-color", "var(--accent1)");
            setTimeout(function () {
                $row.css("background-color", "");
            }, 800);
            jQuery("#newEntryDate").focus();
        });

        // Project filters ticket list
        jQuery(".project-select").change(function () {
            jQuery(".ticket-select").val("");
            jQuery(".ticket-select option").show();
            jQuery("#ticketSelect .chosen-results li").show();
            var selectedValue = jQuery(this).find("option:selected").val();
            if (selectedValue) {
                jQuery(".ticket-select option").not(".project_" + selectedValue).not('[value=""]').hide();
                jQuery("#ticketSelect .chosen-results li").not(".project_" + selectedValue).hide();
            }
            jQuery(".ticket-select").chosen("destroy").chosen(chosenTicketOpts);
        });

        // Selecting a ticket auto-selects its project
        jQuery(".ticket-select").change(function () {
            var projectId = jQuery(this).find("option:selected").attr("data-value");
            if (projectId) {
                jQuery(".project-select").val(projectId);
                jQuery(".project-select").chosen("destroy").chosen();
                jQuery(".ticket-select").chosen("destroy").chosen(chosenTicketOpts);
            }
        });

        // Date picker for new entry row
        jQuery("#newEntryDate").datepicker({
            dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
            dayNames: leantime.i18n.__("language.dayNames").split(","),
            dayNamesMin: leantime.i18n.__("language.dayNamesMin").split(","),
            dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
            monthNames: leantime.i18n.__("language.monthNames").split(","),
            monthNamesShort: leantime.i18n.__("language.monthNamesShort").split(","),
            currentText: leantime.i18n.__("language.currentText"),
            closeText: leantime.i18n.__("language.closeText"),
            buttonText: leantime.i18n.__("language.buttonText"),
            isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
            nextText: leantime.i18n.__("language.nextText"),
            prevText: leantime.i18n.__("language.prevText"),
            weekHeader: leantime.i18n.__("language.weekHeader"),
            firstDay: 1
        });

        // Live total calculation
        function updateTotal() {
            var total = 0;
            jQuery("#myTimesheetList .hourCell").each(function () {
                var val = parseFloat(jQuery(this).val());
                if (!isNaN(val)) {
                    total = Math.round((total + val) * 100) / 100;
                }
            });
            jQuery("#listTotalHours").text(total);
        }

        jQuery("#myTimesheetList").on("change keyup", ".hourCell", function () {
            updateTotal();
        });
    });
</script>

@php
    $values = $tpl->get('values');
@endphp


<div class="pageheader">
    <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
        <input type="text" name="term"
               placeholder="{{ __('input.placeholders.search_type_hit_enter') }}"/>
    </form>

    <div class="pageicon"><span class="fa-laptop"></span></div>
    <div class="pagetitle">
        <h5>{{ __('OVERVIEW') }}</h5>
        <h1>{{ __('MY_TIMESHEETS') }}</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">


        <div class="fail">
            @if ($tpl->get('info') != '')
                <span class="info">{!! $tpl->displayNotification() !!}</span>
            @endif
        </div>

        <div id="loader">&nbsp;</div>
        <form action="" method="post" class="stdform">

            <div class="row-fluid">
                <div class="span12">


                    <div class="widget">
                        <h4 class="widgettitle">{{ __('OVERVIEW') }}</h4>
                        <div class="widgetcontent" style="min-height: 460px">

                            <label for="clients">{{ __('label.client') }}</label>
                            <select name="clients" id="clients" onchange="filterProjectsByClient();">
                                <option value="all">{{ __('headline.all_clients') }}</option>
                                @foreach ($tpl->get('allClients') as $client)
                                    <option value="{{ $client['id'] }}">{{ $tpl->escape($client['name']) }}</option>
                                @endforeach
                            </select> <br/>

                            <label for="projects">{{ __('PROJECT') }}</label> <select
                                    name="projects" id="projects"
                                    onchange="removeOptions($('select#projects option:selected').val());">


                                <option value="all">{{ __('ALL_PROJECTS') }}</option>

                                <optgroup>
                                    @php
                                        $lastClientName = '';
                                    @endphp
                                    @foreach ($tpl->get('allProjects') as $row)
                                        @php
                                            $currentClientName = $row['clientName'];
                                        @endphp
                                        @if ($currentClientName != $lastClientName)
                                            </optgroup><optgroup label="{{ $currentClientName }}">
                                        @endif

                                        <option value="{{ $row['id'] }}" data-client-id="{{ $row['clientId'] }}"
                                            @if ($row['id'] == $values['project'])
                                                selected="selected"
                                            @endif
                                        >{{ $row['name'] }}</option>

                                        @php
                                            $lastClientName = $row['clientName'];
                                        @endphp
                                    @endforeach
                                </optgroup>
                            </select> <br/>

                            <label for="tickets">{{ __('TICKET') }}</label>
                            <x-global::forms.select name="tickets" id="tickets">

                                @foreach ($tpl->get('allTickets') as $row)
                                    <option class="{{ $row['projectId'] }}" value="{{ $row['projectId'] }}|{{ $row['id'] }}"
                                        @if ($row['id'] == $values['ticket'])
                                            selected="selected"
                                        @endif
                                    >{{ $row['headline'] }}</option>
                                @endforeach

                            </x-global::forms.select> <br/>
                            <br/>
                            <label for="kind">{{ __('KIND') }}</label> <x-global::forms.select id="kind"
                                                                                              name="kind">
                                @foreach ($tpl->get('kind') as $row)
                                    <option value="{{ $row }}"
                                        @if ($row == $values['kind'])
                                            selected="selected"
                                        @endif
                                    >{{ __($row) }}</option>
                                @endforeach

                            </x-global::forms.select><br/>
                            <label for="date">{{ __('DATE') }}</label> <input type="text" autocomplete="off"
                                                                                             id="date" name="date"
                                                                                             value="{{ $values['date'] }}"
                                                                                             size="7"/>
                            <br/>
                            <label for="hours">{{ __('HOURS') }}</label> <x-global::forms.input
                                    name="hours" id="hours"
                                    value="{{ $values['hours'] }}" size="7" /> <br/>
                            <label for="description">{{ __('DESCRIPTION') }}</label> <textarea
                                    rows="5" cols="50" id="description"
                                    name="description">{{ $values['description'] }}</textarea><br/>
                            <br/>
                            <br/>
                            <label for="invoicedEmpl">{{ __('INVOICED') }}</label> <input
                                    type="checkbox" name="invoicedEmpl" id="invoicedEmpl"
                                @if (isset($values['invoicedEmpl']) === true && $values['invoicedEmpl'] == '1')
                                    checked="checked"
                                @endif
                                />
                            {{ __('ONDATE') }}&nbsp;<input type="text"
                                                                          id="invoicedEmplDate" name="invoicedEmplDate"
                                                                          value="{{ $values['invoicedEmplDate'] }}"
                                                                          size="7"/><br/>


                            @if ($login::userIsAtLeast($roles::$manager))
                                <br/>
                                <label for="invoicedComp">{{ __('INVOICED_COMP') }}</label> <input
                                        type="checkbox" name="invoicedComp" id="invoicedComp"
                                    @if ($values['invoicedComp'] == '1')
                                        checked="checked"
                                    @endif
                                    />
                                {{ __('ONDATE') }}&nbsp;<input type="text" autocomplete="off"
                                                                              id="invoicedCompDate"
                                                                              name="invoicedCompDate"
                                                                              value="{{ $values['invoicedCompDate'] }}"
                                                                              size="7"/><br/>

                                <label for="paid">{{ __('labels.paid') }}</label> <input
                                    type="checkbox" name="paid" id="paid"
                                    @if ($values['paid'] == '1')
                                        checked="checked"
                                    @endif
                                    />
                                {{ __('ONDATE') }}&nbsp;<input type="text" autocomplete="off"
                                                                              id="paidDate"
                                                                              name="paidDate"
                                                                              value="{{ $values['paidDate'] }}"
                                                                              size="7"/><br/>



                            @endif
                            <x-global::button submit type="primary" name="save">{{ __('SAVE') }}</x-global::button> <x-global::button submit type="primary" name="saveNew">{{ __('SAVE_NEW') }}</x-global::button>


        </form>
    </div>
</div>


<script type="text/javascript">

        function filterProjectsByClient() {
            var selectedClientId = jQuery('#clients option:selected').val();
            var projectSelect = jQuery('#projects');

            // Show all projects if "all" is selected
            if (selectedClientId === 'all') {
                projectSelect.find('option').show();
                projectSelect.find('optgroup').show();
            } else {
                // Hide all options first
                projectSelect.find('option[data-client-id]').hide();
                projectSelect.find('optgroup').hide();

                // Show only projects matching the selected client
                projectSelect.find('option[data-client-id="' + selectedClientId + '"]').show();
                projectSelect.find('option[data-client-id="' + selectedClientId + '"]').parent('optgroup').show();
            }

            // Reset project selection to "all"
            projectSelect.val('all');
        }

        jQuery("#date, #invoicedCompDate, #invoicedEmplDate, #paidDate").datepicker({

                dateFormat:  leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
                dayNames: leantime.i18n.__("language.dayNames").split(","),
                dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                monthNames: leantime.i18n.__("language.monthNames").split(","),
                currentText: leantime.i18n.__("language.currentText"),
                closeText: leantime.i18n.__("language.closeText"),
                buttonText: leantime.i18n.__("language.buttonText"),
                isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
                nextText: leantime.i18n.__("language.nextText"),
                prevText: leantime.i18n.__("language.prevText"),
                weekHeader: leantime.i18n.__("language.weekHeader"),
                firstDay: leantime.i18n.__("language.firstDayOfWeek"),
            });


</script>

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

                            <x-globals::forms.form-field label-text="{{ __('label.client') }}" name="clients">
                                <x-globals::forms.select :bare="true" name="clients" id="clients" onchange="filterProjectsByClient();">
                                    <option value="all">{{ __('headline.all_clients') }}</option>
                                    @foreach ($tpl->get('allClients') as $client)
                                        <option value="{{ $client['id'] }}">{{ $tpl->escape($client['name']) }}</option>
                                    @endforeach
                                </x-globals::forms.select>
                            </x-globals::forms.form-field>

                            <x-globals::forms.form-field label-text="{{ __('PROJECT') }}" name="projects">
                                <x-globals::forms.select :bare="true" name="projects" id="projects"
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
                                </x-globals::forms.select>
                            </x-globals::forms.form-field>

                            <x-globals::forms.form-field label-text="{{ __('TICKET') }}" name="tickets">
                                <x-globals::forms.select :bare="true" name="tickets" id="tickets">
                                    @foreach ($tpl->get('allTickets') as $row)
                                        <option class="{{ $row['projectId'] }}" value="{{ $row['projectId'] }}|{{ $row['id'] }}"
                                            @if ($row['id'] == $values['ticket'])
                                                selected="selected"
                                            @endif
                                        >{{ $row['headline'] }}</option>
                                    @endforeach
                                </x-globals::forms.select>
                            </x-globals::forms.form-field>

                            <x-globals::forms.form-field label-text="{{ __('KIND') }}" name="kind">
                                <x-globals::forms.select :bare="true" id="kind" name="kind">
                                    @foreach ($tpl->get('kind') as $row)
                                        <option value="{{ $row }}"
                                            @if ($row == $values['kind'])
                                                selected="selected"
                                            @endif
                                        >{{ __($row) }}</option>
                                    @endforeach
                                </x-globals::forms.select>
                            </x-globals::forms.form-field>

                            <x-globals::forms.form-field label-text="{{ __('DATE') }}" name="date">
                                <x-globals::forms.input :bare="true" type="text" autocomplete="off"
                                        id="date" name="date"
                                        value="{{ $values['date'] }}"
                                        size="7"/>
                            </x-globals::forms.form-field>

                            <x-globals::forms.form-field label-text="{{ __('HOURS') }}" name="hours">
                                <x-globals::forms.input :bare="true" name="hours" id="hours"
                                        value="{{ $values['hours'] }}" size="7" />
                            </x-globals::forms.form-field>

                            <x-globals::forms.form-field label-text="{{ __('DESCRIPTION') }}" name="description">
                                <x-globals::forms.textarea name="description" id="description" rows="5"
                                        value="{{ $values['description'] }}" />
                            </x-globals::forms.form-field>

                            <x-globals::forms.form-field label-text="{{ __('INVOICED') }}" name="invoicedEmpl">
                                <div class="tw:flex tw:items-center tw:gap-2">
                                    <x-globals::forms.checkbox name="invoicedEmpl" :checked="isset($values['invoicedEmpl']) && $values['invoicedEmpl'] == '1'" />
                                    {{ __('ONDATE') }}&nbsp;<x-globals::forms.input :bare="true" type="text"
                                            id="invoicedEmplDate" name="invoicedEmplDate"
                                            value="{{ $values['invoicedEmplDate'] }}"
                                            size="7"/>
                                </div>
                            </x-globals::forms.form-field>

                            @if ($login::userIsAtLeast($roles::$manager))
                                <x-globals::forms.form-field label-text="{{ __('INVOICED_COMP') }}" name="invoicedComp">
                                    <div class="tw:flex tw:items-center tw:gap-2">
                                        <x-globals::forms.checkbox name="invoicedComp" :checked="$values['invoicedComp'] == '1'" />
                                        {{ __('ONDATE') }}&nbsp;<x-globals::forms.input :bare="true" type="text" autocomplete="off"
                                                id="invoicedCompDate" name="invoicedCompDate"
                                                value="{{ $values['invoicedCompDate'] }}"
                                                size="7"/>
                                    </div>
                                </x-globals::forms.form-field>

                                <x-globals::forms.form-field label-text="{{ __('labels.paid') }}" name="paid">
                                    <div class="tw:flex tw:items-center tw:gap-2">
                                        <x-globals::forms.checkbox name="paid" :checked="$values['paid'] == '1'" />
                                        {{ __('ONDATE') }}&nbsp;<x-globals::forms.input :bare="true" type="text" autocomplete="off"
                                                id="paidDate" name="paidDate"
                                                value="{{ $values['paidDate'] }}"
                                                size="7"/>
                                    </div>
                                </x-globals::forms.form-field>
                            @endif
                            <x-globals::forms.button submit type="primary" name="save">{{ __('SAVE') }}</x-globals::forms.button> <x-globals::forms.button submit type="primary" name="saveNew">{{ __('SAVE_NEW') }}</x-globals::forms.button>


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

@php
use Leantime\Core\Support\FromFormat;
@endphp

<script type="text/javascript">

    // Reset the project to "all", then narrow the project list to the picked client. (Reset first:
    // setting an enhanced select's value rewrites its options, which would undo the filter.)
    function filterProjectsByClient() {
        var clientId = document.getElementById('clients').value;
        var projectSelect = document.getElementById('projects');

        leantime.selectController.setValue(projectSelect, 'all');

        Array.prototype.forEach.call(projectSelect.querySelectorAll('option[data-client-id]'), function (option) {
            var belongsToClient = clientId === 'all' || option.getAttribute('data-client-id') === clientId;
            option.style.display = belongsToClient ? '' : 'none';
        });
    }

    jQuery(document).ready(function() {
        leantime.timesheetsController.initProjectTicketSync(
            document.getElementById('projects'),
            document.getElementById('tickets')
        );

        jQuery(document).ready(function ($) {
            jQuery("#datepicker, #date, #invoicedCompDate, #invoicedEmplDate, #paidDate").datepicker({
                numberOfMonths: 1,
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
            });
        });
    });
</script>

{!! $tpl->displayNotification() !!}

<h4  class="widgettitle title-light"><span class="fa-regular fa-clock"></span> {!! __('headlines.edit_time') !!}</h4>
<form action="{{ BASE_URL }}/timesheets/editTime/{{ (int) $_GET['id'] }}" method="post" class="editTimeModal">

<label for="clients">{!! __('label.client') !!}</label>
<x-global::forms.select enhanced name="clients" id="clients" class="client-select" onchange="filterProjectsByClient();">
    <option value="all">{!! __('headline.all_clients') !!}</option>
    @foreach ($allClients as $client)
        <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
    @endforeach
</x-global::forms.select> <br />

<label for="projects">{!! __('label.project') !!}</label>
<x-global::forms.select enhanced name="projects" id="projects" class="project-select">
    <option value="all">{!! __('headline.all_projects') !!}</option>

    @foreach ($allProjects as $row)
        <option value="{{ $row['id'] }}" data-client-id="{{ $row['clientId'] }}"
            @if ($row['id'] == $values['project'])
                selected="selected"
            @endif
        >{{ $row['name'] }}</option>
    @endforeach
</x-global::forms.select> <br />

<div id="ticketSelect">
<label for="tickets">{!! __('label.ticket') !!}</label>
<x-global::forms.select enhanced name="tickets" id="tickets" class="ticket-select">

    @foreach ($allTickets as $row)
        <option class="project_{{ $row['projectId'] }}" data-value="{{ $row['projectId'] }}" value="{{ $row['id'] }}"
            @if ($row['id'] == $values['ticket'])
                selected="selected"
            @endif
        >{{ $row['headline'] }}</option>
    @endforeach

</x-global::forms.select> <br />
</div>
    <label for="kind">{!! __('label.kind') !!}</label> <x-global::forms.select id="kind"
    name="kind">
    @foreach ($kind as $key => $row)
        <option value="{{ $key }}"
            @if ($key == $values['kind'])
                selected="selected"
            @endif
        >{!! __($row) !!}</option>
    @endforeach

</x-global::forms.select><br />
<label for="date">{!! __('label.date') !!}</label> <input type="text" autocomplete="off"
    id="datepicker" name="date" value="{{ format(value: $values['date'], fromFormat: FromFormat::DbDate)->date() }}" size="7" />
<br />
<label for="hours">{!! __('label.hours') !!}</label> <x-global::forms.text-input
    id="hours" name="hours"
    value="{{ $values['hours'] }}" size="7" /> <br />
<label for="description">{!! __('label.description') !!}</label> <x-global::forms.textarea
    rows="5" cols="50" id="description" name="description">{{ $values['description'] }}</x-global::forms.textarea><br />




    @if ($login::userIsAtLeast($roles::$manager))
        <input style="float:left; margin-right:5px;"
                type="checkbox" name="invoicedEmpl" id="invoicedEmpl"
            @if (isset($values['invoicedEmpl']) && $values['invoicedEmpl'] == '1')
                checked="checked"
            @endif />

            <label for="invoicedEmpl">{!! __('label.invoiced') !!}</label>

            {!! __('label.date') !!}&nbsp;<input type="text" autocomplete="off"
                                              id="invoicedEmplDate" name="invoicedEmplDate"
                                              value="{{ format(value: $values['invoicedEmplDate'], fromFormat: FromFormat::DbDate)->date() }}"
                                              size="7"/><br/>


        <br/>
        <input style="float:left; margin-right:5px;"
                type="checkbox" name="invoicedComp" id="invoicedComp"
            @if ($values['invoicedComp'] == '1')
                checked="checked"
            @endif />

        <label for="invoicedComp">{!! __('label.invoiced_comp') !!}</label>
        {!! __('label.date') !!}&nbsp;<input type="text" autocomplete="off"
                                                  id="invoicedCompDate"
                                                  name="invoicedCompDate"
                                                  value="{{ format(value: $values['invoicedCompDate'], fromFormat: FromFormat::DbDate)->date() }}"
                                                  size="7"/><br/>

        <br/>
        <input style="float:left; margin-right:5px;"
               type="checkbox" name="paid" id="paid"
            @if ($values['paid'] == '1')
                checked="checked"
            @endif />

        <label for="paid">{!! __('label.paid') !!}</label>
        {!! __('label.date') !!}&nbsp;<input type="text" autocomplete="off"
                                                      id="paidDate"
                                                      name="paidDate"
                                                      value="{{ format(value: $values['paidDate'], fromFormat: FromFormat::DbDate)->date() }}"
                                                      size="7"/><br/>
    @endif



    <input type="hidden" name="saveForm" value="1"/>
    <p class="stdformbutton">
        <x-global::forms.button tag="a" class="delete editTimeModal pull-right" link="{{ BASE_URL }}/timesheets/delTime/{{ $tpl->escape($_GET['id']) }}" state="danger" variant="outline">{!! __('links.delete') !!}</x-global::forms.button>
        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" />
    </p>
</form>

@php
    use Leantime\Core\Support\FromFormat;

    $values = $tpl->get('values');
@endphp
<script type="text/javascript">

    function filterProjectsByClient() {
        var selectedClientId = jQuery('#clients option:selected').val();
        var projectSelect = jQuery('#projects');

        // Show all projects if "all" is selected
        if (selectedClientId === 'all') {
            projectSelect.find('option').show();
        } else {
            // Hide all options first (except the "all" option)
            projectSelect.find('option[data-client-id]').hide();

            // Show only projects matching the selected client
            projectSelect.find('option[data-client-id="' + selectedClientId + '"]').show();
        }

        // Reset project selection to "all" and trigger chosen update
        projectSelect.val('all');
        projectSelect.trigger("chosen:updated");
    }

    jQuery(document).ready(function() {
        jQuery(".client-select").chosen();
        jQuery(".project-select").chosen();
        jQuery(".ticket-select").chosen();

        jQuery(".project-select").change(function () {
            jQuery(".ticket-select").removeAttr("selected");
            jQuery(".ticket-select").val("");
            jQuery(".ticket-select").trigger("liszt:updated");

            jQuery(".ticket-select option").show();
            jQuery("#ticketSelect .chosen-results li").show();

            var selectedValue = jQuery(this).find("option:selected").val();
            jQuery("#ticketSelect .chosen-results li").not(".project_" + selectedValue).hide();
       });

        jQuery(".ticket-select").change(function () {
            var selectedValue = jQuery(this).find("option:selected").attr("data-value");
            jQuery(".project-select option[value=" + selectedValue + "]").attr("selected", "selected");
            jQuery(".project-select").trigger("liszt:updated");
        });

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

<h4  class="widgettitle title-light"><x-global::elements.icon name="schedule" /> {{ __('headlines.edit_time') }}</h4>
<form action="{{ BASE_URL }}/timesheets/editTime/{{ (int) $_GET['id'] }}" method="post" class="editTimeModal">

<x-globals::forms.form-field label-text="{{ __('label.client') }}" name="clients">
    <x-globals::forms.select :bare="true" name="clients" id="clients" class="client-select" onchange="filterProjectsByClient();">
        <option value="all">{{ __('headline.all_clients') }}</option>
        @foreach ($tpl->get('allClients') as $client)
            <option value="{{ $client['id'] }}">{{ $tpl->escape($client['name']) }}</option>
        @endforeach
    </x-globals::forms.select>
</x-globals::forms.form-field>

<x-globals::forms.form-field label-text="{{ __('label.project') }}" name="projects">
    <x-globals::forms.select :bare="true" name="projects" id="projects" class="project-select">
        <option value="all">{{ __('headline.all_projects') }}</option>
        @foreach ($tpl->get('allProjects') as $row)
            <option value="{{ $row['id'] }}" data-client-id="{{ $row['clientId'] }}"
                @if ($row['id'] == $values['project'])
                    selected="selected"
                @endif
            >{{ $row['name'] }}</option>
        @endforeach
    </x-globals::forms.select>
</x-globals::forms.form-field>

<div id="ticketSelect">
    <x-globals::forms.form-field label-text="{{ __('label.ticket') }}" name="tickets">
        <x-globals::forms.select :bare="true" name="tickets" id="tickets" class="ticket-select">
            @foreach ($tpl->get('allTickets') as $row)
                <option class="project_{{ $row['projectId'] }}" data-value="{{ $row['projectId'] }}" value="{{ $row['id'] }}"
                    @if ($row['id'] == $values['ticket'])
                        selected="selected"
                    @endif
                >{{ $row['headline'] }}</option>
            @endforeach
        </x-globals::forms.select>
    </x-globals::forms.form-field>
</div>

<x-globals::forms.form-field label-text="{{ __('label.kind') }}" name="kind">
    <x-globals::forms.select :bare="true" id="kind" name="kind">
        @foreach ($tpl->get('kind') as $key => $row)
            <option value="{{ $key }}"
                @if ($key == $values['kind'])
                    selected="selected"
                @endif
            >{{ __($row) }}</option>
        @endforeach
    </x-globals::forms.select>
</x-globals::forms.form-field>

<x-globals::forms.form-field label-text="{{ __('label.date') }}" name="date">
    <x-globals::forms.input :bare="true" type="text" autocomplete="off"
        id="datepicker" name="date" value="{{ format(value: $values['date'], fromFormat: FromFormat::DbDate)->date() }}" size="7" />
</x-globals::forms.form-field>

<x-globals::forms.form-field label-text="{{ __('label.hours') }}" name="hours">
    <x-globals::forms.input :bare="true" name="hours" id="hours"
        value="{{ $values['hours'] }}" size="7" />
</x-globals::forms.form-field>

<x-globals::forms.form-field label-text="{{ __('label.description') }}" name="description">
    <x-globals::forms.textarea name="description" id="description" rows="5"
        value="{{ $values['description'] }}" />
</x-globals::forms.form-field>

@if ($login::userIsAtLeast($roles::$manager))
    <x-globals::forms.form-field label-text="{{ __('label.invoiced') }}" name="invoicedEmpl">
        <div class="tw:flex tw:items-center tw:gap-2">
            <x-globals::forms.checkbox name="invoicedEmpl" :checked="isset($values['invoicedEmpl']) && $values['invoicedEmpl'] == '1'" />
            {{ __('label.date') }}&nbsp;<x-globals::forms.input :bare="true" type="text" autocomplete="off"
                id="invoicedEmplDate" name="invoicedEmplDate"
                value="{{ format(value: $values['invoicedEmplDate'], fromFormat: FromFormat::DbDate)->date() }}"
                size="7"/>
        </div>
    </x-globals::forms.form-field>

    <x-globals::forms.form-field label-text="{{ __('label.invoiced_comp') }}" name="invoicedComp">
        <div class="tw:flex tw:items-center tw:gap-2">
            <x-globals::forms.checkbox name="invoicedComp" :checked="$values['invoicedComp'] == '1'" />
            {{ __('label.date') }}&nbsp;<x-globals::forms.input :bare="true" type="text" autocomplete="off"
                id="invoicedCompDate" name="invoicedCompDate"
                value="{{ format(value: $values['invoicedCompDate'], fromFormat: FromFormat::DbDate)->date() }}"
                size="7"/>
        </div>
    </x-globals::forms.form-field>

    <x-globals::forms.form-field label-text="{{ __('label.paid') }}" name="paid">
        <div class="tw:flex tw:items-center tw:gap-2">
            <x-globals::forms.checkbox name="paid" :checked="$values['paid'] == '1'" />
            {{ __('label.date') }}&nbsp;<x-globals::forms.input :bare="true" type="text" autocomplete="off"
                id="paidDate" name="paidDate"
                value="{{ format(value: $values['paidDate'], fromFormat: FromFormat::DbDate)->date() }}"
                size="7"/>
        </div>
    </x-globals::forms.form-field>
@endif



    <input type="hidden" name="saveForm" value="1"/>
    <p class="stdformbutton">
        <a class="delete editTimeModal pull-right" href="{{ BASE_URL }}/timesheets/delTime/{{ e($_GET['id']) }}">{{ __('links.delete') }}</a>
        <x-globals::forms.button submit type="primary" name="save">{{ __('buttons.save') }}</x-globals::forms.button>
    </p>
</form>

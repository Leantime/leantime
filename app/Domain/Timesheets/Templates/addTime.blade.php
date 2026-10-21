@extends($layout)
@section('content')

@php
$values = $values ?? [];
@endphp

<x-global::layout.page-header icon="fa fa-laptop" :eyebrow="__('label.overview')">
    {!! __('headline.my_timesheets') !!}
    <x-slot:actions>
        <form action="{{ BASE_URL }}/index.php?act=tickets.showAll" method="post" class="searchbar">
            <x-global::forms.text-input name="term" placeholder="{{ __('input.placeholders.search_type_hit_enter') }}" />
        </form>
    </x-slot:actions>
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>


        <div class="fail">
            @if (($info ?? '') != '')
                <span class="info">{!! $tpl->displayNotification() !!}</span>
            @endif
        </div>

        <div id="loader">&nbsp;</div>
        <form action="" method="post" class="stdform">

            <div class="row-fluid">
                <div class="span12">


                    <x-global::elements.card style="min-height: 460px">


                        <x-slot:title>{!! __('label.overview') !!}</x-slot:title>

                            <x-global::forms.label for="clients">{!! __('label.client') !!}</x-global::forms.label>
                            <x-global::forms.select name="clients" id="clients" onchange="filterProjectsByClient();">
                                <option value="all">{!! __('headline.all_clients') !!}</option>
                                @foreach ($allClients as $client)
                                    <option value="{{ $client['id'] }}">{{ $client['name'] }}</option>
                                @endforeach
                            </x-global::forms.select> <br/>

                            <x-global::forms.label for="projects">{!! __('label.project') !!}</x-global::forms.label> <x-global::forms.select
                                    name="projects" id="projects"
                                    onchange="removeOptions($('select#projects option:selected').val());">


                                <option value="all">{!! __('labels.all_projects') !!}</option>

                                <optgroup>
                                    @php $lastClientName = ''; @endphp
                                    @foreach ($allProjects as $row)
                                        @php $currentClientName = $row['clientName']; @endphp
                                        @if ($currentClientName != $lastClientName)
                                            </optgroup><optgroup label="{{ $currentClientName }}">
                                        @endif

                                        <option value="{{ $row['id'] }}" data-client-id="{{ $row['clientId'] }}"
                                            @if ($row['id'] == $values['project'])
                                                selected="selected"
                                            @endif
                                        >{{ $row['name'] }}</option>

                                        @php $lastClientName = $row['clientName']; @endphp
                                    @endforeach
                                </optgroup>
                            </x-global::forms.select> <br/>

                            <x-global::forms.label for="tickets">{!! __('label.ticket') !!}</x-global::forms.label>
                            <x-global::forms.select name="tickets" id="tickets">

                                @foreach ($allTickets as $row)
                                    <option class="{{ $row['projectId'] }}" value="{{ $row['projectId'] }}|{{ $row['id'] }}"
                                        @if ($row['id'] == $values['ticket'])
                                            selected="selected"
                                        @endif
                                    >{{ $row['headline'] }}</option>
                                @endforeach

                            </x-global::forms.select> <br/>
                            <br/>
                            <x-global::forms.label for="kind">{!! __('label.kind') !!}</x-global::forms.label> <x-global::forms.select id="kind"
                                                                                          name="kind">
                                @foreach ($kind as $row)
                                    <option value="{{ $row }}"
                                        @if ($row == $values['kind'])
                                            selected="selected"
                                        @endif
                                    >{!! __($row) !!}</option>
                                @endforeach

                            </x-global::forms.select><br/>
                            <x-global::forms.label for="date">{!! __('label.date') !!}</x-global::forms.label> <input type="text" autocomplete="off"
                                                                                         id="date" name="date"
                                                                                         value="{{ $values['date'] }}"
                                                                                         size="7"/>
                            <br/>
                            <x-global::forms.label for="hours">{!! __('label.hours') !!}</x-global::forms.label> <x-global::forms.text-input
                                    id="hours" name="hours"
                                    value="{{ $values['hours'] }}" size="7" /> <br/>
                            <x-global::forms.label for="description">{!! __('label.description') !!}</x-global::forms.label> <x-global::forms.textarea
                                    rows="5" cols="50" id="description"
                                    name="description">{{ $values['description'] }}</x-global::forms.textarea><br/>
                            <br/>
                            <br/>
                            <label for="invoicedEmpl">{!! __('label.invoiced') !!}</label> <input
                                    type="checkbox" name="invoicedEmpl" id="invoicedEmpl"
                                @if (isset($values['invoicedEmpl']) && $values['invoicedEmpl'] == '1')
                                    checked="checked"
                                @endif />
                            {!! __('label.ondate') !!}&nbsp;<input type="text"
                                                                      id="invoicedEmplDate" name="invoicedEmplDate"
                                                                      value="{{ $values['invoicedEmplDate'] }}"
                                                                      size="7"/><br/>


                            @if ($login::userIsAtLeast($roles::$manager))
                                <br/>
                                <label for="invoicedComp">{!! __('label.invoiced_comp') !!}</label> <input
                                        type="checkbox" name="invoicedComp" id="invoicedComp"
                                    @if ($values['invoicedComp'] == '1')
                                        checked="checked"
                                    @endif />
                                {!! __('label.ondate') !!}&nbsp;<input type="text" autocomplete="off"
                                                                          id="invoicedCompDate"
                                                                          name="invoicedCompDate"
                                                                          value="{{ $values['invoicedCompDate'] }}"
                                                                          size="7"/><br/>

                                <label for="paid">{!! __('label.paid') !!}</label> <input
                                    type="checkbox" name="paid" id="paid"
                                    @if ($values['paid'] == '1')
                                        checked="checked"
                                    @endif />
                                {!! __('label.ondate') !!}&nbsp;<input type="text" autocomplete="off"
                                                                          id="paidDate"
                                                                          name="paidDate"
                                                                          value="{{ $values['paidDate'] }}"
                                                                          size="7"/><br/>



                            @endif <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="save" /> <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save_and_new')" name="saveNew" />

                    </x-global::elements.card>
                </div>
            </div>
        </form>
    </x-global::layout.content-box>
</div>

@once
@push('scripts')
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
@endpush
@endonce

@endsection

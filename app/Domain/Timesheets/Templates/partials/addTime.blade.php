
<?php
$values = $tpl->get('values');
?>


<div class="pageheader">
    <form action="../../../../../public/index.php" method="post" class="searchbar">
        <input type="text" name="term"
               placeholder="{{ __("input.placeholders.search_type_hit_enter") }}"/>
    </form>

    <div class="pageicon"><span class="fa-laptop"></span></div>
    <div class="pagetitle">
        <h5>{{ __("OVERVIEW") }}</h5>
        <h1>{{ __("MY_TIMESHEETS") }}</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">


        @displayNotification()

        <div id="loader">&nbsp;</div>
        <form action="" method="post" class="stdform">

            <div class="row-fluid">
                <div class="span12">


                    <div class="widget">
                        <h4 class="widgettitle">{{ __("OVERVIEW") }}</h4>
                        <div class="widgetcontent" style="min-height: 460px">


                            <label for="projects">{!! __('PROJECT') !!}</label>
                            <x-global::forms.select name="projects" id="projects" onchange="removeOptions($('select#projects option:selected').val());">
                                <x-global::forms.select.select-option value="all">
                                    {!! __('ALL_PROJECTS') !!}
                                </x-global::forms.select.select-option>
                            
                                @php $lastClientName = ''; @endphp
                                @foreach ($tpl->get('allProjects') as $row)
                                    @if ($row['clientName'] != $lastClientName)
                                        @if ($lastClientName !== '')
                                            </optgroup>
                                        @endif
                                        <optgroup label="{{ $row['clientName'] }}">
                                    @endif
                            
                                    <x-global::forms.select.select-option :value="$row['id']" :selected="$row['id'] == $values['project']">
                                        {!! $row['name'] !!}
                                    </x-global::forms.select.select-option>
                            
                                    @php $lastClientName = $row['clientName']; @endphp
                                @endforeach
                                @if ($lastClientName !== '')
                                    </optgroup>
                                @endif
                            </x-global::forms.select>
                            
                            <br/>

                            <label for="tickets">{!! __('TICKET') !!}</label>
                            <x-global::forms.select name="tickets" id="tickets">
                                @foreach ($tpl->get('allTickets') as $row)
                                    <x-global::forms.select.select-option 
                                        :value="$row['projectId'] . '|' . $row['id']" 
                                        :selected="$row['id'] == $values['ticket']"
                                        :class="$row['projectId']">
                                        {!! $row['headline'] !!}
                                    </x-global::forms.select.select-option>
                                @endforeach
                            </x-global::forms.select>
                            
                            <br/>
                            <br/>
                            <label for="kind">{!! __('KIND') !!}</label>
                            <x-global::forms.select id="kind" name="kind">
                                @foreach ($tpl->get('kind') as $row)
                                    <x-global::forms.select.select-option :value="$row" :selected="$row == $values['kind']">
                                        {!! __($row) !!}
                                    </x-global::forms.select.select-option>
                                @endforeach
                            </x-global::forms.select>
                            <br/>
                            
                            <label for="date">{{ __("DATE") }}</label> <input type="text" autocomplete="off"
                                                                                             id="date" name="date"
                                                                                             value="<?php echo $values['date'] ?>"
                                                                                             size="7"/>
                            <br/>
                            <label for="hours">{{ __("HOURS") }}</label> <input
                                    type="text" id="hours" name="hours"
                                    value="<?php echo $values['hours'] ?>" size="7"/> <br/>
                            <label for="description">{{ __("DESCRIPTION") }}</label> <textarea
                                    rows="5" cols="50" id="description"
                                    name="description"><?php echo $values['description']; ?></textarea><br/>
                            <br/>
                            <br/>

                            <x-global::forms.checkbox
                                name="invoicedEmpl"
                                id="invoicedEmpl"
                                :checked="isset($values['invoicedEmpl']) && $values['invoicedEmpl'] == '1'"
                                labelText="{{ __('INVOICED') }}"
                                labelPosition="left"
                            />
                            {{ __("ONDATE") }}&nbsp;<input type="text"
                                                                          id="invoicedEmplDate" name="invoicedEmplDate"
                                                                          value="<?php echo $values['invoicedEmplDate'] ?>"
                                                                          size="7"/><br/>


                            <?php if ($login::userIsAtLeast($roles::$manager)) {
                                ?> <br/>
                                <x-global::forms.checkbox
                                    name="invoicedComp"
                                    id="invoicedComp"
                                    :checked="$values['invoicedComp'] == '1'"
                                    labelText="{{ __('INVOICED_COMP') }}"
                                    labelPosition="left"
                                />
                                {{ __("ONDATE") }}&nbsp;<input type="text" autocomplete="off"
                                                                              id="invoicedCompDate"
                                                                              name="invoicedCompDate"
                                                                              value="<?php echo $values['invoicedCompDate'] ?>"
                                                                              size="7"/><br/>
                                
                                <x-global::forms.checkbox
                                    name="paid"
                                    id="paid"
                                    :checked="$values['paid'] == '1'"
                                    labelText="{{ __('labels.paid') }}"
                                    labelPosition="left"
                                />

                                {{ __("ONDATE") }}&nbsp;<input type="text" autocomplete="off"
                                                                              id="paidDate"
                                                                              name="paidDate"
                                                                              value="<?php echo $values['paidDate'] ?>"
                                                                              size="7"/><br/>



                            <?php } ?> <input type="submit" value="{{ __("SAVE") }}"
                                              name="save" class="button"/> <input type="submit"
                                                                                  value="{{ __("SAVE_NEW") }}"
                                                                                  name="saveNew" class="button"/>


        </form>
    </div>
</div>


<script type="text/javascript">


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


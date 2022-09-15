<?php
defined('RESTRICTED') or die('Restricted access');

$values = $this->get('values');
?>
<script type="text/javascript">
    function removeOptions(className) {

        if (className != "all") {
            $('select#tickets option').attr('disabled', 'disabled');
            $('select#tickets option').css('display', 'none');

            $('.' + className).removeAttr('disabled');
            $('.' + className).css('display', 'list-item');
        } else {
            $('select#tickets option').css('display', 'list-item');
            $('select#tickets option').removeAttr('disabled');
        }

    }

    $(document).ready(function () {


            $("#date, #invoicedCompDate, #invoicedEmplDate").datepicker({

                dateFormat:  leantime.i18n.__("language.jsdateformat"),
                dayNames: leantime.i18n.__("language.dayNames").split(","),
                dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                monthNames: leantime.i18n.__("language.monthNames").split(","),
                currentText: leantime.i18n.__("language.currentText"),
                closeText: leantime.i18n.__("language.closeText"),
                buttonText: leantime.i18n.__("language.buttonText"),
                isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                nextText: leantime.i18n.__("language.nextText"),
                prevText: leantime.i18n.__("language.prevText"),
                weekHeader: leantime.i18n.__("language.weekHeader"),
                firstDay: leantime.i18n.__("language.firstDayOfWeek"),
            });


        }
    );

</script>


<div class="pageheader">
    <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
        <input type="text" name="term"
               placeholder="<?php echo $this->__('input.placeholders.search_type_hit_enter') ?>"/>
    </form>

    <div class="pageicon"><span class="iconfa-laptop"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('OVERVIEW'); ?></h5>
        <h1><?php echo $this->__('MY_TIMESHEETS'); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">


        <div class="fail"><?php if ($this->get('info') != '') { ?> <span
                    class="info"><?php echo $this->displayNotification() ?></span> <?php
            } ?>

        </div>

        <div id="loader">&nbsp;</div>
        <form action="" method="post" class="stdform">

            <div class="row-fluid">
                <div class="span12">


                    <div class="widget">
                        <h4 class="widgettitle"><?php echo $this->__('OVERVIEW'); ?></h4>
                        <div class="widgetcontent" style="min-height: 460px">


                            <label for="projects"><?php echo $this->__('PROJECT') ?></label> <select
                                    name="projects" id="projects"
                                    onchange="removeOptions($('select#projects option:selected').val());">


                                <option value="all"><?php echo $this->__('ALL_PROJECTS'); ?></option>

                                <optgroup>
                                    <?php foreach ($this->get('allProjects') as $row) {
                                        $currentClientName = $row['clientName'];
                                        if ($currentClientName != $lastClientName) {
                                            echo '</optgroup><optgroup label="' . $currentClientName . '">';
                                        }

                                        echo '<option value="' . $row['id'] . '"';
                                        if ($row['id'] == $values['project']) {
                                            echo ' selected="selected" ';
                                        }
                                        echo '>' . $row['name'] . '</option>';

                                        $lastClientName = $row['clientName'];
                                    }

                                    ?>
                                </optgroup>
                            </select> <br/>

                            <label for="tickets"><?php echo $this->__('TICKET') ?></label>
                            <select name="tickets" id="tickets">

                                <?php foreach ($this->get('allTickets') as $row) {
                                    echo '<option class="' . $row['projectId'] . '" value="' . $row['projectId'] . '|' . $row['id'] . '"';
                                    if ($row['id'] == $values['ticket']) {
                                        echo ' selected="selected" ';
                                    }
                                    echo '>' . $row['headline'] . '</option>';
                                } ?>

                            </select> <br/>
                            <br/>
                            <label for="kind"><?php echo $this->__('KIND') ?></label> <select id="kind"
                                                                                              name="kind">
                                <?php foreach ($this->get('kind') as $row) {
                                    echo '<option value="' . $row . '"';
                                    if ($row == $values['kind']) {
                                        echo ' selected="selected"';
                                    }
                                    echo '>' . $this->__($row) . '</option>';

                                } ?>

                            </select><br/>
                            <label for="date"><?php echo $this->__('DATE') ?></label> <input type="text" autocomplete="off"
                                                                                             id="date" name="date"
                                                                                             value="<?php echo $values['date'] ?>"
                                                                                             size="7"/>
                            <br/>
                            <label for="hours"><?php echo $this->__('HOURS') ?></label> <input
                                    type="text" id="hours" name="hours"
                                    value="<?php echo $values['hours'] ?>" size="7"/> <br/>
                            <label for="description"><?php echo $this->__('DESCRIPTION') ?></label> <textarea
                                    rows="5" cols="50" id="description"
                                    name="description"><?php echo $values['description']; ?></textarea><br/>
                            <br/>
                            <br/>
                            <label for="invoicedEmpl"><?php echo $this->__('INVOICED') ?></label> <input
                                    type="checkbox" name="invoicedEmpl" id="invoicedEmpl"
                                <?php if (isset($values['invoicedEmpl']) === true && $values['invoicedEmpl'] == '1') {
                                    echo ' checked="checked"';
                                } ?> />
                            <?php echo $this->__('ONDATE') ?>&nbsp;<input type="text"
                                                                          id="invoicedEmplDate" name="invoicedEmplDate"
                                                                          value="<?php echo $values['invoicedEmplDate'] ?>"
                                                                          size="7"/><br/>


                            <?php if ($login::userIsAtLeast($roles::$manager)) { ?> <br/>
                                <label for="invoicedComp"><?php echo $this->__('INVOICED_COMP') ?></label> <input
                                        type="checkbox" name="invoicedComp" id="invoicedComp"
                                    <?php if ($values['invoicedComp'] == '1') {
                                        echo ' checked="checked"';
                                    } ?> />
                                <?php echo $this->__('ONDATE') ?>&nbsp;<input type="text" autocomplete="off"
                                                                              id="invoicedCompDate"
                                                                              name="invoicedCompDate"
                                                                              value="<?php echo $values['invoicedCompDate'] ?>"
                                                                              size="7"/><br/>
                            <?php } ?> <input type="submit" value="<?php echo $this->__('SAVE'); ?>"
                                              name="save" class="button"/> <input type="submit"
                                                                                  value="<?php echo $this->__('SAVE_NEW'); ?>"
                                                                                  name="saveNew" class="button"/>


        </form>
    </div>
</div>

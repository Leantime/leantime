<?php

defined('RESTRICTED') or exit('Restricted access');

use Leantime\Core\Support\FromFormat;

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$values = $tpl->get('values');
?>
<script type="text/javascript">

    jQuery(document).ready(function() {
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
        jQuery("#editTimeForm").on("submit", function(e) {
            e.preventDefault();
        jQuery.post(jQuery(this).attr("action"), jQuery(this).serialize(), function() {
            if (window.parent && window.parent.jQuery) {
                window.parent.location.reload();
            }
        });
    });
});

</script>

<?php echo $tpl->displayNotification() ?>
<h4  class="widgettitle title-light"><span class="fa-regular fa-clock"></span> <?php echo $tpl->__('headlines.edit_todo'); ?></h4>
<form action="<?= BASE_URL?>/timesheets/editTime/<?= (int) $_GET['id']?>" method="post" id="editTimeForm">

<label for="projects" ><?php echo $tpl->__('label.project')?></label>
<select name="projects" id="projects" class="project-select">
    <option value="all"><?php echo $tpl->__('headline.all_projects'); ?></option>

    <?php foreach ($tpl->get('allProjects') as $row) {
        echo '<option value="'.$row['id'].'"';
        if ($row['id'] == $values['project']) {
            echo ' selected="selected" ';
        }
        echo '>'.$row['name'].'</option>';
    }

?>
</select> <br />

<div id="ticketSelect">
<label for="tickets"><?php echo $tpl->__('label.ticket')?></label>
<select name="tickets" id="tickets" class="ticket-select">

    <?php foreach ($tpl->get('allTickets') as $row) {
        echo '<option class="project_'.$row['projectId'].'" data-value="'.$row['projectId'].'" value="'.$row['id'].'"';
        if ($row['id'] == $values['ticket']) {
            echo ' selected="selected" ';
        }
        echo '>'.$row['headline'].'</option>';
    } ?>

</select> <br />
</div>
    <label for="kind" ><?php echo $tpl->__('label.kind')?></label> <select id="kind"
    name="kind">
    <?php
    foreach ($tpl->get('kind') as $key => $row) {
        echo '<option value="'.$key.'"';
        if ($key == $values['kind']) {
            echo ' selected="selected"';
        }
        echo '>'.$tpl->__($row).'</option>';
    }
?>

</select><br />
<label for="date"><?php echo $tpl->__('label.date')?></label> <input type="text" autocomplete="off"
    id="datepicker" name="date" value="<?php echo format(value: $values['date'], fromFormat: FromFormat::DbDate)->date(); ?>" size="7" />
<br />
<label for="hours" ><?php echo $tpl->__('label.hours')?></label> <input
    type="text" id="hours" name="hours"
    value="<?php echo $values['hours'] ?>" size="7" /> <br />
<label for="description"><?php echo $tpl->__('label.description')?></label> <textarea
    rows="5" cols="50" id="description" name="description"><?php echo $values['description']; ?></textarea><br />




    <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
        <input style="float:left; margin-right:5px;"
                type="checkbox" name="invoicedEmpl" id="invoicedEmpl"
            <?php if (isset($values['invoicedEmpl']) === true && $values['invoicedEmpl'] == '1') {
                echo ' checked="checked"';
            } ?> />

            <label for="invoicedEmpl" ><?php echo $tpl->__('label.invoiced') ?></label>

            <span><?php echo $tpl->__('label.date') ?></span><br><input type="text" autocomplete="off" style="margin-top:5px;"
                                                  id="invoicedEmplDate" name="invoicedEmplDate"
                                                  value="<?php echo format(value: $values['invoicedEmplDate'], fromFormat: FromFormat::DbDate)->date(); ?>"
                                                  size="7"/><br/>


        <br/>
        <input style="float:left; margin-right:5px;"
                type="checkbox" name="invoicedComp" id="invoicedComp"
            <?php if ($values['invoicedComp'] == '1') {
                echo ' checked="checked"';
            } ?> />

        <label for="invoicedComp" ><?php echo $tpl->__('label.invoiced_comp') ?></label>
        <span><?php echo $tpl->__('label.date') ?></span><br><input type="text" autocomplete="off" style="margin-top:5px;"
                                                      id="invoicedCompDate"
                                                      name="invoicedCompDate"
                                                      value="<?php echo format(value: $values['invoicedCompDate'], fromFormat: FromFormat::DbDate)->date(); ?>"
                                                      size="7"/><br/>

        <br/>
        <input style="float:left; margin-right:5px;"
               type="checkbox" name="paid" id="paid"
            <?php if ($values['paid'] == '1') {
                echo ' checked="checked"';
            } ?> />

        <label for="paid"><?php echo $tpl->__('label.paid') ?></label>
        <span ><?php echo $tpl->__('label.date') ?></span><br><input type="text" autocomplete="off" style="margin-top:5px;"
                                                          id="paidDate"
                                                          name="paidDate"
                                                          value="<?php echo format(value: $values['paidDate'], fromFormat: FromFormat::DbDate)->date(); ?>"
                                                          size="7"/><br/>
    <?php } ?>



    <input type="hidden" name="saveForm" value="1"/>
    <p class="stdformbutton">
        <a class="delete editTimeModal pull-right" style ="border-radius:16px; background-color:red !important; padding:5px;" href="<?= BASE_URL?>/timesheets/delTime/<?= $tpl->escape($_GET['id']) ?>"><span style = "color:white;font-weight:700;padding:4px 10px;"><?= $tpl->__('links.delete') ?></span></a>
        <input type="submit" value="<?php echo $tpl->__('buttons.save'); ?>" name="save" class="button" />
    </p>
</form>
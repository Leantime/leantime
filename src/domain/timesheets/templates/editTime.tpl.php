<?php

defined('RESTRICTED') or die('Restricted access');
$helper = $this->get('helper');
$values = $this->get('values');
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
            jQuery("#ticketSelect .chzn-results li").show();
            var selectedValue = jQuery(this).find("option:selected").val();
            jQuery("#ticketSelect .chzn-results li").not(".project_" + selectedValue).hide();


        });

        jQuery(".ticket-select").change(function () {

            var selectedValue = jQuery(this).find("option:selected").attr("data-value");
            jQuery(".project-select option[value=" + selectedValue + "]").attr("selected", "selected");
            jQuery(".project-select").trigger("liszt:updated");
        });

        jQuery(document).ready(function ($) {
            jQuery("#datepicker").datepicker({
                numberOfMonths: 1,
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
            });
        });

    });

</script>


<?php echo $this->displayNotification() ?>

<h4  class="widgettitle title-light"><span class="iconfa-time"></span> <?php echo $this->__('headlines.edit_time'); ?></h4>
<form action="<?=BASE_URL?>/timesheets/editTime/<?=(int)$_GET['id']?>" method="post" class="editTimeModal">

<label for="projects"><?php echo $this->__('label.project')?></label>
<select name="projects" id="projects" class="project-select">
    <option value="all"><?php echo $this->__('headline.all_projects'); ?></option>

    <?php foreach($this->get('allProjects') as $row) {
        echo'<option value="'.$row['id'].'"';
        if($row['id'] == $values['project']) { echo' selected="selected" ';
        }
        echo'>'.$row['name'].'</option>';
    }

    ?>
</select> <br />

<div id="ticketSelect">
<label for="tickets"><?php echo $this->__('label.ticket')?></label>
<select name="tickets" id="tickets" class="ticket-select">

    <?php foreach($this->get('allTickets') as $row) { 
        echo'<option class="project_'.$row['projectId'].'" data-value="'.$row["projectId"].'" value="'.$row['id'].'"';
        if($row['id'] == $values['ticket']) { echo' selected="selected" ';
        }
        echo'>'.$row['headline'].'</option>';
    } ?>
    
</select> <br />
</div>
    <label for="kind"><?php echo $this->__('label.kind')?></label> <select id="kind"
    name="kind">
    <?php foreach($this->get('kind') as $key => $row){
        echo'<option value="'.$key.'"';
        if($row == $values['kind']) { echo ' selected="selected"';
        }
        echo'>'.$this->__($row).'</option>';

    }
    ?>

</select><br />
<label for="date"><?php echo $this->__('label.date')?></label> <input type="text"
    id="datepicker" name="date" value="<?php echo $this->getFormattedDateString($values['date']); ?>" size="7" />
<br />
<label for="hours"><?php echo $this->__('label.hours')?></label> <input
    type="text" id="hours" name="hours"
    value="<?php echo $values['hours'] ?>" size="7" /> <br />
<label for="description"><?php echo $this->__('label.description')?></label> <textarea
    rows="5" cols="50" id="description" name="description"><?php echo $values['description']; ?></textarea><br />




    <?php if ($login::userIsAtLeast("clientManager")) { ?>

        <input style="float:left; margin-right:5px;"
                type="checkbox" name="invoicedEmpl" id="invoicedEmpl"
            <?php if (isset($values['invoicedEmpl']) === true && $values['invoicedEmpl'] == '1') {
                echo ' checked="checked"';
            } ?> />

            <label for="invoicedEmpl"><?php echo $this->__('label.invoiced') ?></label>

            <?php echo $this->__('label.date') ?>&nbsp;<input type="text"
                                                  id="invoicedEmplDate" name="invoicedEmplDate"
                                                  value="<?php echo ($values['invoicedEmplDate'] != "") ? $this->getFormattedDateString($values['invoicedEmplDate']) : ""; ?>"
                                                  size="7"/><br/>


        <br/>
        <input style="float:left; margin-right:5px;"
                type="checkbox" name="invoicedComp" id="invoicedComp"
            <?php if ($values['invoicedComp'] == '1') {
                echo ' checked="checked"';
            } ?> />

        <label for="invoicedComp"><?php echo $this->__('label.invoiced_comp') ?></label>
        <?php echo $this->__('label.date') ?>&nbsp;<input type="text"
                                                      id="invoicedCompDate"
                                                      name="invoicedCompDate"
                                                      value="<?php echo ($values['invoicedCompDate'] != "") ? $this->getFormattedDateString($values['invoicedCompDate']) : ""; ?>"
                                                      size="7"/><br/>
    <?php } ?>



    <input type="hidden" name="saveForm" value="1"/>
    <p class="stdformbutton">
        <?php echo $this->displayLink('timesheets.delTime', $this->__('links.delete'), array('id' => $_GET['id']), array('class'=>'delete editTimeModal pull-right')); ?>
        <input type="submit" value="<?php echo $this->__('buttons.save'); ?>"
        name="save" class="button" /></fieldset>
    </p>
</form>





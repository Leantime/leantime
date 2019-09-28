<?php
defined('RESTRICTED') or die('Restricted access');
$values = $this->get('values');
$helper = $this->get('helper');
?>

<script type="text/javascript">
    $(function() {
        $("#dateFrom, #dateTo").datepicker({
            minDate: +1, 
            maxDate: '+1Y', 
            dateFormat: 'dd.mm.yy',
            dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            dayNamesMin:  ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            monthNames: ['Januar','Februar','M&auml;rz','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember']
        });
        
    });
</script>

<h1>Neuen Termin hinzufügen</h1>

<?php if($this->get('info') != '') { ?>
<div class="fail"><span class="info"><?php echo $this->get('info'); ?></span>
</div>
<?php } ?>

<form action="" method="post">

<fieldset>
<legend>Neuer Termin</legend>


    <label for="description">Titel:</label><br />
    <input type="text" id="description" name="description" value="<?php echo $values['description']; ?>" /><br />
    
    <label for="dateFrom">Start am:</label><br />
    <input type="text" id="dateFrom" name=dateFrom value="<?php echo $helper->timestamp2date($values['dateFrom'], 2); ?>" /><br />
    um <br />
    <input type="text" id="timeFrom" name="timeFrom" value="<?php echo $helper->timestamp2date($values['dateFrom'], 1); ?>"/>
    <small>(hh:mm)</small>
    <br />
    <label for="dateTo">Ende am:</label><br />
    <input type="text" id="dateTo" name=dateTo value="<?php echo $helper->timestamp2date($values['dateTo'], 2); ?>" /><br />
    um <br />
    <input type="text" id="timeTo" name="timeTo" value="<?php echo $helper->timestamp2date($values['dateTo'], 1); ?>"/>
    <small>(hh:mm)</small>
    <br />
    <label for="allDay">Ganztägig</label><br />
    <input type="checkbox" id="allDay" name="allDay" 
    <?php if($values['allDay'] === 'true') {
        echo 'checked="checked" ';
    }?>
    /><br />
<input type="submit" name="save" id="save" value="Speichern" class="button" />
</fieldset>
</form>
<br />
<a href="index.php?act=calendar.showMyCalendar" class="link">zurück</a>

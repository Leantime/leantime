<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$values = $this->get('values');

?>

<link rel='stylesheet' type='text/css' href='includes/libs/fullCalendar/fullcalendar.css' />
<h1>Google Kalender bearbeiten</h1>

<?php if($this->get('info') != ''){ ?>
<div class="fail"><span class="info"><?php echo $this->get('info'); ?></span>
</div>
<?php } ?>

<form action="" method="post">

<fieldset class="left">
	<legend>Google Kalender bearbeiten</legend> 

	<label for="name">Bezeichnung</label>
	<input type="text" id="name" name="name" value="<?php echo $values['name']; ?>" /><br />
	
	<label for="url">URL:</label>
	<input type="text" id="url" name="url" style="width:300px;" value="<?php echo $values['url']; ?>" /><br />
	
	<label for="color">Farbe:</label>
	<select name="color" id="color">
		<option value="c0033ff" class="c0033ff" <?php if($values['colorClass'] == 'c0033ff') echo 'selected="selected"';?>>0033ff</option>
		<option value="c0099ff" class="c0099ff" <?php if($values['colorClass'] == 'c0099ff') echo 'selected="selected"';?>>0099ff</option>
		<option value="c00ffff" class="c00ffff" <?php if($values['colorClass'] == 'c00ffff') echo 'selected="selected"';?>>00ffff</option>
		<option value="c3333ff" class="c3333ff" <?php if($values['colorClass'] == 'c3333ff') echo 'selected="selected"';?>>3333ff</option>
		<option value="c3399ff" class="c3399ff" <?php if($values['colorClass'] == 'c3399ff') echo 'selected="selected"';?>>3399ff</option>
		<option value="c33ffff" class="c33ffff" <?php if($values['colorClass'] == 'c33ffff') echo 'selected="selected"';?>>33ffff</option>
		
		<option value="c6633ff" class="c6633ff" <?php if($values['colorClass'] == 'c6633ff') echo 'selected="selected"';?>>6633ff</option>
		<option value="c6699ff" class="c6699ff" <?php if($values['colorClass'] == 'c6699ff') echo 'selected="selected"';?>>6699ff</option>
		<option value="c66ffff" class="c66ffff" <?php if($values['colorClass'] == 'c66ffff') echo 'selected="selected"';?>>66ffff</option>
		<option value="c9933ff" class="c9933ff" <?php if($values['colorClass'] == 'c9933ff') echo 'selected="selected"';?>>9933ff</option>
		<option value="c9999ff" class="c9999ff" <?php if($values['colorClass'] == 'c9999ff') echo 'selected="selected"';?>>9999ff</option>
		<option value="c99ffff" class="c99ffff" <?php if($values['colorClass'] == 'c99ffff') echo 'selected="selected"';?>>99ffff</option>
		<option value="ccc33ff" class="ccc33ff" <?php if($values['colorClass'] == 'ccc33ff') echo 'selected="selected"';?>>cc33ff</option>
		<option value="ccc99ff" class="ccc99ff" <?php if($values['colorClass'] == 'ccc99ff') echo 'selected="selected"';?>>cc99ff</option>
		<option value="cccffff" class="cccffff" <?php if($values['colorClass'] == 'cccffff') echo 'selected="selected"';?>>ccffff</option>
		<option value="cff33ff" class="cff33ff" <?php if($values['colorClass'] == 'cff33ff') echo 'selected="selected"';?>>ff33ff</option>
		<option value="cff99ff" class="cff99ff" <?php if($values['colorClass'] == 'cff99ff') echo 'selected="selected"';?>>ff99ff</option>
		<option value="cffffff" class="cffffff" <?php if($values['colorClass'] == 'cffffff') echo 'selected="selected"';?>>ffffff</option>
	</select>
	
	
<input type="submit" name="save" id="save" value="Speichern" class="button" /></fieldset>

<fieldset>
<legend>Bearbeiten</legend>
<p><a href="index.php?act=calendar.delGCal&amp;id=<?php echo htmlentities($_GET['id']); ?>">Kalender löschen</a></p>
</fieldset>

<fieldset>
<legend>Hilfe</legend>
Gehen Sie folgendermaßen vor, um die Privatadresse für Ihren Kalender zu ermitteln:
<br /><br />
   <br />1. Klicken Sie auf der linken Seite in der Kalenderliste neben dem entsprechenden Kalender auf den Abwärtspfeil und wählen Sie Kalendereinstellungen aus.
   <br /><br />2. Klicken Sie auf der Seite "Einstellungen" des gewünschten Kalenders auf die Schaltfläche XML. Daraufhin wird ein Pop-up-Fenster mit der privaten URL Ihres Kalenders angezeigt.
   <br /><br />3. Über diese URL können Sie auf Ihre Kalenderdaten zugreifen.

</fieldset>

</form>

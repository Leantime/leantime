<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$values = $this->get('values');

?>


<div class="pageheader">
            <form action="<?=BASE_URL ?>/index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $this->__('OVERVIEW'); ?></h5>
                <h1><?php echo $this->__('GOOGLE_CALENDAR_IMPORT'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php echo $this->displayNotification() ?>

<form action="" method="post">


	<label for="name"><?php echo $this->__('DESCRIPTION') ?>:</label>
	<input type="text" id="name" name="name" value="<?php echo $values['name']; ?>" /><br />
	
	<label for="url"><?php echo $this->__('URL') ?>:</label>
	<input type="text" id="url" name="url" style="width:300px;" value="<?php echo $values['url']; ?>" /><br />
	
	<label for="color"><?php echo $this->__('COLOR') ?>:</label>
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
	</select><br/>
	
	
<input type="submit" name="save" id="save" value="<?php echo $this->__('SAVE') ?>" class="button" />
<br/><br/>
<h3><?php echo $this->__('EXPLANATION') ?></h3>

<p><?php echo $this->__('FOLLOW_STEPS') ?></p><br/>
<p>1. <?php echo $this->__('STEP_ONE_IMPORT') ?></p>
<p>2. <?php echo $this->__('STEP_TWO_IMPORT') ?></p>
<p>3. <?php echo $this->__('STEP_THREE_IMPORT') ?></p>

</form>

<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$values = $this->get('values');
?>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('CLIENT_DETAILS'); ?></h5>
                <h1><?php echo $language->lang_echo('NEW_CLIENT'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

			<?php echo $this->displayNotification() ?>

<div class="widget">
   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
   <div class="widgetcontent">
   	
<form action="" method="post" class="stdform">
	
	<label
		for="name"><?php echo $language->lang_echo('NAME') ?></label> <input type="text"
		name="name" id="name" value="<?php echo $values['name'] ?>" /><br />
		
	<label
		for="email"><?php echo $language->lang_echo('EMAIL') ?></label> <input type="text"
		name="email" id="email" value="<?php echo $values['email'] ?>" /><br />
	
	<label for="internet"><?php echo $language->lang_echo('URL') ?></label> <input
		type="text" name="internet" id="internet"
		value="<?php echo $values['internet'] ?>" /><br />
			
	<label for="street"><?php echo $language->lang_echo('STREET') ?></label> <input
		type="text" name="street" id="street"
		value="<?php echo $values['street'] ?>" /><br />
	
	<label for="zip"><?php echo $language->lang_echo('ZIP') ?></label> <input type="text"
		name="zip" id="zip" value="<?php echo $values['zip'] ?>" /><br />
	
	<label for="city"><?php echo $language->lang_echo('CITY') ?></label> <input type="text"
		name="city" id="city" value="<?php echo $values['city'] ?>" /><br />
	
	<label for="state"><?php echo $language->lang_echo('STATE') ?></label> <input
		type="text" name="state" id="state"
		value="<?php echo $values['state'] ?>" /><br />
	
	<label for="country"><?php echo $language->lang_echo('COUNTRY') ?></label> <input
		type="text" name="country" id="country"
		value="<?php echo $values['country'] ?>" /><br />
	
	<label for="phone"><?php echo $language->lang_echo('PHONE') ?></label> <input
		type="text" name="phone" id="phone"
		value="<?php echo $values['phone'] ?>" /><br />
	
	<p class="stdformbutton">
		<input type="submit" name="save" id="save" value="<?php echo $language->lang_echo('SAVE') ?>" class="button" /></fieldset>
		<input type="reset" class="btn" value="<?php echo $language->lang_echo('RESET_BUTTON') ?>" />
	</p>
	
</form>

</div>
</div>
</div>
</div>
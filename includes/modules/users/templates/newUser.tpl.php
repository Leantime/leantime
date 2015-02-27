<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' ); 
$roles = $this->get('roles');
$values = $this->get('values');
?>

<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('USER_DETAILS'); ?></h5>
                <h1><?php echo $language->lang_echo('NEW_USER'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

			<?php echo $this->displayNotification() ?>
			
<div class="widget">
   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
   <div class="widgetcontent">
   	
<form action="" method="post" class="stdform">

<label for="firstname"><?php echo $language->lang_echo('FIRSTNAME'); ?></label> <input
	type="text" name="firstname" id="firstname"
	value="<?php echo $values['firstname'] ?>" /><br />

<label for="lastname"><?php echo $language->lang_echo('LASTNAME'); ?></label> <input
	type="text" name="lastname" id="lastname"
	value="<?php echo $values['lastname'] ?>" /><br />

<label for="user"><?php echo $language->lang_echo('EMAIL'); ?></label> <input
	type="text" name="user" id="user" value="<?php echo $values['user'] ?>" /><br />

<label for="phone"><?php echo $language->lang_echo('PHONE'); ?></label> <input
	type="text" name="phone" id="phone"
	value="<?php echo $values['phone'] ?>" /><br />
	
<label for="role"><?php echo $language->lang_echo('ROLE'); ?></label> 
<select name="role" id="role">
	<?php foreach($roles as $role){ ?>

		<option value="<?php echo $role['id']; ?>" title="<?php echo $role['roleDescription']; ?>">
			<?php echo $role['roleDescription']; ?>			
		</option>

	<?php } ?>
</select> <br />

<label for="client"><?php echo $language->lang_echo('CLIENT') ?></label>
<select name='client' id="cliet">
	<?php foreach($this->get('clients') as $client): ?>
		<option value="<?php echo $client['id'] ?>"><?php echo $client['name'] ?></option>
	<?php endforeach; ?>
</select><br/>

<label for="password"><?php echo $language->lang_echo('PASSWORD'); ?></label> <input
	type="password" name="password" id="password" value="" /><br />

<label for="password2"><?php echo $language->lang_echo('PASSWORD2'); ?></label> <input
	type="password" name="password2" id="password2" value="" /><br />

<p class="stdformbutton">
	<input type="submit" name="save" id="save" value="<?php echo $language->lang_echo('SAVE'); ?>" class="button" />
</p>

</form>

</div>
</div>

</div>
</div>

<?php

	defined( 'RESTRICTED' ) or die( 'Restricted access' );
	$account = $this->get('account');
	$values = $this->get('values');
	$helper = $this->get('helper');

?>

<h1>Edit Account</h1>

<div class="fail"><?php if($this->get('info') != ''){ ?> <span
	class="info"><?php echo $this->get('info') ?></span> <?php } ?>

</div>

<form action='' method='POST'>
	<fieldset class="left"><legend>Overview</legend>
		<?php foreach ($this->get('account') as $row) { ?>
		<label for="firstname">Lastname</label> <input
			type="text" name="firstname" id="firstname"
			value="<?php echo $row['firstname'] ?>" /><br />
		
		<label for="lastname">Firstname</label> <input
			type="text" name="lastname" id="lastname"
			value="<?php echo $row['lastname'] ?>" /><br />
		
		<label for="user">Email</label> <input
			type="text" name="user" id="user" value="<?php echo $row['user'] ?>" /><br />
		
		<label for="phone">Phone<?php echo $lang['PHONE']; ?></label> <input
			type="text" name="phone" id="phone"
			value="<?php echo $row['phone'] ?>" /><br />
		
		<label for="currentPassword">Current Password</label><input type='password' 
			value="" name="currentPassword" id="password" /><br />
			
		<label for="oldPassword">New Password</label><input type='password' 
			value="" name="newPassword" id="password" /><br />
		
		<label for="newPassword">Confirm New Password</label><input type="password" 
			value="" name="confirmPassword" id="password" /><br />
		
		<input type='submit' value='Save' id='save' name='save' />
		
		<?php } ?>
	</fieldset>
</form>

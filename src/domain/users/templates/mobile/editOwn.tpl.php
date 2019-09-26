<?php
defined('RESTRICTED') or die('Restricted access');
$roles = $this->get('roles');
$values = $this->get('values');
?>

<h1><?php echo $lang['EDIT_MY_DATA']?></h1>

<div class="fail"><?php if($this->get('info') != '') { ?> <span
    class="info"><?php echo $lang[$this->get('info')] ?></span> <?php 
                    } ?>

</div>

<form action="" method="post">
<fieldset><legend><?php echo $lang['USER_DETAILS']; ?></legend> <label
    for="firstname"><?php echo $lang['LASTNAME']; ?></label> <br /><input
    type="text" name="firstname" id="firstname"
    value="<?php echo $values['firstname'] ?>" /><br />

<label for="lastname"><?php echo $lang['FIRSTNAME']; ?></label><br /> <input
    type="text" name="lastname" id="lastname"
    value="<?php echo $values['lastname'] ?>" /><br />

<label for="user"><?php echo $lang['EMAIL']; ?></label> <br /><input
    type="text" name="user" id="user" value="<?php echo $values['user'] ?>" /><br />

<label for="phone"><?php echo $lang['PHONE']; ?></label><br /> <input
    type="text" name="phone" id="phone"
    value="<?php echo $values['phone'] ?>" /><br />

<br />
<label for="password"><?php echo $lang['PASSWORD']; ?></label><br /> <input
    type="password" name="password" id="password" value="" /><br />

<label for="password2"><?php echo $lang['PASSWORD2']; ?></label><br /> <br /><input
    type="password" name="password2" id="password2" value="" /><br />

<input type="submit" name="save" id="save"
    value="<?php echo $lang['SAVE']; ?>" class="button" /></fieldset>
</form>

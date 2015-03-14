<?php defined( 'RESTRICTED' ) or die( 'Restricted access' ); ?>

<img src="<?php echo $this->get('profilePicture'); ?>" alt="Picture of <?php $this->get('userName'); ?>" class="profilePicture"/>

<div class="userinfo">
	<h5><?php echo $this->get('userName'); ?> - <small><?php echo $this->get('userEmail'); ?></small></h5>
	<ul>
		<li><?php echo $this->displayLink('users.editOwn',$language->lang_echo('EDITPROFILE')); ?></li>
        <li><?php echo $this->displayLink('general.logout',$language->lang_echo('LOGOUT')); ?></a></li>
     </ul>
</div>
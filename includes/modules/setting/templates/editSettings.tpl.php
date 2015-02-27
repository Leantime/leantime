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
                <h5><?php echo $language->lang_echo('SETTINGS'); ?></h5>
                <h1><?php echo $language->lang_echo('SYSTEM_SETTINGS') ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<?php if($this->get('info') != ''){ ?>
	<div class="fail">
		<span class="info"><?php echo $language->lang_echo($this->get('info')); ?></span>
	</div>
<?php } ?>

<form action="" method="post">

	<strong><?php echo $language->lang_echo('GENERAL') ?></strong><br /><br />
	<label><?php echo $language->lang_echo('SITE_NAME') ?></label>
	<input type="text" name="sitename" value="<?php echo $values['sitename']; ?>"/><br />

	<strong><?php echo $language->lang_echo('DATABASE_SETTINGS') ?></strong><br /><br />
	<label><?php echo $language->lang_echo('HOST') ?></label>
	<input type="text" name="host" value="<?php echo $values['dbHost']; ?>"/><br />
	<label><?php echo $language->lang_echo('USERNAME') ?></label>
	<input type="text" name="username" value="<?php echo $values['dbUser']; ?>"/><br />
	<label><?php echo $language->lang_echo('DATABASE') ?></label>
	<input type="text" name="database" value="<?php echo $values['dbDatabase']; ?>"/><br />
	<label><?php echo $language->lang_echo('PASSWORD') ?></label>
	<input type="password" name="password" value="<?php echo $values['dbPassword']; ?>"/><br />
	<br />
	
	<strong><?php echo $language->lang_echo('UPLOAD_SETTINGS') ?></strong><br /><br />
	<label><?php echo $language->lang_echo('PATH_ON_SERVER') ?></label>
	<input type="text" name="path" value="<?php echo $values['userFilePath']; ?>"/><br /><br />
	<label><?php echo $language->lang_echo('MAX_FILE_SIZE') ?></label>
	<input type="text" name="filesize" value="<?php echo $values['maxFileSize']; ?>"/><br />
	<br />
	
	<strong><?php echo $language->lang_echo('EMAIL') ?></strong><br /><br />
	<label><?php echo $language->lang_echo('SENDER') ?></label>
	<input type="text" name="email" value="<?php echo $values['email']; ?>"/><br />
	<br />
	
	<strong><?php echo $language->lang_echo('ADMINISTRATOR') ?></strong><br /><br />
	<label><?php echo $language->lang_echo('FIRSTNAME') ?></label>
	<input type="text" name="adminFirstname" value="<?php echo $values['adminFirstname']; ?>"/><br />
	<label><?php echo $language->lang_echo('LASTNAME') ?></label>
	<input type="text" name="adminLastname" value="<?php echo $values['adminLastname']; ?>"/><br />
	<label><?php echo $language->lang_echo('USERNAME') ?></label>
	<input type="text" name="adminUserName" value="<?php echo $values['adminUserName']; ?>"/><br />
	<label><?php echo $language->lang_echo('PASSWORD') ?></label>
	<input type="password" name="adminUserPassword" value="<?php echo $values['adminUserPassword']; ?>"/><br />
	<label><?php echo $language->lang_echo('EMAIL') ?></label>
	<input type="text" name="adminEmail" value="<?php echo $values['adminEmail']; ?>"/><br />
	
	<input type="submit" name="save" class="button" value="<?php echo $language->lang_echo('SAVE') ?>"/>

</form>

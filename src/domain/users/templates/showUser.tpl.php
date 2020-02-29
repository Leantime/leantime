<?php
defined('RESTRICTED') or die('Restricted access');
$user = $this->get('user');
$role = $this->get('roles');
?>

<div class="pageheader">
            
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1><?php echo $user['lastname'] . " " . $user['firstname']; ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">


<p><strong><?php echo $language->lang_echo('NAME'); ?>:</strong> <?php echo ''.$user['lastname'].', '.$user['firstname'].''; ?></p>
<p><strong><?php echo $language->lang_echo('EMAIL'); ?>:</strong> <?php echo $user['username']; ?></p>
<p><strong><?php echo $language->lang_echo('PHONE'); ?>:</strong> <?php echo $user['phone']; ?></p>
<p><strong><?php echo $language->lang_echo('ROLE'); ?>:</strong> <?php echo $role['roleDescription']; ?></p><br/>
<hr />

<div class='box-right'>
    <?php $file = '/userdata/'.$user['profileImg']; ?>
    
    <?php if(file_exists($_SERVER['DOCUMENT_ROOT'].$file) && $user['profileImg']!='') { ?>
        <img src='<?php echo $file; ?>' class='profileImg' width='150px'
        alt='<?php echo $_SESSION['userdata']['firstname']." ".$_SESSION['userdata']['lastname']; ?>s profile pic' />
    <?php } else { ?>
        <img src='<?=BASE_URL ?>/userdata/default.png' class='profileImg' width='150px'
        alt='<?php echo $language->lang_echo('Default_Profile_Pic'); ?>' />        
    <?php } ?>
</div>

</div>
</div>

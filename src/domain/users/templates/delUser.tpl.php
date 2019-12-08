<?php
defined('RESTRICTED') or die('Restricted access');
$user = $this->get('user');
?>

<div class="pageheader">
           
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1><?php printf("".$this->__('DELETE_USER')."", $user['firstname'], $user['lastname']); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
                
                <h4 class="widget widgettitle"><?php echo $lang['CONFIRM_DELETE']; ?></h4>
                <div class="widgetcontent">
                    


<?php if($this->get('msg') === '') { ?>

<form method="post" accept-charset="utf-8">
<fieldset><legend><?php echo $this->__('CONFIRM_DELETE'); ?></legend>
<p><?php echo $this->__('CONFIRM_DELETE_QUE'); ?><br />
</p>
<input type="submit" value="<?php echo $this->__('DELETE'); ?>" name="del"
    class="button"></fieldset>
</form>

<?php }else{ ?>

<span class="info"><?php echo $this->__($this->get('msg')); ?></span>

<?php } ?>
</div></div>

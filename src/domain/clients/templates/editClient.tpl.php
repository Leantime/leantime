<?php
defined('RESTRICTED') or die('Restricted access');
$values = $this->get('values');
?>

<div class="pageheader">
           
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1><?php echo $this->__('EDIT_CLIENT'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

                <?php echo $this->displayNotification() ?>

<form action="" method="post" class="stdform">
    
<div class="widget">
   <h4 class="widgettitle"><?php echo $this->__('OVERVIEW'); ?></h4>
   <div class="widgetcontent">    
    
    <label for="name"><?php echo $this->__('NAME') ?></label>
    <input type="text" name="name" id="name" value="<?php echo $values['name'] ?>" /><br />
    
    <label for="email"><?php echo $this->__('EMAIL') ?></label>
    <input type="text" name="email" id="email" value="<?php echo $values['email'] ?>" /><br />
    
    <label for="internet"><?php echo $this->__('URL') ?></label> <input
        type="text" name="internet" id="internet"
        value="<?php echo $values['internet'] ?>" /><br />
    
    <label for="street"><?php echo $this->__('STREET') ?></label> <input
        type="text" name="street" id="street"
        value="<?php echo $values['street'] ?>" /><br />
    
    <label for="zip"><?php echo $this->__('ZIP') ?></label> <input type="text"
        name="zip" id="zip" value="<?php echo $values['zip'] ?>" /><br />
    
    <label for="city"><?php echo $this->__('CITY') ?></label> <input type="text"
        name="city" id="city" value="<?php echo $values['city'] ?>" /><br />
    
    <label for="state"><?php echo $this->__('STATE') ?></label> <input
        type="text" name="state" id="state"
        value="<?php echo $values['state'] ?>" /><br />
    
    <label for="country"><?php echo $this->__('COUNTRY') ?></label> <input
        type="text" name="country" id="country"
        value="<?php echo $values['country'] ?>" /><br />
    
    <label for="phone"><?php echo $this->__('PHONE') ?></label> <input
        type="text" name="phone" id="phone"
        value="<?php echo $values['phone'] ?>" /><br />
    
    <input type="submit" name="save" id="save"
        value="<?php echo $this->__('SAVE') ?>" class="button" />

    </div>
</div>

</form>

</div>
</div>

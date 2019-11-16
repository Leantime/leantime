<?php

defined('RESTRICTED') or die('Restricted access');
$values = $this->get('values');
$helper = $this->get('helper');
?>

<script type="text/javascript">
        jQuery(document).ready(function() { 
            jQuery("#datepicker").datepicker();
            jQuery("#datepicker2").datepicker();
            jQuery('#timepicker').timepicker();
            jQuery('#timepicker2').timepicker();
        }); 
        
</script>

<div class="pageheader">
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $this->__('OVERVIEW'); ?></h5>
                <h1><?php echo $this->__('NEW_EVENT'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        
        <div class="maincontent">
            <div class="maincontentinner">

    <?php echo $this->displayNotification() ?>
             <div class="widget">
                <h4 class="widgettitle"><?php echo $this->__('EVENT'); ?></h4>
                <div class="widgetcontent">

                
        
                <form action="" method="post" class='stdform'> 
                
                    <label for="description"><?php echo $this->__('TITLE') ?>:</label>
                    <input type="text" id="description" name="description" value="<?php echo $values['description']; ?>" /><br />
                    
                    <div class="par">
                        <label for="dateFrom"><?php echo $this->__('START') ?>:</label>
                        <input type="text" id="datepicker" name="dateFrom" value="" /><br/>
                    </div>
                    <div class="par">
                        <label for=""><?php echo $this->__('AT') ?></label>
                        <div class="input-append bootstrap-timepicker">
                                <input type="text" id="timepicker" name="timeFrom" value="" />
                                <span class="add-on"><i class="iconfa-time"></i></span>
                           </div>
                    </div>
                    <div class="par">
                        <label for="dateTo"><?php echo $this->__('END') ?>:</label>
                        <input type="text" id="datepicker2" name="dateTo" value="" /><br/>
                    </div>
                    <div class="par">
                        <label for=""><?php echo $this->__('AT') ?> </label>
                        <div class="input-append bootstrap-timepicker">
                                <input type="text" id="timepicker2" name="timeTo" value="" />
                                <span class="add-on"><i class="iconfa-time"></i></span>
                           </div>
                    </div>
                    
                    <label for="allDay"><?php echo $this->__('ALL_DAY') ?>:</label>
                    <input type="checkbox" id="allDay" name="allDay" 
        <?php if($values['allDay'] === 'true') {
            echo 'checked="checked" ';
        }?>
                    /><br />
                    
                    <p class="stdformbutton">
                        <input type="submit" name="save" id="save" value="<?php echo $this->__('SAVE') ?>" class="button" />
                        <input type="reset" class="btn" value="<?php echo $this->__('RESET_BUTTON') ?>" />
                    </p>
                
                </form>
                
                </div>
              </div>

            </div>
        </div>
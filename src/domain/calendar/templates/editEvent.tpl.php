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
                <h1><?php echo $this->__('EDIT_EVENT'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

                <?php echo $this->displayNotification() ?>
                 <div class="widget">
                    <h4 class="widgettitle"><?php echo $this->__('EVENT'); ?></h4>
                    <div class="widgetcontent">

                
                <form action="" method="post">
                
                
                    <label for="description"><?php echo $this->__('TITLE') ?>:</label>
                    <input type="text" id="description" name="description" value="<?php echo $values['description']; ?>" /><br />
                    
                    <label for="dateFrom"><?php echo $this->__('START') ?>:</label>
                    <input type="text" id="datepicker" name="dateFrom" value="<?php echo $helper->timestamp2date($values['dateFrom'], 2); ?>" />
        <?php echo $this->__('AT') ?>
                    <div class="par">
                        <div class="input-append bootstrap-timepicker">
                                <input type="text" id="timepicker" name="timeFrom" value="" />
                                <span class="add-on"><i class="iconfa-time"></i></span>
                           </div>
                    </div>
                    <small>(hh:mm)</small><br />
                    
                    <label for="dateTo"><?php echo $this->__('END') ?>:</label>
                    <input type="text" id="datepicker2" name="dateTo" value="<?php echo $helper->timestamp2date($values['dateTo'], 2); ?>" />
        <?php echo $this->__('AT') ?>
                    <div class="par">
                        <div class="input-append bootstrap-timepicker">
                                <input type="text" id="timepicker2" name="timeTo" value="" />
                                <span class="add-on"><i class="iconfa-time"></i></span>
                           </div>
                    </div>
                    <small>(hh:mm)</small><br />
                    
                    <label for="allDay"><?php echo $this->__('ALL_DAY') ?></label>
                    <input type="checkbox" id="allDay" name="allDay" 
        <?php if($values['allDay'] === 'true') {
            echo 'checked="checked" ';
        }?>
                    /><br />
                    <input type="submit" name="save" id="save" value="<?php echo $this->__('SAVE') ?>" class="button" />
                    
                    <div>
                        
        <?php echo $this->displayLink('calendar.delEvent', $this->__('DELETE_EVENT'), array('id' => (int)$_GET['id'])); ?>
                    </div>
                
                </form>
                </div>
                </div>

        </div>
    </div>

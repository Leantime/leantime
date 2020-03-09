<?php

defined('RESTRICTED') or die('Restricted access');
$values = $this->get('values');
$helper = $this->get('helper');
?>

<div class="pageheader">
            
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('headline.calendar'); ?></h5>
        <h1><?php echo $this->__('headline.new_event'); ?></h1>
    </div>
</div><!--pageheader-->
        
        
<div class="maincontent">
    <div class="maincontentinner">

    <?php echo $this->displayNotification() ?>
     <div class="widget">
        <h4 class="widgettitle"><?php echo $this->__('subtitles.event'); ?></h4>
        <div class="widgetcontent">

            <form action="" method="post" class='stdform'>

                <label for="description"><?php echo $this->__('label.title') ?></label>
                <input type="text" id="description" name="description" value="<?php echo $values['description']; ?>" /><br />

                <div class="par">
                    <label for="dateFrom"><?php echo $this->__('label.start_date') ?></label>
                    <input type="text" id="event_date_from" name="dateFrom" value="" /><br/>
                </div>
                <div class="par">
                    <label for=""><?php echo $this->__('label.start_time') ?></label>
                    <div class="input-append bootstrap-timepicker">
                            <input type="text" id="event_time_from" name="timeFrom" value="" />
                       </div>
                </div>
                <div class="par">
                    <label for="dateTo"><?php echo $this->__('label.end_date') ?></label>
                    <input type="text" id="event_date_to" name="dateTo" value="" /><br/>
                </div>
                <div class="par">
                    <label for=""><?php echo $this->__('label.end_time') ?> </label>
                    <div class="input-append bootstrap-timepicker">
                            <input type="text" id="event_time_to" name="timeTo" value="" />
                       </div>
                </div>

                <label for="allDay"><?php echo $this->__('label.all_day') ?></label>
                <input type="checkbox" id="allDay" name="allDay"
                <?php if($values['allDay'] === 'true') {
                    echo 'checked="checked" ';
                }?>
                /><br />

                <p class="stdformbutton">
                    <input type="submit" name="save" id="save" value="<?php echo $this->__('buttons.save') ?>" class="button" />
                </p>
            </form>

        </div>
      </div>

    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });
</script>
<?php
defined('RESTRICTED') or die('Restricted access');
$values = $this->get('values');
?>

<?php $this->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $this->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa <?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('headline.calendar'); ?></h5>
        <h1><?php echo $this->__('headline.new_event'); ?></h1>
    </div>
    <?php $this->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $this->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">
        <?php echo $this->displayNotification() ?>
         <div class="widget">
            <h4 class="widgettitle"><?php echo $this->__('subtitles.event'); ?></h4>
            <div class="widgetcontent">


                <form action="" method="post">

                    <?php $this->dispatchTplEvent('afterFormOpen'); ?>

                    <label for="description"><?php echo $this->__('label.title') ?></label>
                    <input type="text" id="description" name="description" value="<?php echo $values['description']; ?>" /><br />

                    <label for="dateFrom"><?php echo $this->__('label.start_date') ?></label>
                    <input type="text" id="event_date_from" autocomplete="off" name="dateFrom" value="<?php echo $this->getFormattedDateString($values['dateFrom']); ?>" />

                    <div class="par">
                        <label> <?php echo $this->__('label.start_time') ?></label>
                        <div class="input-append bootstrap-timepicker">
                                <input type="time" id="event_time_from" name="timeFrom" value="<?php echo $this->get24HourTimestring($values['dateFrom']); ?>" />
                           </div>
                    </div>

                    <label for="dateTo"><?php echo $this->__('label.end_date') ?></label>
                    <input type="text" id="event_date_to" autocomplete="off" name="dateTo" value="<?php echo $this->getFormattedDateString($values['dateTo']); ?>" />

                    <div class="par">
                        <label for=""><?php echo $this->__('label.end_time') ?> </label>
                        <div class="input-append bootstrap-timepicker">
                                <input type="time" id="event_time_to" name="timeTo" value="<?php echo $this->get24HourTimestring($values['dateTo']); ?>" />
                           </div>
                    </div>

                    <label for="allDay"><?php echo $this->__('label.all_day') ?></label>
                    <input type="checkbox" id="allDay" name="allDay"
                    <?php if ($values['allDay'] === 'true') {
                        echo 'checked="checked" ';
                    }?>
                    />

                    <?php $this->dispatchTplEvent('beforeSubmitButton'); ?>

                    <div class="clear"></div>
                    <br />
                    <a href="<?=BASE_URL?>/calendar/delEvent/<?=(int)$_GET['id'] ?>" class="delete right"><i class="fa fa-trash"></i> <?=$this->__('links.delete')?></a>
                    <input type="submit" name="save" id="save" value="<?php echo $this->__('buttons.save') ?>" class="button" />

                    <div class="clear"></div>

                    <?php $this->dispatchTplEvent('beforeFormClose'); ?>

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

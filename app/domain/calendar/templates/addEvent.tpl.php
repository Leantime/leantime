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

            <form action="" method="post" class='stdform'>

                <?php $this->dispatchTplEvent('afterFormOpen'); ?>

                <label for="description"><?php echo $this->__('label.title') ?></label>
                <input type="text" id="description" name="description" value="<?php $this->e($values['description']); ?>" /><br />

                <div class="par">
                    <label for="dateFrom"><?php echo $this->__('label.start_date') ?></label>
                    <input type="text" id="event_date_from" name="dateFrom" value="" autocomplete="off" /><br/>
                </div>
                <div class="par">
                    <label for=""><?php echo $this->__('label.start_time') ?></label>
                    <div class="input-append bootstrap-timepicker">
                            <input type="time" id="event_time_from" name="timeFrom" value="" />
                       </div>
                </div>
                <div class="par">
                    <label for="dateTo"><?php echo $this->__('label.end_date') ?></label>
                    <input type="text" id="event_date_to" name="dateTo" value="" autocomplete="off" /><br/>
                </div>
                <div class="par">
                    <label for=""><?php echo $this->__('label.end_time') ?> </label>
                    <div class="input-append bootstrap-timepicker">
                            <input type="time" id="event_time_to" name="timeTo" value="" />
                       </div>
                </div>

                <label for="allDay"><?php echo $this->__('label.all_day') ?></label>
                <input type="checkbox" id="allDay" name="allDay"
                <?php if ($values['allDay'] === 'true') {
                    echo 'checked="checked" ';
                }?>
                /><br />

                <?php $this->dispatchTplEvent('beforeSubmitButton'); ?>

                <p class="stdformbutton">
                    <input type="submit" name="save" id="save" value="<?php echo $this->__('buttons.save') ?>" class="button" />
                </p>

                <?php $this->dispatchTplEvent('beforeFormClose'); ?>

            </form>

        </div>
      </div>

    </div>
</div>

<script type="text/javascript">

    <?php $this->dispatchTplEvent('scripts.afterOpen'); ?>

    jQuery(document).ready(function() {
        leantime.calendarController.initEventDatepickers();
    });

    <?php $this->dispatchTplEvent('scripts.beforeClose'); ?>

</script>

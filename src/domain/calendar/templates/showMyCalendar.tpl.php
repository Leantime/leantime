<?php
    defined('RESTRICTED') or die('Restricted access');
?>

<div class="pageheader">

    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('headline.calendar'); ?></h5>
        <h1><?php echo $this->__('headline.my_calendar'); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayLink(
            'calendar.addEvent',
            "<i class='iconfa-plus'></i> ".$this->__('buttons.add_event'),
            null,
            array('class'=>'btn btn-primary btn-rounded')
        ) ?>

        <div id="calendar"></div>

    </div>
</div>


<script type='text/javascript'>

    jQuery(document).ready(function() {


        var events=[<?php foreach($this->get('calendar') as $calendar): ?>
            {
                title: <?php echo json_encode($calendar['title']); ?>,
                start: new Date(<?php echo
                    $calendar['dateFrom']['y'].','.
                    ($calendar['dateFrom']['m'] - 1).','.
                    $calendar['dateFrom']['d'].','.
                    $calendar['dateFrom']['h'].','.
                    $calendar['dateFrom']['i'] ?>),
                <?php if(isset($calendar['dateTo'])) : ?>
                end: new Date(<?php echo
                    $calendar['dateTo']['y'].','.
                    ($calendar['dateTo']['m'] - 1).','.
                    $calendar['dateTo']['d'].','.
                    $calendar['dateTo']['h'].','.
                    $calendar['dateTo']['i'] ?>),
                <?php endif; ?>
                <?php if ((isset($calendar['allDay']) && $calendar['allDay'] == 1)) : ?>
                allDay: true,
                <?php else: ?>
                allDay: false,
                <?php endif; ?>
                <?php if(isset($calendar['eventType']) && $calendar['eventType'] == 'calendar') : ?>
                url: '<?=BASE_URL ?>/calendar/editEvent/<?php echo $calendar['id'] ?>',
                color: '#00814A'
                <?php else: ?>
                url: '<?=BASE_URL ?>/tickets/showTicket/<?php echo $calendar['id'] ?>?projectId=<?php echo $calendar['projectId'] ?>',
                color:'#BC3600'
                <?php endif; ?>
            },
            <?php endforeach; ?>];

        leantime.calendarController.initCalendar(events);

    });

</script>
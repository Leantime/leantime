<script type='text/javascript'>

        jQuery(document).ready(function() {
        
            var date = new Date();
            var d = date.getDate();
            var m = date.getMonth();
            var y = date.getFullYear();
            
            var heightWindow = jQuery(".mainwrapper").height() - 400;
            
            var calendar = jQuery('#calendar').fullCalendar({
                height: heightWindow,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay,listDay'
                },
                buttonText: {
                    prev: '&laquo;',
                    next: '&raquo;',
                    prevYear: '&nbsp;&lt;&lt;&nbsp;',
                    nextYear: '&nbsp;&gt;&gt;&nbsp;',
                    today: 'today',
                    month: 'month',
                    week: 'week',
                    day: 'day'
                },
                select: function(start, end, allDay) {
                    var title = prompt('Event Title:');
                    if (title) {
                        calendar.fullCalendar('renderEvent',
                            {
                                title: title,
                                start: start,
                                end: end,
                                allDay: allDay
                            },
                            true // make the event "stick"
                        );
                    }
                    calendar.fullCalendar('unselect');
                },
                events: [
                    <?php foreach($this->get('calendar') as $calendar): ?>
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
                            url: leantime.appUrl+'/calendar/editEvent/<?php echo $calendar['id'] ?>',
                            color: '#00814A'
        <?php else: ?>
                            url: leantime.appUrl+'/tickets/showTicket/<?php echo $calendar['id'] ?>',
                            color:'#BC3600'
        <?php endif; ?>
                        },
                    <?php endforeach; ?>
                ],
                eventColor: '#0866c6'
            });
            
        });

</script>


<div class="pageheader">
                        
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('CALENDAR'); ?></h5>
                <h1><?php echo $language->lang_echo('MY_CALENDAR'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

                <?php echo $this->displayLink(
                    'calendar.addEvent',
                    "<i class='iconfa-plus'></i> ".$language->lang_echo('NEW_EVENT'),
                    null,
                    array('class'=>'btn btn-primary btn-rounded')
                ) ?>
                                                    
                <div id="calendar"></div>

            </div>
        </div>

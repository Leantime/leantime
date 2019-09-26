<script type='text/javascript'>
        jQuery(document).ready(function() {
        
            var date = new Date();
            var d = date.getDate();
            var m = date.getMonth();
            var y = date.getFullYear();
            
            var calendar = jQuery('#calendar').fullCalendar({
                header: {
                    left: 'prev,next', // today',
                    center: 'title',
                    right: ''
//                    right: 'month,agendaWeek,agendaDay'
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
                selectable: true,
                selectHelper: true,
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
                editable: true,
                events: [
                    <?php foreach($this->get('calendar') as $calendar): ?>
                        {
                        <?php if(isset($calendar['dateTo'])) : ?>
                            title: "<?php echo $calendar['title'] ?>",
        <?php else: ?>
                            title: " ",
        <?php endif; ?>
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
                        <?php if (isset($calendar['allDay']) && $calendar['allDay'] == 0) : ?>
                            allDay: false,
                        <?php endif; ?>
                        <?php if(isset($calendar['dateTo'])) : ?>
                             url: '/calendar/editEvent/<?php echo $calendar['id'] ?>'
        <?php else: ?>
                             url: '/tickets/showTicket/<?php echo $calendar['id'] ?>'
        <?php endif; ?>
                        },
                    <?php endforeach; ?>
                ]
            });
            
        });    
</script>

<div id='calendar'>&nbsp;</div>

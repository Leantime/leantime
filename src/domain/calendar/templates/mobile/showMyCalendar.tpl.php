

<?php


?>


<link rel='stylesheet' type='text/css' href='includes/libs/fullCalendar/fullcalendar.css' />

<script type='text/javascript' src='includes/libs/jquery-ui-1.8.6.custom.min.js'></script>
<script type='text/javascript' src='includes/libs/fullCalendar/fullcalendar.min.js'></script>
<script type='text/javascript' src='includes/libs/fullCalendar/gcal.js'></script>

<style type="text/css">

#calendar div.fc-state-default span{
    padding:10px;
}

</style>

<script type='text/javascript'>

    $(document).ready(function() {
    
        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();
        
        $('#calendar').fullCalendar({
            height:750,
            buttonText: {
                prev:     '&nbsp;&#9668;&nbsp;',  // left triangle
                next:     '&nbsp;&#9658;&nbsp;',  // right triangle
                prevYear: '&nbsp;&lt;&lt;&nbsp;', // <<
                nextYear: '&nbsp;&gt;&gt;&nbsp;', // >>
                today:    'Heute',
                month:    'Monat',
                week:     'Woche',
                day:      'Tag'
            },
            columnFormat: {
                month: 'ddd',    // Mon
                week: 'ddd, d.M.', // Mon 9/7
                day: 'dddd, d.M.'  // Monday 9/7
            },

            axisFormat: 'H:mm',            
            allDayText: 'Ganztägig',
            monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli',
             'August', 'September', 'Oktober', 'November', 'Dezember'],

            monthNamesShort: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun',
                              'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
                              
            dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch',
                       'Donnerstag', 'Freitag', 'Samstag'],

            dayNamesShort: ['So', 'Mo', 'Di', 'Mi',
                                   'Do', 'Fr', 'Sa'],
                                         
            header: {
                left: 'prev,today,next',
                center: '',
                right: 'agendaDay,agendaWeek'
            },
            editable: false,
            defaultView: 'agendaDay', 
            minTime:'7:00',
            maxTime:'19:00',
            eventSources: 
            [
            
            $.fullCalendar.gcalFeed("http://www.google.com/calendar/feeds/de.german%23holiday%40group.v.calendar.google.com/public/basic",
                {
                    className: 'holidays'
                        
                }
            ),
                
    <?php 
            
    foreach($this->get('gCalLink') as $row)  { ?>

            
            
                $.fullCalendar.gcalFeed("<?php echo trim($row['url']); ?>",
                    {
                        className: '<?php echo $row['colorClass']; ?>'
                    }),
            
    <?php } ?>
            
                [               
                <?php 
                $i =0;
                foreach($this->get('dates') as $row){
                    $i++;
                    echo'{
						title: "'.$row['description'].'",
						start: "'.$row['dateFrom'].'",
						end: "'.$row['dateTo'].'",
						allDay: '.$row['allDay'].', 
						url: "index.php?act=calendar.editEvent&id='.$row['id'].'"
					}';
                    
                    echo',';
                    

                }
                
                ?>

                <?php 
                $i =0;
                foreach($this->get('ticketWishDates') as $row){
                    $i++;
                    echo'{
							title: "#'.$row['id'].' '.$row['headline'].'",
								start: "'.$row['dateToFinish'].'",
								allDay: true, 
								url: "index.php?act=tickets.showTicket&id='.$row['id'].'",
								className: "wish"
							}';
                            
                    echo',';
                            

                }
                        
                ?>
        <?php 
                                $i =0;
        foreach($this->get('ticketEditDates') as $row){
            $i++;
            echo'{
											title: "#'.$row['id'].' '.$row['headline'].'",
												start: "'.$row['editFrom'].'",
												end: "'.$row['editTo'].'",
												allDay: true, 
												url: "index.php?act=tickets.showTicket&id='.$row['id'].'",
												className: "editing"
											}';
            if($i < count($this->get('ticketEditDates'))) {
                echo',';
            }

        }
                                        
        ?>
                ]
            ],
             timeFormat: 'H:mm'
            
                
        });
        
    });

</script>

<h1>Mein Kalender</h1>


    
<div id="calendar"></div>
    
<fieldset>
<legend>Legende</legend>
<span class="fc-event" style="padding:3px;">zypro Kalender</span><br /><br />
<span class="wish" style="padding:3px;">Tickets Wunschtermin</span><br /><br />
<span class="editing" style="padding:3px;">Tickets Bearbeitung</span><br /><br />
<span class="holidays" style="padding:3px;">Feiertage</span><br /><br />
<?php 
foreach($this->get('gCalLink') as $row)  { ?>

<span class="<?php echo $row['colorClass']?>" style="padding:3px;"><?php echo $row['name']?>&nbsp;</span><br /><br />
            
            

            
<?php } ?>



</fieldset>

<a href="index.php?act=calendar.addEvent" class="link">Neuer Termin</a>
<a href="index.php?act=calendar.showAllGCals" class="link">Google Kalender importieren</a>

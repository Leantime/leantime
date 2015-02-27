<script type='text/javascript'>

		jQuery(document).ready(function() {
		
			var date = new Date();
			var d = date.getDate();
			var m = date.getMonth();
			var y = date.getFullYear();
			
			var calendar = jQuery('#calendar').fullCalendar({
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'month,agendaWeek,agendaDay'
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
							title: "<?php echo $calendar['title'] ?>",
							start: new Date(<?php echo
									$calendar['dateFrom']['y'].','.
									($calendar['dateFrom']['m'] - 1).','.
									$calendar['dateFrom']['d'].','.
									$calendar['dateFrom']['h'].','.
									$calendar['dateFrom']['i'] ?>),
							<?php if(isset($calendar['dateTo'])): ?>
							end: new Date(<?php echo
									$calendar['dateTo']['y'].','.
									($calendar['dateTo']['m'] - 1).','.
									$calendar['dateTo']['d'].','.
									$calendar['dateTo']['h'].','.
									$calendar['dateTo']['i'] ?>),
							<?php endif; ?>
							<?php if (isset($calendar['dateTo']) || (isset($calendar['allDay']) && $calendar['allDay'] == 0)): ?>
							allDay: false,
							<?php else: ?>
							allDay: true,
							<?php endif; ?>
							<?php if(isset($calendar['dateTo'])): ?>
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


<div class="pageheader">
            <form action="index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('CALENDAR'); ?></h5>
                <h1><?php echo $language->lang_echo('MY_CALENDAR'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

				<?php echo $this->displayLink('calendar.addEvent',
													$language->lang_echo('NEW_EVENT'),
													NULL,
													array('class'=>'btn btn-primary btn-rounded')) ?>
													
				<?php echo $this->displayLink('calendar.showAllGCals',
													$language->lang_echo('GOOGLE_CALENDAR_IMPORT'),
													NULL,
													array('class'=>'btn btn-primary btn-rounded')) ?>
					

				<div id="calendar"></div>

				<div class='box-right'>
					<h3><?php echo $language->lang_echo('EDIT') ?></h3>
					
					<span class="fc-event" style="padding:3px;"><?php echo $lang['SYSTEM_CALENDAR']; ?></span><br /><br />
					<span class="wish" style="padding:3px;"><?php echo $lang['TICKETS_WISHDATES']; ?></span><br /><br />
					<span class="editing" style="padding:3px;"><?php echo $lang['TICKETS_WORKING']; ?></span><br /><br />
					<span class="holidays" style="padding:3px;"><?php echo $lang['HOLIDAYS']; ?></span><br /><br />
					
					<?php foreach($this->get('gCalLink') as $row): ?>
						<span class="<?php echo $row['colorClass']?>" style="padding:3px;"><?php echo $row['name']?>&nbsp;</span><br /><br />		
					<?php endforeach; ?>
				</div>

			</div>
		</div>

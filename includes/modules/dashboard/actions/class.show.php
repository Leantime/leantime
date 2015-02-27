<?php

class show extends dashboard {
	
	/**
	 * @return unknown_type
	 */
	public function run(){
		
		$tpl = new template();
		$helper = new helper();
		
		if (!$this->userHasWidgets($_SESSION['userdata']['id'])) 
			$this->setDefaultWidgets($_SESSION['userdata']['id']);
		
		// CALENDAR
		$calendar = new calendar();
		$tpl->assign('calendar', $calendar->getCalendar($_SESSION['userdata']['id']));
		
		// TICKETS
		$tickets = new tickets();
		$tpl->assign('myTickets', $tickets->getUserTickets(3, $_SESSION['userdata']['id']));
		
		// PROJECTS
		$projects = new projects();
		$allProjects = $projects->getAll(false,5);
		$myProjects = array();
		
		foreach ($allProjects as $project) {
			$opentickets = $projects->getOpenTickets($project['id']);
			$closedTickets = $project['numberOfTickets']-$opentickets['openTickets'];

			if ($project['numberOfTickets'] != 0) 
				$projectPercentage = round($closedTickets/$project['numberOfTickets'] *100, 2);
			else
				$projectPercentage = 0;
			
			$values = array(
				'id' 				=> $project['id'],
				'name' 				=> $project['name'],
				'projectPercentage' => $projectPercentage
			);
			$myProjects[] = $values;
		}
		
		// HOURS
		$ts = new timesheets();
		$myHours = $ts->getUsersHours($_SESSION['userdata']['id']);
		
		$tpl->assign('myHours', $myHours);
		
		// NOTES
		if (isset($_POST['save'])) {
			if (isset($_POST['title']) && isset($_POST['description'])) {
				$values = array(
					'title' => $_POST['title'],
					'description' => $_POST['description']
				);
				
				$this->addNote($_SESSION['userdata']['id'], $values);
				$tpl->setNotification('SAVE_SUCCESS', 'success');
			} else {
				$tpl->setNotification('MISSING_FIELDS', 'error');	
			}
		}
		
		// Statistics
		$tpl->assign('closedTicketsPerWeek', $this->getClosedTicketsPerWeek());
		$tpl->assign('hoursPerTicket', round($this->getHoursPerTicket()));
		$tpl->assign('hoursBugFixing', round($this->getHoursBugFixing(),1));
		
		// WIDGET CUSTOMIZATION
		if (isset($_POST['updateWidgets'])) {
			$widgets = array();
			foreach($this->getWidgets() as $widget) 
				if (isset($_POST['widget-'.$widget['id']])) 
					$widgets[] = $widget['id'];
			
			if (count($widgets)) {
				
				$this->updateWidgets($_SESSION['userdata']['id'], $widgets);
				$tpl->setNotification('SAVE_SUCCESS', 'success');

			} else {

				$tpl->setNotification('ONE_WIDGET_REQUIRED', 'error');

			}
		}
		
		// HOT LEADS
		$leads = new leads();
		$hotLeads = $leads->getHotLeads();
		$tpl->assign('hotLeads',$hotLeads);
		
		$tpl->assign('notes', $this->getNotes($_SESSION['userdata']['id']));
		$tpl->assign('availableWidgets', $this->getAvailableWidgets($_SESSION['userdata']['id']));
		$tpl->assign('myProjects', $myProjects);
		$tpl->assign('widgetTypes', $this->getWidgets());
		$tpl->assign('widgets', $this->getUsersWidgets($_SESSION['userdata']['id']));
		$tpl->assign('helper', $helper);
		
		$tpl->display('dashboard.show');
		
	}
	
}
?>
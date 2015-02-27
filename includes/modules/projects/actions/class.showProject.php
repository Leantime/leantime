<?php

/**
 * shwAll Class - Show all projects
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage projects
 * @license	GNU/GPL, see license.txt
 *
 */

class showProject extends projects{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();

		if(isset($_GET['id'])){
				
			$id = (int)($_GET['id']);

			$project = $this->getProject($id);

			$helper = new helper();
			
			$language 	= new language();

			$language->setModule('projects');

			$lang = $language->readIni();
				
			//Calculate projectdetails
			$opentickets = $this->getOpenTickets($id);

			$closedTickets = $project['numberOfTickets']-$opentickets['openTickets'];

			if ($project['numberOfTickets'] != 0) {

				$projectPercentage = round($closedTickets/$project['numberOfTickets'] *100, 2);
			} else {

				$projectPercentage = 0;
			}

			if ($project['numberOfTickets'] == NULL)
				$project['numberOfTickets'] = 1;

			
			//Post comment
			$comments = new comments();
			if(isset($_POST['comment']) === true){
					
				$values = array(
					'text'			=> ($_POST['text']),
					'datetime' 		=> date("Y-m-d H:i:s"),
					'userId' 		=> ($_SESSION['userdata']['id']),
					'moduleId' 		=> $id,
					'commentParent' => $_POST['father']
				);
					
				$comments->addComment($values,'project');

				$tpl->setNotification('COMMENT_ADDED', 'success');
			}

				$file = new files();
				if (isset($_POST['upload']))
					if (isset($_FILES['file'])) {
						
						$file->upload($_FILES,'project',$id);
						
						$tpl->setNotification('FILE_UPLOADED', 'success');
					} else {
						
						$tpl->setNotification('NO_FILE', 'error');
					}

				
				$timesheets = new timesheets();
				$language 	= new language();

				$language->setModule('projects');
				$lang = $language->readIni();

				
				$data = array();
				$months = array();
				$results = $timesheets->getProjectHours($id);



				$allHours = 0;
				$max = 0;
				foreach($results as $row){
						
					if($row['month'] != NULL){

						$data[] = (int)$row['summe'];
						$months[] = substr($language->lang_echo('MONTH_'.$row['month'].''),0,3);

						if($row['summe'] > $max){
							$max = $row['summe'];
						}
							
					}else{

						$allHours = $row['summe'];
							
					}
						
				}


				$steps = 10;

				if($max > 100) {
					$steps = 50;
				}

				$max = $max + $steps;

				$tpl->assign('timesheetsAllHours', $allHours);

				$chart = "";
				
				$tpl->assign('chart', $chart);



				//Delete File
				if(isset($_GET['delFile']) === true){

					$file = $_GET['delFile'];
					$upload = new fileupload();

					$upload->initFile($file);
					$upload->deleteFile($file);

					$this->deleteFile($file);

					$this->setNotification('FILE_DELETED', 'success');

				}

				//Delete comment
				if(isset($_GET['delComment']) === true){

					$commentId = (int)($_GET['delComment']);

					$this->deleteComment($commentId);

					$this->setNotification('COMMENT_DELETED');

				}

				//Delete account
				if(isset($_GET['delAccount']) === true){

					$accountId = (int)($_GET['delAccount']);

					$this->deleteAccount($accountId);

					$this->setNotification('ACCOUNT_DELETED');
						
				}
				
				
				
				
				//Timesheets
					$invEmplCheck = '0';
					$invCompCheck = '0';
						
					$projectFilter = $id;
					$dateFrom = mktime(0, 0, 0, date("m"), '1',  date("Y"));
					$dateFrom = date("Y-m-d",$dateFrom);
					$dateTo = date("Y-m-d 00:00:00");
					$kind = 'all';
					$userId = 'all';
						
					
						
					if(isset($_POST['kind']) && $_POST['kind'] != ''){
		
						$kind = ($_POST['kind']);
		
					}
						
					if(isset($_POST['userId']) && $_POST['userId'] != ''){
		
						$userId = ($_POST['userId']);
		
					}
						
					if(isset($_POST['dateFrom']) && $_POST['dateFrom'] != ''){
		
						$dateFrom = ($helper->timestamp2date($_POST['dateFrom'],4));
		
					}
						
					if(isset($_POST['dateTo']) && $_POST['dateTo'] != ''){
		
						$dateTo = ($helper->timestamp2date($_POST['dateTo'], 4));
		
					}
					
					if(isset($_POST['invEmpl']) === true){
		
						$invEmplCheck = $_POST['invEmpl'];
						
						if($invEmplCheck == 'on') 
							$invEmplCheck = '1';
						else
							$invEmplCheck = '0';
		
					}else{
						$invEmplCheck = '0';
					}
					
					if(isset($_POST['invComp'])=== true){
		
						$invCompCheck = ($_POST['invComp']);
						
						if($invCompCheck == 'on') 
							$invCompCheck = '1';
						else
							$invCompCheck = '0';
		
					}else{
						$invCompCheck = '0';
					}
						
					$user = new users();
					$employees = $user->getEmployees();
					$timesheets = new timesheets();
					$projects = new projects();
					
					$tpl->assign('employeeFilter', $userId);
					$tpl->assign('employees', $employees);
					$tpl->assign('dateFrom', $helper->timestamp2date($dateFrom, 2));
					$tpl->assign('dateTo', $helper->timestamp2date($dateTo, 2));
					$tpl->assign('actKind', $kind);
					$tpl->assign('kind', $timesheets->kind);
					$tpl->assign('invComp', $invCompCheck);
					$tpl->assign('invEmpl', $invEmplCheck);
					$tpl->assign('helper', $helper);
					$tpl->assign('projectFilter', $projectFilter);
			
					$tpl->assign('allTimesheets', $timesheets->getAll($projectFilter, $kind, $dateFrom, $dateTo, $userId, $invEmplCheck, $invCompCheck));
			
			/* 			'name' = :name AND
						'username' = :username AND
						'password' = :password AND
						'host' = :host AND
						'kind' = :kind */

			if (isset($_POST['accountSubmit'])) {
				$values = array(
					'name' 		=> $_POST['accountName'],
					'username' 	=> $_POST['username'],
					'password' 	=> $_POST['password'],
					'host' 		=> $_POST['host'],
					'kind' 		=> $_POST['kind']
				);
				
				$this->addAccount($values, $id);
			}
				
			//Assign vars
			$ticket = new tickets();
			$tpl->assign('imgExtensions', array('jpg','jpeg','png','gif','psd','bmp','tif','thm','yuv'));
			$tpl->assign('projectTickets', $this->getProjectTickets($id));
			$tpl->assign('projectPercentage', $projectPercentage);
			$tpl->assign('openTickets', $opentickets['openTickets']);
			$tpl->assign('project', $project);
				
			$files = $file->getFilesByModule('project', $id);
			$tpl->assign('files', $files);
			$tpl->assign('numFiles', count($files));
				
			$bookedHours = $this->getProjectBookedHours($id);
			if ($bookedHours['totalHours'] != '') 
				$booked = round($bookedHours['totalHours'],3);
			else 
				$booked = 0;
			
			$tpl->assign('bookedHours', $booked);
			
			$bookedDollars = $this->getProjectBookedDollars($id);
			if ($bookedDollars['totalDollars'] != '') 
				$dollars = round($bookedDollars['totalDollars'],3);
			else 
				$dollars = 0;
			
			$tpl->assign('bookedDollars', $dollars);
			
			$tpl->assign("bookedHoursArray", $this->getProjectBookedHoursArray($id));
				
//			die($id);
			$comment = $comments->getComments('project',$_GET['id']);
			$tpl->assign('comments', $comment);
			$tpl->assign('numComments', $comments->countComments('project', $_GET['id']));
				
				
			$tpl->assign('state', $this->state);
			$tpl->assign('helper', $helper);
			$tpl->assign('role', $_SESSION['userdata']['role']);
			$accounts = $this->getProjectAccounts($id); 
			$tpl->assign('accounts', $accounts);
				

			$tpl->display('projects.showProject');

		}else{
				
			$tpl->display('general.error');

		}
			
	}

	private function generateOfcData(){

	}

}
?>
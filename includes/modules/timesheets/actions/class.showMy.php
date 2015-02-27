<?php

/**
 * showMy Class - Show my timesheets
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage clients
 * @license	GNU/GPL, see license.txt
 *
 */

class showMy extends timesheets{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 */
	public function run() {

		$tpl = new template();

		$invEmplCheck = '0';
		$invCompCheck = '0';
		
		//Only admins and employees
		if($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'employee') {

			$projects = new projects();
			$helper = new helper();
				
			if($_SESSION['userdata']['role'] == 'admin'){

				$tpl->assign('admin', true);
					
			}
				
			if(isset($_POST['saveInvoice']) === true){

				$invEmpl = $_POST['invoiced'];
				
				$this->updateInvoices($invEmpl);


			}
				
				
			
			$projectFilter = '';
				
				
			$dateFrom = mktime(0, 0, 0, date("m"), 1,  date("Y") - 1);
			$dateFrom = date("Y-m-d",$dateFrom);
				
			$dateTo = date("Y-m-d 00:00:00");
				
			$kind = 'all';
				
			if(isset($_REQUEST['projectFilter']) && $_REQUEST['projectFilter'] != ''){

				$projectFilter = ($_REQUEST['projectFilter']);

			}
				
			if(isset($_REQUEST['kind']) && $_REQUEST['kind'] != ''){

				$kind = ($_REQUEST['kind']);

			}
				
			if(isset($_REQUEST['dateFrom']) && $_REQUEST['dateFrom'] != ''){

				$dateFrom = ($helper->timestamp2date($_REQUEST['dateFrom'],4));

			}
				
			if(isset($_REQUEST['dateTo']) && $_REQUEST['dateTo'] != ''){

				$dateTo = ($helper->timestamp2date($_REQUEST['dateTo'], 4));

			}
			
			if(isset($_REQUEST['invEmpl']) === true){

				$invEmplCheck = $_REQUEST['invEmpl'];
				
				if($invEmplCheck == 'on') 
					$invEmplCheck = '1';
				else
					$invEmplCheck = '0';

			}else{
				$invEmplCheck = '0';
			}
			
			if(isset($_REQUEST['invComp'])=== true){

				$invCompCheck = ($_REQUEST['invComp']);
				
				if($invCompCheck == 'on') 
					$invCompCheck = '1';
				else
					$invCompCheck = '0';

			}else{
				$invCompCheck = '0';
			}

			$myTimesheets = $this->getMy($projectFilter, $kind, $dateFrom, $dateTo, $invEmplCheck, $invCompCheck);
			
			//Is this an export request?
			if(isset($_GET['export']) === true){
				
				if($_GET['export'] == 'excel'){
						
						$this->exportExcel($myTimesheets, $dateFrom, $dateTo);
				
				}
				
			} else {
			
				$tpl->assign('dateFrom', $helper->timestamp2date($dateFrom, 2));
				$tpl->assign('dateTo', $helper->timestamp2date($dateTo, 2));
				$tpl->assign('actKind', $kind);
				$tpl->assign('invComp', $invCompCheck);
				$tpl->assign('invEmpl', $invEmplCheck);
				$tpl->assign('kind', $this->kind);
				$tpl->assign('helper', $helper);
				$tpl->assign('projectFilter', $projectFilter);
				$tpl->assign('allProjects', $projects->getAll());
					
				$tpl->assign('allTimesheets', $myTimesheets);
	
				$tpl->display('timesheets.showMy');
			
			}
		}else{

			$tpl->display('general.error');

		}
			
	}
	
	
	
	
	public function exportExcel($myTimesheets, $dateFrom, $dateTo) {
		
		function xlsWriteLabel($Row, $Col, $Value ) {
		    $L = strlen($Value);
		    echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
		    echo $Value;
			return;
		} 
		
		$export_file = "export.xls";
	   	$helper = new helper();
	   	$language 	= new language();

		$language->setModule('timesheets');

		$lang = $language->readIni();
	   	
	   	
	    ini_set('zlib.output_compression','Off');
	   
	    header('Pragma: public');
	    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");                  // Date in the past   
	    header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
	    header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1
	    header('Cache-Control: pre-check=0, post-check=0, max-age=0');    // HTTP/1.1
	    header ("Pragma: no-cache");
	    header("Expires: 0");
	    header("Content-Transfer-Encoding: binary ");


	    header('charset="utf-8"');
	    header('Content-Type: application/vnd.ms-excel;');                 // This should work for IE & Opera
	    header("Content-type: application/x-msexcel");      
	          
	    header('Content-Disposition: attachment; filename="'.basename($export_file).'"'); 
			
		echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);   
			
			$i = 0;
			$sumWorktime = 0;
			
			xlsWriteLabel($i,0,"Zeitzettel ");
			xlsWriteLabel($i,1,"");
			xlsWriteLabel($i,2,"");
			xlsWriteLabel($i,3,"");
			xlsWriteLabel($i,4,"");
			xlsWriteLabel($i,5,"");

			$i++;
			
			
			xlsWriteLabel($i,0,"Zeitraum ");
			xlsWriteLabel($i,1,"von ".$helper->timestamp2date($dateFrom, 2)." ");
			xlsWriteLabel($i,2,"bis ".$helper->timestamp2date($dateFrom, 2)."");
			xlsWriteLabel($i,3,"");
			xlsWriteLabel($i,4,"");
			xlsWriteLabel($i,5,"");

			$i++;
			
			xlsWriteLabel($i,0,"");
			xlsWriteLabel($i,1,"");
			xlsWriteLabel($i,2,"");
			xlsWriteLabel($i,3,"");
			xlsWriteLabel($i,4,"");
			xlsWriteLabel($i,5,"");

			$i++;
			
			xlsWriteLabel($i,0,"".$lang['DATE']."");
			xlsWriteLabel($i,1,"".$lang['HOURS']."");
			xlsWriteLabel($i,2,"".$lang['TICKET']."");
			xlsWriteLabel($i,3,"".$lang['PROJECT']."");
			xlsWriteLabel($i,4,"".$lang['KIND']."");
			xlsWriteLabel($i,5,"".$lang['DESCRIPTION']."");
			
			foreach($myTimesheets as $row) { 
				$i++;

				
				xlsWriteLabel($i,0,"".$helper->timestamp2date($row['workDate'], 2)."");
				xlsWriteLabel($i,1,"".$row['hours']."");
				xlsWriteLabel($i,2,"".utf8_decode($row['headline'])."");
				xlsWriteLabel($i,3,"".utf8_decode($row['name'])."");
				xlsWriteLabel($i,4,"".$lang[$row['kind']]."");
				xlsWriteLabel($i,5,"".utf8_decode($row['description'])."");
				
				$sumWorktime = $sumWorktime + $row['hours'];
				
			 }
			 
			 $i++;
			 
			 xlsWriteLabel($i,0,"");
			 xlsWriteLabel($i,1,"");
			 xlsWriteLabel($i,2,"");
			 xlsWriteLabel($i,3,"");
			 
		
			$i++;
			
			xlsWriteLabel($i,0,"Summen");
			xlsWriteLabel($i,1,"".$sumWorktime."");
			xlsWriteLabel($i,2,"");
			xlsWriteLabel($i,3,"");
			xlsWriteLabel($i,4,"");
			xlsWriteLabel($i,5,"");
			 
			echo pack("ss", 0x0A, 0x00);
		
	}
	

}
?>
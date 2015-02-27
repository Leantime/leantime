<?php

defined('RESTRICTED') or die('No access');

class widgets extends dashboard{
	
	public function run() {
		
		$user_id=$_SESSION['userdata']['id'];

		if (isset($_REQUEST["value"])) { 
		  // SET value  
		  
		  $value = $_REQUEST["value"];
		  
		  $returns = $this->getWidgets($user_id);
		  
		  if (empty($returns))
		   
		    $this->addWidgetData($value, $user_id);
		  
		  else
		  	
		    $this->updateWidgetData($value, $user_id);
		
		} else {
		  // GET value 
		  
		  $returns = $this->getWidgetsValue($user_id);

		  echo $returns[0];
		} 
	}

}

?>
<?php
header('Content-type: text/json');
header('Content-type: application/json');

include $_SERVER['DOCUMENT_ROOT'].'/config/configuration.php';

$config = new config();

$id = (int)$_GET['id'];

if ($id && $id > 0) {
		$con = mysql_connect($config->dbHost, $config->dbUser, $config->dbPassword);
		mysql_select_db($config->dbDatabase, $con);
		$sql = "SELECT 
						th.changeType, th.changeValue, th.dateModified, th.userId, 
						ticket.id as ticketId, ticket.headline, ticket.description,
						user.firstname, user.lastname 
					FROM zp_ticketHistory as th
					INNER JOIN zp_user as user ON th.userId = user.id
					INNER JOIN zp_tickets as ticket ON th.ticketId = ticket.id
					WHERE ticketId = ".mysql_real_escape_string($id);
							
		$query = mysql_query($sql);
		
		while ($value = mysql_fetch_assoc($query)) {
			$description = $value['description'];		
			$posts[] = array(
						'headline' 	=> ''.$value['firstname'].' '.$value['lastname'].' changed '.$value['changeType'].' to '.$value['changeValue'],
						'text'		=> 'Ticket #'.$value['ticketId']. ' edited on '.date('h:m',$value['dateModified']),
						'startDate'	=> date('y,m,d',strtotime($value['dateModified'])),
						'asset' => array(
								'caption' => 'Test', 
								'media' => '', 
								'credit' => ''
						)
			);
		}
		$response['timeline'] = array('headline' => 'Ticket #'.$id, 'type' => 'default', 'text' => $description, 'date' => $posts);
		header('Content-type: text/json');
		header('Content-type: application/json');
		
		echo json_encode($response);
}
?>
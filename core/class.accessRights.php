<?php

/**
 * AccesRight class - Controll Access to the module elements
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */

class accessRights {
	
	/**
	 * @access private
	 * @var object - db connection
	 */
	public $db = NULL;
	
	public $rightLetters = array('r' => 0, 'w' => '1', 'd' => 2);
	
	/**
	 * 
	 * Saves the id of a new entry to be able to show the rules after saving
	 * 
	 */
	public $tempId = '';
	
	public function __construct() {
			
		$this->db = new db();
	
	}
	
	
	/**
	 * LimitValues 
	 * Limits the number of values of a set taken from db, to confirm accessrights
	 * 
	 */
	public function limitValues($values = array(), $module, $idKeyToCheck){
		
		$userSysOrgArray = explode(',', $_SESSION['userdata']['systemOrgsId']);
		$userRoleArray = explode(',', $_SESSION['userdata']['role']);
		
		$query ="SELECT 
					id, 
					valueId, 
					ownerId, 
					systemorganisations, 
					roles, 
					users, 
					accessrights,
					IF(";
					$i=1;
					foreach($userSysOrgArray as $row){
							
						$query .= "FIND_IN_SET('".$row."', systemorganisations) ";
						if($i<count($userSysOrgArray)){
							$query .= "OR ";
						}
						$i++;
					}
					
					
					$query .= ",1, 000) AS foundInSysorg,
					
					IF(";
					$i=1;
					foreach($userRoleArray as $row){
							
						$query .= "FIND_IN_SET('".$row."', roles) ";
						if($i<count($userRoleArray)){
							$query .= "OR ";
						}
						$i++;
					}
					
					
					$query .= ",10, 000) AS foundInRole,";

					$query .= "IF(FIND_IN_SET(:userId,users), 100, 000) AS foundInUsers					
		FROM 
			jhd_".$module."_accessrights 
		";
		
		$stmn = $this->db->{'database'}->prepare($query);		
		
		$stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_STR);		

		$stmn->execute();
		
		$accesRightValues = $stmn->fetchAll();
		
		foreach($values as $key => $rowValues){
			
			$values[$key]['accessRights'] = 'd';

			foreach($accesRightValues as $rowAccess){
					
				if($rowValues[$idKeyToCheck] == $rowAccess['valueId']){
					
					$values[$key]['accessRights'] = 'r';
					//Owner always gets Deletion rights	
					if($rowAccess['ownerId'] === $_SESSION['userdata']['id']){
								
						$accessRights = 'd';	
						$values[$key]['accessRights'] = $accessRights;
						
					}else{
						$found = 0;
						
						if($rowAccess['foundInSysorg'] > 0){
							
							$found = 1;
							$accessRights = $this->performBinaryComp($rowAccess['foundInSysorg'],$rowAccess['accessrights']);		
							
							if($this->rightLetters[$accessRights] > $this->rightLetters[$values[$key]['accessRights']]){
								$values[$key]['accessRights'] = $accessRights;	
							}
														
							
						}
						
						if($rowAccess['foundInRole'] > 0){
							
							$found = 1;
							$accessRights = $this->performBinaryComp($rowAccess['foundInRole'],$rowAccess['accessrights']);		
							if($this->rightLetters[$accessRights] > $this->rightLetters[$values[$key]['accessRights']]){
								$values[$key]['accessRights'] = $accessRights;	
							}
							
							
						}
						
						if($rowAccess['foundInUsers'] > 0){
								
							$found = 1;
							$accessRights = $this->performBinaryComp($rowAccess['foundInUsers'],$rowAccess['accessrights']);		
							if($this->rightLetters[$accessRights] > $this->rightLetters[$values[$key]['accessRights']]){
								$values[$key]['accessRights'] = $accessRights;	
							}
													
						}

						if($found == 0){		
							//Delete Value User was not found in the accessrightstable
							unset($values[$key]);
						}
						
					
					}

					
				}
			
			}
			 
			
		}
		
		return 	$values;

	}
	
	/**
	 * Check one specific row against accessRights
	 * Function needs the valueId, the module and the right that you need.
	 *  
	 */
	public function checkAccessRights($valueId, $module, $checkAccessFor='r'){
		
			
		$userSysOrgArray = explode(',', $_SESSION['userdata']['systemOrgsId']);
		$userRoleArray = explode(',', $_SESSION['userdata']['role']);
		
		$query ="SELECT 
					id, 
					valueId, 
					ownerId, 
					systemorganisations, 
					roles, 
					users, 
					accessrights,
					IF(";
					$i=1;
					foreach($userSysOrgArray as $row){
							
						$query .= "FIND_IN_SET('".$row."', systemorganisations) ";
						if($i<count($userSysOrgArray)){
							$query .= "OR ";
						}
						$i++;
					}
					
					
					$query .= ",1, 000) AS foundInSysorg,
					
					IF(";
					$i=1;
					foreach($userRoleArray as $row){							
						$query .= "FIND_IN_SET('".$row."', roles) ";
						if($i<count($userRoleArray)){
							$query .= "OR ";
						}
						$i++;
					}
					
					
					$query .= ",10, 000) AS foundInRole,";

					$query .= "IF(FIND_IN_SET(:userId,users), 100, 000) AS foundInUsers,	";
					
					$query .= "IF(FIND_IN_SET(:userId,ownerId), 111, 000) AS foundInOwner
									
		FROM 
			jhd_".$module."_accessrights 
		WHERE valueId = :valueId";
		
		$stmn = $this->db->{'database'}->prepare($query);		
		
		$stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_STR);
		$stmn->bindValue(':valueId', $valueId, PDO::PARAM_STR);
		
		$stmn->execute();
		
		$accesRightValues = $stmn->fetch();

		$accessRights = 'r';
		if(count($accesRightValues) == 0 || $accesRightValues === false){
			
			
			//Has no accessrights therefore grant access to everything	
			return true;
			
		}else{
				
				
			if($accesRightValues['foundInSysorg'] > 0){
	
				$accessRightsTemp = $this->performBinaryComp($accesRightValues['foundInSysorg'],$accesRightValues['accessrights']);		
				if($this->rightLetters[$accessRightsTemp] >= $this->rightLetters[$checkAccessFor]){
						$accessRights = $accessRightsTemp;	
				}
				
			}
			
			if($accesRightValues['foundInRole'] > 0){
								
				$accessRightsTemp = $this->performBinaryComp($accesRightValues['foundInRole'],$accesRightValues['accessrights']);		

				if($this->rightLetters[$accessRightsTemp] >= $this->rightLetters[$checkAccessFor]){
						$accessRights = $accessRightsTemp;	
				}
			}
			
			if($accesRightValues['foundInUsers'] > 0){
								
				$accessRightsTemp = $this->performBinaryComp($accesRightValues['foundInUsers'],$accesRightValues['accessrights']);		

				if($this->rightLetters[$accessRightsTemp] >= $this->rightLetters[$checkAccessFor]){
						$accessRights = $accessRightsTemp;	
				}
			}
			
			if($accesRightValues['foundInOwner'] > 0){
					
				return true;
				
			}
			
			
			if($this->rightLetters[$accessRights] >= $this->rightLetters[$checkAccessFor]) {
				
				return true;
				
			}else{
				//No right to perform this action	
				return false;
			
			}	
			
		}
		
	}
	
	/**
	 * Performs the binary comparison of the accessrights Bins
	 * Gives a letter back 
	 * r : read
	 * w : write
	 * d : delete
	 * 
	 */
	public function performBinaryComp($foundIn, $accessRights){
			
		//Reading writes have to be determined by now.	
		$returnRights = 'r';
				
		//Check Writing Rights
		$checkBin = $foundIn.'000';
		$checkDez = bindec($checkBin);
		
		$accessBin = substr($accessRights, -strlen($checkBin));
		$accessDec = bindec($accessBin);
		
		
		if($checkDez <= $accessDec){
			
			$returnRights = 'w';
	
		}
		
		//Check Deletion Rights
		$checkBin = $foundIn.'000000';
		$checkDez = bindec($checkBin);
		
		$accessBin = substr($accessRights, -strlen($checkBin));
		$accessDec = bindec($accessBin);
		

		if($checkDez <= $accessDec){
			
			$returnRights = 'd';
	
		}


		return $returnRights;

	}
	
	/**
	 * ShowHTML Accessrights HTML
	 * 
	 */
	public function showRuleHTML($module, $valueId = '', $predefined = 0){

		if($valueId != '' || $this->tempId != ''){
			if($this->tempId != ''){
				$valueId = $this->tempId;
			}
			$query = "SELECT id, valueId, ownerId, systemorganisations, roles, users, accessrights
			FROM jhd_".$module."_accessrights WHERE valueId = :valueId LIMIT 10";
			
			$stmn = $this->db->{'database'}->prepare($query);		
			
			$stmn->bindValue(':valueId', $valueId, PDO::PARAM_STR);
			
			$stmn->execute();
			
			$accesRightValues = $stmn->fetchAll();
			
		}elseif($predefined == 1) {
				
			$accesRightValues = array(array(
			"id" => "",
			"valueId" => "",
			"ownerId" => $_SESSION['userdata']['id'],
			"systemorganisations" => '',
			"roles" => '',
			"users" => "".$_SESSION['userdata']['id'].",",
			"accessrights" => '1100100100',
			));
			
		}else{
			
			$accesRightValues = array();
		
		}
		$setting = new setting();
		$contacts = new contacts();
		
		$allSysOrgs = $setting->getAllSystemOrganisations();
		$allRoles	= $setting->getRoles();
		$allUsers	= $contacts->getAllAccounts();
		 
		if(count($accesRightValues) > 0){
			$isPrivate = 1;
		}else{
			$isPrivate = 0;
		}
		echo'
		<script type="text/javascript">
			function showRows(num){
					
				$("#rules tr.rule").hide();
				
				for(i=1; i<=num; i++){
					
					$("#rules tr:eq("+i+")").show();
				}
				
				
			}
			$(function() {
				showRows('.count($accesRightValues).');
			});
		
		</script>
		<table cellpadding="0" cellspacing="0" width="800px" id="rules">';
			echo'<tr>
					<td colspan="3">
					<strong>Privat</strong>&nbsp;<input type="checkbox" value="privat" name="private"'; if($isPrivate == 1)echo' checked="checked" '; echo'/>&nbsp;&nbsp;
					<strong>Anzahl Regeln</strong>&nbsp;<select name="numRules" id="numRules" onchange="showRows($(\'#numRules option:selected\').val());">
					<option value="0">Auswählen</option>
					<option value="1"';if(count($accesRightValues) == 1)echo'selected="selected"';echo'>1</option>
					<option value="2"';if(count($accesRightValues) == 2)echo'selected="selected"';echo'>2</option>
					<option value="3"';if(count($accesRightValues) == 3)echo'selected="selected"';echo'>3</option>
					<option value="4"';if(count($accesRightValues) == 4)echo'selected="selected"';echo'>4</option>
					<option value="5"';if(count($accesRightValues) == 5)echo'selected="selected"';echo'>5</option>
					<option value="6"';if(count($accesRightValues) == 6)echo'selected="selected"';echo'>6</option>
					<option value="7"';if(count($accesRightValues) == 7)echo'selected="selected"';echo'>7</option>
					<option value="8"';if(count($accesRightValues) == 8)echo'selected="selected"';echo'>8</option>
					<option value="9"';if(count($accesRightValues) == 9)echo'selected="selected"';echo'>9</option>
					<option value="10"';if(count($accesRightValues) == 10)echo'selected="selected"';echo'>10</option>
					</select><br />
					<hr />
					</td> 
				</tr>';
				
				
				for($i=0; $i<10; $i++){
					
					if(isset($accesRightValues[$i]['accessrights']) !== true){
						$accesRightValues[$i]['accessrights'] = '1000000000';
						$accesRightValues[$i]['systemorganisations'] = '';
						$accesRightValues[$i]['roles'] = '';
						$accesRightValues[$i]['users'] = '';
					}
						$readSysOrg = substr($accesRightValues[$i]['accessrights'], -1, 1);
						$writeSysOrg = substr($accesRightValues[$i]['accessrights'], -4, 1);
						$deleteSysOrg = substr($accesRightValues[$i]['accessrights'], -7, 1);
						
						$readRole = substr($accesRightValues[$i]['accessrights'], -2, 1);
						$writeRole = substr($accesRightValues[$i]['accessrights'], -5, 1);
						$deleteRole = substr($accesRightValues[$i]['accessrights'], -8, 1);
						
						$readUser = substr($accesRightValues[$i]['accessrights'], -3, 1);
						$writeUser = substr($accesRightValues[$i]['accessrights'], -6, 1);
						$deleteUser = substr($accesRightValues[$i]['accessrights'], -9, 1);
					
					echo'<tr style="display:none;" class="rule">
					<td style="padding-bottom:10px;"><label>Systemorganisationen</label>
						<select name="sysOrg_'.$i.'[]" id="sysOrg_'.$i.'[]" multiple="multiple" size="10">';
							foreach($allSysOrgs as $row){
								echo'<option value="'.$row['id'].'"';
								if(isset($accesRightValues[$i]) === true && 
									in_array($row['id'], explode(',', $accesRightValues[$i]['systemorganisations']))===true){
										echo 'selected="selected"';
								}
									echo'>'.$row['name'].'</option>';
								
							}
						
					echo'</select><br />
					<input type="checkbox" name="readSysOrgs_'.$i.'" id="readSysOrgs_'.$i.'"';if($readSysOrg == 1)echo' checked="checked" '; echo'/>Lesen
					<input type="checkbox" name="writeysOrgs_'.$i.'" id="readSysOrgs_'.$i.'"';if($writeSysOrg == 1)echo' checked="checked" '; echo'/>Schreiben
					<input type="checkbox" name="deleteSysOrgs_'.$i.'" id="readSysOrgs_'.$i.'"';if($deleteSysOrg == 1)echo' checked="checked" '; echo'/>Löschen
					
					</td>';
					
					
				echo'<td><label>Rollen</label>
				<select name="roles_'.$i.'[]" id="roles_'.$i.'[]" multiple="multiple" size="10">';
				
				$oldSysOrg = '';
				$currentSysOrg = '';
				$k = 0;
				foreach($allRoles as $roleRow){
					
					$currentSysOrg = $roleRow['sysOrg'];
					
						//Show only Modules that are defined for that SysOrg
						
						

							if($oldSysOrg != $currentSysOrg){
								if($k > 0){
									echo'</optgroup>';
								}
								echo '<optgroup label="'. $roleRow['sysOrgName'].'">';
							}
							
							echo'<option value="'.$roleRow['roleName'].'"';
							
							if(isset($accesRightValues[$i]) === true && 
									in_array($roleRow['roleName'], explode(',', $accesRightValues[$i]['roles']))===true){
										echo 'selected="selected"';
							}
								
							echo'>'.$roleRow['roleDescription'].'</option>';
						
						
						
						
						
					$k++;
					$oldSysOrg = $roleRow['sysOrg'];
					
					
				}
				echo'</select><br />
				<input type="checkbox" name="readRoles_'.$i.'" id="readRoles_'.$i.'"';if($readRole == 1)echo' checked="checked" '; echo'/>Lesen
					<input type="checkbox" name="writeRoles_'.$i.'" id="writeRoles_'.$i.'"';if($writeRole == 1)echo' checked="checked" '; echo'/>Schreiben
					<input type="checkbox" name="deleteRoles_'.$i.'" id="deleteRoles_'.$i.'"';if($deleteRole == 1)echo' checked="checked" '; echo'/>Löschen
				</td>
				
				<td><label>Benutzer</label>
						<select name="users_'.$i.'[]" id="users_'.$i.'[]" multiple="multiple" size="10">';
							foreach($allUsers as $row){
								echo'<option value="'.$row['id'].'"';
								if(isset($accesRightValues[$i]) === true && 
									in_array($row['id'], explode(',', $accesRightValues[$i]['users']))===true){
										echo 'selected="selected"';
								}
									echo'>'.$row['n_family'].', '.$row['n_given'].'</option>';
								
							}
						
					echo'</select><br />
					
					<input type="checkbox" name="readUsers_'.$i.'" id="readUsers_'.$i.'"';if($readUser == 1)echo' checked="checked" '; echo'/>Lesen
					<input type="checkbox" name="writeUsers_'.$i.'" id="writeUsers_'.$i.'"';if($writeUser == 1)echo' checked="checked" '; echo'/>Schreiben
					<input type="checkbox" name="deleteUsers_'.$i.'" id="deleteUsers_'.$i.'"';if($deleteUser == 1)echo' checked="checked" '; echo'/>Löschen
					</td>
					
				</tr>
				';
				}
				
				
		echo'</table>';
	
	}

/**
	 * ShowHTML Accessrights HTML
	 * 
	 */
	public function showRuleView($module, $valueId = ''){
		
		if($valueId != '' || $this->tempId != ''){
			
			$query = "SELECT id, valueId, ownerId, systemorganisations, roles, users, accessrights
			FROM jhd_".$module."_accessrights WHERE valueId = :valueId LIMIT 10";
			
			$stmn = $this->db->{'database'}->prepare($query);		
			
			$stmn->bindValue(':valueId', $valueId, PDO::PARAM_STR);
			
			$stmn->execute();
			
			$accesRightValues = $stmn->fetchAll();
			
		}else{
				
			$accesRightValues = array();
			
		}
		
		$setting = new setting();
		$contacts = new contacts();
		
		$allSysOrgs = $setting->getAllSystemOrganisations();
		$allRoles	= $setting->getRoles();
		$allUsers	= $contacts->getAllAccounts();
		 
		if(count($accesRightValues) > 0){
			$isPrivate = 1;
		}else{
			$isPrivate = 0;
		}
		echo'
		
		<table cellpadding="0" cellspacing="0" id="rules" width="100%">';
			echo'
				<thead>
				<tr>
				<th style="text-align:left;"><h1>Systemorganisationen</h1></th>
				<th style="text-align:left;"><h1>Rollen</h1></th>
				<th style="text-align:left;"><h1>Benutzer</h1></th>
				</tr>
				</thead>
			';
				
				
				for($i=0; $i<10; $i++){
					
					if(isset($accesRightValues[$i]['accessrights']) !== true){
						$accesRightValues[$i]['accessrights'] = '1000000000';
						$accesRightValues[$i]['systemorganisations'] = '';
						$accesRightValues[$i]['roles'] = '';
						$accesRightValues[$i]['users'] = '';
					}
						$readSysOrg = substr($accesRightValues[$i]['accessrights'], -1, 1);
						$writeSysOrg = substr($accesRightValues[$i]['accessrights'], -4, 1);
						$deleteSysOrg = substr($accesRightValues[$i]['accessrights'], -7, 1);
						
						$readRole = substr($accesRightValues[$i]['accessrights'], -2, 1);
						$writeRole = substr($accesRightValues[$i]['accessrights'], -5, 1);
						$deleteRole = substr($accesRightValues[$i]['accessrights'], -8, 1);
						
						$readUser = substr($accesRightValues[$i]['accessrights'], -3, 1);
						$writeUser = substr($accesRightValues[$i]['accessrights'], -6, 1);
						$deleteUser = substr($accesRightValues[$i]['accessrights'], -9, 1);
					
					echo'<tr class="rule">
					<td style="padding-bottom:10px;"><p>
						
						
						';
							foreach($allSysOrgs as $row){
								
								if(isset($accesRightValues[$i]) === true && 
									in_array($row['id'], explode(',', $accesRightValues[$i]['systemorganisations']))===true){
										echo $row['name'];
										echo '<br />';
										
								}
									
								
							}
						
					echo'
					</p>
					</td>';
					
					
				echo'<td>
				<p>';
				
				$oldSysOrg = '';
				$currentSysOrg = '';
				$k = 0;
				foreach($allRoles as $roleRow){
					
					$currentSysOrg = $roleRow['sysOrg'];
					
						//Show only Modules that are defined for that SysOrg
						
						

							
							
							
							if(isset($accesRightValues[$i]) === true && 
									in_array($roleRow['roleName'], explode(',', $accesRightValues[$i]['roles']))===true){
										if($oldSysOrg != $currentSysOrg){
								
											echo '<strong>'. $roleRow['sysOrgName'].'</strong><br />';
										}
								
											
										echo '&nbsp;&nbsp;'.$roleRow['roleDescription'];
										echo '<br />';
										
							}
								
							
						
						
						
						
						
					$k++;
					$oldSysOrg = $roleRow['sysOrg'];
					
					
				}
				echo'</p></td>
				
				<td><p>
						';
							foreach($allUsers as $row){
								
								if(isset($accesRightValues[$i]) === true && 
									in_array($row['id'], explode(',', $accesRightValues[$i]['users']))===true){
										echo ''.$row['n_family'].', '.$row['n_given'].'';
										echo '<br />';
										
								}
									
							}
						
					echo'</p>
					
					</td>
					
				</tr>
				';
				}
				
				
		echo'</table>';
	
	}
	
	public function saveRules($valueId, $module, $predefined = array()){
					
			$helper = new helper();
			
			$this->tempId = $valueId;
			
			try{
				$this->db->{'database'}->beginTransaction();
				//Not private anymore? Delete all things
				$query = "DELETE FROM jhd_".$module."_accessrights WHERE valueId = :valueId";
					
				$stmn = $this->db->{'database'}->prepare($query);		
				
				$stmn->bindValue(':valueId', $valueId, PDO::PARAM_STR);
				
				$stmn->execute();
				
				if(is_array($predefined) === true && count($predefined) > 2){
						
					$_POST["numRules"] = 1;
					$_POST['private'] = true;
					$_POST['sysOrg_0'] = $predefined['systemorganisations'];
					$_POST['roles_0'] = $predefined['roles'];
					$_POST['users_0'] = $predefined['users'];
					$_POST['accessrights'] =$predefined['accessrights'];
			
			
				}
					
				if(isset($_POST['private']) === true){
						
						$numberOfRules = $_POST['numRules'];
						
						//Save all rules
						for($i=0; $i<$numberOfRules; $i++){
							
							if(isset($_POST['sysOrg_'.$i.'']) === true){	
								$sysOrgs = $helper->getMultipleValues($_POST['sysOrg_'.$i.'']);
							}else{
								$sysOrgs = '';
							}

							if(isset($_POST['roles_'.$i.'']) === true){	
								$roles = $helper->getMultipleValues($_POST['roles_'.$i.'']);
							}else{
								$roles = '';
							}
							
							if(isset($_POST['users_'.$i.'']) === true){	
								$users= $helper->getMultipleValues($_POST['users_'.$i.'']);
							}else{
								$users = '';
							}
							if(isset($_POST['readSysOrgs_'.$i.'']) === true && $_POST['readSysOrgs_'.$i.''] === 'on') $ar1=1; else $ar1=0;
							if(isset($_POST['readRoles_'.$i.'']) === true && $_POST['readRoles_'.$i.''] === 'on') $ar2=1; else $ar2=0;
							if(isset($_POST['readUsers_'.$i.'']) === true && $_POST['readUsers_'.$i.''] === 'on') $ar3=1; else $ar3=0;
							if(isset($_POST['writeSysOrgs_'.$i.'']) === true && $_POST['writeSysOrgs_'.$i.''] === 'on') $ar4=1; else $ar4=0;
							if(isset($_POST['writeRoles_'.$i.'']) === true && $_POST['writeRoles_'.$i.''] === 'on') $ar5=1; else $ar5=0;
							if(isset($_POST['writeUsers_'.$i.'']) === true && $_POST['writeUsers_'.$i.''] === 'on') $ar6=1; else $ar6=0;
							if(isset($_POST['deleteSysOrgs_'.$i.'']) === true && $_POST['deleteSysOrgs_'.$i.''] === 'on') $ar7=1; else $ar7=0;
							if(isset($_POST['deleteRoles_'.$i.'']) === true && $_POST['deleteRoles_'.$i.''] === 'on') $ar8=1; else $ar8=0;
							if(isset($_POST['deleteUsers_'.$i.'']) === true && $_POST['deleteUsers_'.$i.''] === 'on') $ar9=1; else $ar9=0;
							
							if(isset($_POST['accessrights'] ) === false) {
							
								$accessRight = '1'.$ar9.$ar8.$ar7.$ar6.$ar5.$ar4.$ar3.$ar2.$ar1;
							
							}else{
							
								$accessRight = $_POST['accessrights'];
							
							}
							
							$query = "INSERT INTO jhd_".$module."_accessrights
							(valueId, ownerId, systemorganisations, roles, users, accessrights)
							VALUES
							(:valueId, :ownerId, :systemorganisations, :roles, :users, :accessrights)";
							
							
							$stmn = $this->db->{'database'}->prepare($query);		
				
							$stmn->bindValue(':valueId', $valueId, PDO::PARAM_STR);
							if (isset($_SESSION['userdata']['id'])){
								$stmn->bindValue(':ownerId', $_SESSION['userdata']['id'], PDO::PARAM_STR);
							}else{
								$stmn->bindValue(':ownerId', -1, PDO::PARAM_STR);
							}
							$stmn->bindValue(':systemorganisations', $sysOrgs, PDO::PARAM_STR);
							$stmn->bindValue(':roles', $roles, PDO::PARAM_STR);
							$stmn->bindValue(':users', $users, PDO::PARAM_STR);
							$stmn->bindValue(':accessrights', $accessRight, PDO::PARAM_STR);
				
							$stmn->execute();
							
						}
	
				}
	
			$this->db->{'database'}->commit();
			$stmn->closeCursor();
			return false;
		} catch(PDOException $err) {    			
        		$this->db->{'database'}->rollback();        		
        		echo $this->db->getErrorMessage($err);        			
        		return false;
    	} 
		
		
	}
	
	public function getAllUsersByAccessRights($module, $valueId = ''){
			
		if($valueId != '' || $this->tempId != ''){
					
		$query = "SELECT id, valueId, ownerId, systemorganisations, roles, users, accessrights
			FROM jhd_".$module."_accessrights WHERE valueId = :valueId LIMIT 10";
			
			$stmn = $this->db->{'database'}->prepare($query);		
			
			$stmn->bindValue(':valueId', $valueId, PDO::PARAM_STR);
			
			$stmn->execute();
			
			$accesRightValues = $stmn->fetchAll();
			
		}else{
				
			$accesRightValues = array();
			
		}
		
		$setting = new setting();
		$contacts = new contacts();
		
		$allSysOrgs = $setting->getAllSystemOrganisations();
		$allRoles	= $setting->getRoles();
		$allUsers	= $contacts->getAllAccounts();
		 
		if(count($accesRightValues) > 0){
			$isPrivate = 1;
		}else{
			$isPrivate = 0;
		}
		echo'
		
		<table cellpadding="0" cellspacing="0" id="rules" width="100%">';
			echo'
				<thead>
				<tr>
				<th style="text-align:left;"><h1>Systemorganisationen</h1></th>
				<th style="text-align:left;"><h1>Rollen</h1></th>
				<th style="text-align:left;"><h1>Benutzer</h1></th>
				</tr>
				</thead>
			';
				
				
				for($i=0; $i<10; $i++){
					
					if(isset($accesRightValues[$i]['accessrights']) !== true){
						$accesRightValues[$i]['accessrights'] = '1000000000';
						$accesRightValues[$i]['systemorganisations'] = '';
						$accesRightValues[$i]['roles'] = '';
						$accesRightValues[$i]['users'] = '';
					}
						$readSysOrg = substr($accesRightValues[$i]['accessrights'], -1, 1);
						$writeSysOrg = substr($accesRightValues[$i]['accessrights'], -4, 1);
						$deleteSysOrg = substr($accesRightValues[$i]['accessrights'], -7, 1);
						
						$readRole = substr($accesRightValues[$i]['accessrights'], -2, 1);
						$writeRole = substr($accesRightValues[$i]['accessrights'], -5, 1);
						$deleteRole = substr($accesRightValues[$i]['accessrights'], -8, 1);
						
						$readUser = substr($accesRightValues[$i]['accessrights'], -3, 1);
						$writeUser = substr($accesRightValues[$i]['accessrights'], -6, 1);
						$deleteUser = substr($accesRightValues[$i]['accessrights'], -9, 1);
					
					echo'<tr class="rule">
					<td style="padding-bottom:10px;"><p>
						
						
						';
							foreach($allSysOrgs as $row){
								
								if(isset($accesRightValues[$i]) === true && 
									in_array($row['id'], explode(',', $accesRightValues[$i]['systemorganisations']))===true){
										echo $row['name'];
										echo '<br />';
										break;
								}
									
								
							}
						
					echo'
					</p>
					</td>';
					
					
				echo'<td>
				<p>';
				
				$oldSysOrg = '';
				$currentSysOrg = '';
				$k = 0;
				foreach($allRoles as $roleRow){
					
					$currentSysOrg = $roleRow['sysOrg'];
					
						//Show only Modules that are defined for that SysOrg
						
						

							
							
							
							if(isset($accesRightValues[$i]) === true && 
									in_array($roleRow['roleName'], explode(',', $accesRightValues[$i]['roles']))===true){
										if($oldSysOrg != $currentSysOrg){
								
											echo '<strong>'. $roleRow['sysOrgName'].'</strong><br />';
										}
								
											
										echo '&nbsp;&nbsp;'.$roleRow['roleDescription'];
										echo '<br />';
										break;
							}
								
							
						
						
						
						
						
					$k++;
					$oldSysOrg = $roleRow['sysOrg'];
					
					
				}
				echo'</p></td>
				
				<td><p>
						';
							foreach($allUsers as $row){
								
								if(isset($accesRightValues[$i]) === true && 
									in_array($row['id'], explode(',', $accesRightValues[$i]['users']))===true){
										echo ''.$row['n_family'].', '.$row['n_given'].'';
										echo '<br />';
										break;
								}
									
							}
						
					echo'</p>
					
					</td>
					
				</tr>
				';
				}
				
				
		echo'</table>';
			
		
				
			
		
	}

	public function getMemberEmails($module, $valueId = ''){
			
		if($valueId != '' || $this->tempId != ''){
					
		$query = "SELECT 
				id, valueId, ownerId, systemorganisations, roles, users, accessrights
			FROM jhd_".$module."_accessrights WHERE valueId = :valueId LIMIT 10";
			
			$stmn = $this->db->{'database'}->prepare($query);		
			
			$stmn->bindValue(':valueId', $valueId, PDO::PARAM_STR);
			
			$stmn->execute();
			
			$accesRightValues = $stmn->fetchAll();
			
			/**
			 * CAUTON: Very complicated Query 
			 * Too hard to explain
			 * 
			 */
			
			/**
			 * 
			 * All users with sysorgs and roles als comma seperated list as columns
			 */
			$usersWSysOrgsQuery = "SELECT
				account.id AS accountId,
				person.jhd_person_name_family AS lastname,
				person.jhd_person_name_given,
				address.jhd_address_email AS email,
				account.role AS roles
			FROM
				jhd_accounts AS account
			LEFT JOIN jhd_persons AS person ON account.id = person.jhd_person_account_id
			LEFT JOIN jhd_contacts AS contact ON person.jhd_person_id = contact.jhd_contact_person_id
			LEFT JOIN jhd_addresses AS address ON contact.jhd_contact_address_id = address.jhd_address_id";
			
			/**
			 * BUILD WHERE STATEMENTS
			 * 
			 * Take the rules from the db and check if one of the things is true
			 * ownerId, SysOrg, Role or userID
			 * 
			 */
			$i = 0;
			
			//Only when there are rules
			if(count($accesRightValues) > 0){
				$usersWSysOrgsQuery .= " WHERE ";
				
				foreach($accesRightValues as $res){
				
					
					/**
					 * Check SysOrgs
					 * 
					 */
					$rightsSysOrgArray = explode(',', $res['systemorganisations']);
					
					if(is_array($rightsSysOrgArray) && count($rightsSysOrgArray) > 0 && $rightsSysOrgArray[0] != ''){
						
						foreach($rightsSysOrgArray as $sysOrgsRights){
								
							$usersWSysOrgsQuery .= "
							(SELECT 
								GROUP_CONCAT(jhd_system_organisations.id)
							FROM jhd_roles
							LEFT JOIN jhd_system_organisations ON jhd_roles.sysOrg = jhd_system_organisations.id
							WHERE account.role LIKE CONCAT('%',jhd_roles.roleName,'%')) 
							LIKE '%".$sysOrgsRights."%'
							OR ";
							
														
						}
					
					}
					
					/**
					 * Check roles
					 * 
					 */
					$rightsRolesArray = explode(',', $res['roles']);
					
					
					if(is_array($rightsRolesArray) && count($rightsRolesArray) > 0 && $rightsRolesArray[0] != ''){
						
						foreach($rightsRolesArray as $rightsRoles){
								
							$usersWSysOrgsQuery .= "account.role LIKE '%".$rightsRoles."%' OR ";
							//$usersWSysOrgsQuery .= "".$rightsRoles." IN(account.role) OR ";
							
							
						}
					
					}
					
					/**
					 * 
					 * Owner Id or userID
					 * 
					 */
					$usersWSysOrgsQuery .= "account.id = '".$res['ownerId']."'";
					
					if($res['users'] != ''){
						$usersWSysOrgsQuery .= " OR account.id IN(".$res['users'].")";
					}
					
					if(count($accesRightValues) < $i){
						$usersWSysOrgsQuery .= " OR ";
					}
					
					$i++;
					
				}
			
			}

			//var_dump($usersWSysOrgsQuery);
			
			$stmn = $this->db->{'database'}->prepare($usersWSysOrgsQuery);		
						
			$stmn->execute();
			
			$usersWSysOrgsValues = $stmn->fetchAll();
			
			
			
			return $usersWSysOrgsValues;
			
		}else{
				
			$accesRightValues = array();
			
		}
		
	}

	public function deleteRules($valueId, $module){
					
			
			$this->db->{'database'}->beginTransaction();
			//Not private anymore? Delete all things
			$query = "DELETE FROM jhd_".$module."_accessrights WHERE valueId = :valueId";
				
			$stmn = $this->db->{'database'}->prepare($query);		
			
			$stmn->bindValue(':valueId', $valueId, PDO::PARAM_STR);
			
			$stmn->execute();
			
			$this->db->{'database'}->commit();
			$stmn->closeCursor();
	}
	
	
}

?>
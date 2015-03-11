<?php

/**
 * Login class - login procedure
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */

class login{

	/**
	 * @access private
	 * @var integer user id from DB
	 */
	private $id = NULL;

	/**
	 * @access private
	 * @var string username from db
	 */
	private $username = NULL;
	
	/**
	 * @access private
	 * @var string username from db
	 */
	private $name = '';

	/**
	 * @access private
	 * @var string
	 */
	private $passwort = NULL;

	/**
	 * @access private
	 * @var string username (emailaddress)
	 */
	private $user = NULL;
	
	/**
	 * @access private
	 * @var string username (emailaddress)
	 */
	private $mail = NULL;

	/**
	 * @access private
	 * @var string
	 */
	private $session = NULL;

	/**
	 * @access private
	 * @var object - db connection
	 */
	private $db = NULL;

	/**
	 * @access public
	 * @var string userrole (admin, client, employee)
	 */
	public $role = '';
	
	/**
	 * @access public
	 * @var string userrole (admin, client, employee)
	 */
	public $sysOrgs = '';

	/**
	 * @access public
	 * @var integer time for cookie
	 */
	public $cookieTime = 7200;

	/**
	 * @access private
	 * @var object userobject
	 */
	private $userObj;
	
	/**
	 * @access private
	 * @var string Name of the table with the accounts
	 */
	private $accountTable = 'zp_user';

	/**
	 * @access public
	 * @var string
	 */
	public $error = "";
	
	/**
	 * @access public
	 * @var object
	 */
	public $hasher;

	/**
	 * __construct - getInstance of session and get sessionId and refers to login if post is set
	 *
	 * @param $sessionid
	 * @return boolean
	 */
	public function __construct($sessionid){

		$this->db = new db();

		$this->session = $sessionid;
		
		$this->hasher = new PasswordHash(14,TRUE);

		if (isset($_POST['login'])===true && isset($_POST['username'])===true && isset($_POST['password'])===true){

			$this->username = ($_POST['username']);
				
			$this->passwort = ($_POST['password']);
				
			if(isset($_POST['language']) === true){
				
				$_SESSION['language'] = htmlentities($_POST['language']);
			
			}
			
			if(isset($_POST['staysignedin']) === true){
				$this->cookieTime = 86400;
			}
			
			
			$this->login();
				
			//Check the sessions in the DB and delete sessionid if user hasn't done anything since $cookieTime
			$this->checkSessions();
				
		}

		if (isset($_GET['logout'])===true && $_GET['logout']==='1'){
				
			$this->logout();

		}

	}

	/**
	 * login - Validate POST-data with DB
	 *
	 * @access private
	 * @return boolean
	 */
	private function login(){
		
		$config = new config();
		
		if($this->getUserByLogin($this->username, $this->passwort) === true){
				
			//Set Sessions
			$_SESSION['userdata']['role'] = $this->role;
			$_SESSION['userdata']['id'] = $this->userId;
			$_SESSION['userdata']['name'] = $this->name;
			$_SESSION['userdata']['mail'] = $this->mail;
			$_SESSION['userdata']['systemOrgsId'] = $this->sysOrgs['id'];
			$_SESSION['userdata']['systemOrgsName'] = $this->sysOrgs['name'];
			$_SESSION['template'] = $this->getTemplate($this->role);
			$_SESSION['userdata']['addressMemory'] = "";
			$this->updateUserSession($this->session, time());

			$this->setCookie($this->cookieTime);
			
			
			
			return true;

		}elseif($this->username == $config->adminUserName && $this->passwort == $config->adminUserPassword){
			
			//Set Sessions
			$_SESSION['userdata']['role'] = 'admin';
			$_SESSION['userdata']['id'] = 'x';
			$_SESSION['userdata']['name'] = $config->adminUserName;
			$_SESSION['userdata']['mail'] = $config->adminEmail;
			
			$this->setCookie($this->cookieTime);
				
			return true;
			
		}else{
			
			$this->error = 'Username or password incorrect!';
			
			return false;

		}
	}

	/**
	 * setCookie - set and/or updates the cookie
	 *
	 * @param $time
	 * @return
	 */
	private function setCookie($time) {
			
		setcookie("sid", $this->session, time()+$time);

	}

	/**
	 * logged_in - Check if logged in and Update sessions
	 *
	 * @access public
	 * @return unknown_type
	 */
	public function logged_in(){

		$query = "SELECT count(username) AS userCounter FROM zp_user 
		          WHERE session = :session LIMIT 1";
		
		$stmn = $this->db->{'database'}->prepare($query);		
		
		$stmn->bindValue(':session', $this->session, PDO::PARAM_STR);
		
		$stmn->execute();
		
		$returnValues = $stmn->fetch();		
		
		$userCounter = $returnValues['userCounter'];
		
		//echo 'userCounter:'.$userCounter;		
		
		$stmn->closeCursor();
		
		if($userCounter !=1) {
			
			$config = new config();
			
			//Is Superadmin
			if(isset($_SESSION['userdata']['name']) === true && $_SESSION['userdata']['name'] == $config->adminUserName){
				
				
				
				$this->setCookie($this->cookieTime);
				
				$this->updateUserSession($this->session, time());
				
				return true;
				
			}else{
				
				$this->logout();

				return false;
			}
			//echo 'return false<br>';

		}else{
			//echo 'else<br>';	
			//Cookie still in time?
			
			if(isset($_COOKIE['sid']) === true){
					
				
				
				if(isset($_SESSION['userdata']) === true){
					
					$this->userId = $_SESSION['userdata']['id'];
						
					$this->setCookie($this->cookieTime);

					$this->updateUserSession($this->session, time());

					return true;

				}else{

					$this->logout();

					return false;

				}
					
			}else{

				$this->logout();

				return false;

			}
				
		}
	}

	/**
	 * showLogin - show Login-Template
	 *
	 * @access public
	 * @return
	 */
	public function showLogin(){

		include('./includes/modules/general/templates/login.tpl.php');
		

	}

	/**
	 * logout - destroy sessions and cookies
	 *
	 * @access private
	 * @return boolean
	 */
	private function logout(){

	$query = "UPDATE zp_user SET session = '' 
				 WHERE session = :sessionid LIMIT 1";

		$stmn = $this->db->{'database'}->prepare($query);
		
		$stmn->bindValue(':sessionid', $this->session, PDO::PARAM_STR);	
		$stmn->execute();				
		$stmn->closeCursor();

		$this->setCookie(time()-$this->cookieTime);

		unset($_SESSION['userdata']);
		unset($_SESSION['template']);

		unset($_COOKIE);

		return true;
	}

	/**
	 * checkSessions - check the sesisions in the database and unset them if necessary
	 *
	 * @access private
	 * @return
	 */
	private function checkSessions() {

		
		$query = "UPDATE zp_user SET session = '' WHERE (".time()." - sessionTime) > ".$this->cookieTime." ";

		$stmn = $this->db->{'database'}->prepare($query);		
		$stmn->execute();				
		$stmn->closeCursor();

	}
	
	

/**
	 * getUserByLogin - Check login data andset email vars
	 *
	 * @access public
	 * @param $emailname
	 * @param $password
	 * @return boolean
	 */
	public function getUserByLogin($username, $password) {

		$query = "SELECT username, password FROM zp_user 
		          WHERE username = :username LIMIT 1";
				
		$stmn = $this->db->{'database'}->prepare($query);
		$stmn->bindValue(':username', $username, PDO::PARAM_STR);
		
		$stmn->execute();
		
		$returnValues = $stmn->fetch();		
		
		$userCounter=count($returnValues);
		
		$stmn->closeCursor();
		
		//echo 'getUserByLogin count:'. $userCounter;
		
		if($userCounter === false || !$this->hasher->CheckPassword($password, $returnValues['password'])) {
			
			//echo 'return false<br>';
			unset($_SESSION['userdata']);
			unset($_SESSION['template']);

			unset($_COOKIE);
			
			return false;

		}else{

			//
			$query = "SELECT 
					id,
					username,
					role,
					lastname AS name
					
						FROM zp_user 
			          WHERE username = :username
			          LIMIT 1";
					 //echo $query;
			
			$stmn = $this->db->{'database'}->prepare($query);
			$stmn->bindValue(':username', $username, PDO::PARAM_STR);
			
			$stmn->execute();
			$returnValues = $stmn->fetch();	
			$stmn->closeCursor();
			
			$this->name = $returnValues['name'];
			$this->mail = $returnValues['username'];
			$this->userId = $returnValues['id'];
			
			$user = new users();
			$roles = $user->getRole($returnValues['role']);
			$this->role = $roles['roleName'];
			
			$setting = new setting();
			$roleArray = explode(',', $this->role);
			
			$this->sysOrgs = $setting->getSysOrgsStringByRoles($roleArray);
			
			return true;
		}
	}

	/**
	 * updateemailSession - Update the sessiontime of a email
	 *
	 * @access public
	 * @param $sessionid
	 * @param $time
	 * @return
	 */
	public function updateUserSession($sessionid, $time){
		//echo "updateUserSession";
		$query = "UPDATE
					zp_user 
				SET 
					lastlogin = :date,
					session = :sessionid,
					sessionTime = :time 
				WHERE 
					id =  :id 
				LIMIT 1";		

		
		$stmn = $this->db->{'database'}->prepare($query);
		$stmn->bindValue(':date', date("Y-m-d h:i:s", time()), PDO::PARAM_INT);
		$stmn->bindValue(':id', $this->userId, PDO::PARAM_INT);
		$stmn->bindValue(':sessionid', $sessionid, PDO::PARAM_STR);
		$stmn->bindValue(':time', $time, PDO::PARAM_STR);
		$stmn->execute();		
				
		$stmn->closeCursor();
	}
	
	public function getTemplate($roles){
		
		$rolesArray = explode(',', $roles);
		
		if(isset($rolesArray[0]) === true){
			$checkFor = $rolesArray[0];
			
			$query = "SELECT 
				zp_roles.template FROM zp_roles
				WHERE 
					roleName =  :role
				LIMIT 1";		

		
				$stmn = $this->db->{'database'}->prepare($query);
				$stmn->bindValue(':role', $checkFor, PDO::PARAM_INT);
				
				$stmn->execute();
				$returnValues = $stmn->fetch();					
				$stmn->closeCursor();
				
				if(isset($returnValues['template']) &&  $returnValues['template'] != '') {
					
					$template = $returnValues['template'];
										
				}else{
				
					$template = 'zypro';
				
				}
			
			
		}else{
			$template = 'zypro';
		}
			
		return $template;
	}
	
	
}

?>
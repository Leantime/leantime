<?php

//get language-File for labels
$language = new language();
$config = new config();
	
$language->setModule('general');

$lang = $language->readIni();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<meta name="description" content="zypro - OpenSource Ticketsystem" />
<meta name="keywords" content="" />
<meta name="robots" content="index,follow" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>  

<link rel="stylesheet" type="text/css"
	href="includes/modules/general/templates/css/mobile.css" />
<title>zypro - Ticketsystem</title>
</head>
<body>

<div id="head">

<div class="sitename"><?php echo $config->sitename; ?></div>

</div>
<?php 
if(isset($_GET['logout'])===true){
	
	$referer=$_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']).'/index.php?act=general.showMenu';

}elseif(isset($_SERVER['REQUEST_URI'])===true && $_SERVER['REQUEST_URI']=='index.php'){
	
	$referer=$_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']).'/index.php?act=general.showMenu';
	

}else{
	$referer=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}
?>

<div id="login">

<h1><?php echo $lang['LOGIN']; ?></h1><br /><br />

<form method="post"
	action="<?php echo 'http://'.$referer; ?>">



<?php if($this->error != ''){?> <span style="color: red;"><?php echo $lang[$this->error]; ?><br />
</span> <br />
<?php } ?> 

<label for="username"><?php echo $lang['USERNAME']; ?></label><br />
<input type="text" name="username" id="username"
	class="width140" /><br /> 
<br />

<label for="password"><?php echo $lang['PASSWORD']; ?></label> <br />
<input type="password" name="userpass" id="password" class="width140" /><br />
<br />

<input name="login" type="submit" class="button"
	value="<?php echo $lang['LOGIN_BUTTON']; ?>" />
</form>

</div>

</body>
</html>

<?php 
	
	$config = new config();
	$login = $this->get('login');
?>

<div id="login">
<form method="post" action="index.php?act=general.menu">


	
	<?php if($login->error != ''){?>
		<span style="color: red;"><?php echo $login->error; ?></span> 
	<?php } ?>
	
	<label for="username">Benutzername</label> 
	<input type="text" name="username" id="username"/> <br />
	<label for="password">Passwort</label> 
	<input type="password" name="userpass" id="password" /> <br />
	<br />
	<input name="login" type="submit" class="button" value="Login" />


</form>

</div>


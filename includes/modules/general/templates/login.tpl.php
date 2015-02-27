<?php 
	
	$config = new config();
?>

<div id="login">

	<form method="post" action="index.php?act=dashboard.show">
	
		<fieldset style="background-color: #970F1D;">
			
			
			<?php if($this->error != ''){?>
				<span style="color: red;"><?php echo $this->error; ?></span> 
			<?php } ?>
			<div style="text-align: left">
			<strong>Anmeldung</strong>
			</div><br />
			<label for="username">Benutzername</label> 
			<input type="text" name="username" id="username"/> <br /> <br />
			<label for="password">Passwort</label> 
			<input type="password" name="userpass" id="password" /> <br />
			<br />
			<div style="text-align:right;">
				<input name="login" type="submit" class="button" value="Login" /><br />
				
				<!--
				<a href="index.php?act=clientPortal.clientRegistration">Registrieren</a> | 
				<a href="index.php?act=clientPortal.forgotPassword">Passwort vergessen</a>
			    -->
			</div>
			<div style="clear:both">&nbsp;</div>
		</fieldset>
	</form>
	Bisher nur für eine geschlossene Benutzergruppe.
	<div style="clear:both">&nbsp;</div>
</div>


<?php $main->includeAction('general.header'); ?>

<div id="head">
	<div class="versionContainer">
		<?php if(isset($_SESSION['userdata']['name']) === true){
			
			echo 'Angemeldet als '.$_SESSION['userdata']['name'];
			
		}
		?>
	
	</div>
	
	<?php $config = new config(); ?>
	
	<div class="sitename">&nbsp;<?php echo $config->sitename ?> </div>
	
	<?php $main->includeAction('general.menu'); ?>
</div>


<div id="content">
	<noscript>
	
		<div class="info">
			<span style="color: #FF0000;">Ihr Javascript ist
			ausgeschaltet. Ohne aktiviertes Javascript ist das System nur
			eingeschr&auml;nkt nutzbar</span>
		</div>
	
	</noscript>

	<?php $main->run(); ?>

</div>

<div id="footer"><?php $main->includeAction('general.footer'); ?></div>

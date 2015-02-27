<?php 
defined( 'RESTRICTED' ) or die( 'Restricted access' );
$roles = $this->get('roles'); 
?>

<h1>Benutzer löschen</h1>

<?php if($this->get('msg') === '') { ?>

	<form method="post" accept-charset="utf-8">
	<fieldset><legend>Löschen bestätigen</legend>
	<p>Soll diese Systemorganisation wirklich gelöscht werden? Alle Rollen dazu werden ebenfalls gelöscht!<br />
	Wählen Sie eine neue Rolle für die Benutzer der zu löschenden Rolle aus:
	<br /><select
	name="newRole" id="newRole">
	<?php foreach($roles as $row){ 
		if($row['id'] != $_GET['id']){
	?>
	
	<option value="<?php  echo $row['roleName']; ?>"
	<?php if($row['roleName'] == $values['role']){ ?> selected="selected" <?php } ?>><?php echo $row['roleDescription']; ?></option>

	<?php } 
		}?>
</select> <br />
	</p>
	<input type="submit" value="Löschen" name="del"
		class="button"></fieldset>
	</form>

<?php }else{ ?>

	<span class="info"><?php echo $this->get('msg'); ?></span>

<?php } ?>
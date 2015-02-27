<?php
defined( 'RESTRICTED' ) or die( 'Restricted access' );
?>
<script type="text/javascript">
	$(document).ready(function() 
    	{ 
        	$('#comments').pager('div');
 
    	} 
	); 
    
</script>

<?php
$client = $this->get('client');
?>

<h1><?php echo $client['name']; ?></h1>

<fieldset><legend><?php echo $lang['CLIENT_DETAILS']; ?></legend>

<p><strong><?php echo $client['name']; ?></strong><br />
<br />
<strong><?php echo $lang['CLIENT_DETAILS']; ?>:</strong> <?php echo $client['street']; ?><br />
<strong><?php echo $lang['ZIP']; ?>, <?php echo $lang['CITY']; ?>:</strong>
<?php echo ''.$client['zip'].', '.$client['city'].''; ?><br />
<strong><?php echo $lang['STATE']; ?>, <?php echo $lang['COUNTRY']; ?>:</strong>
<?php echo ''.$client['state'].', '.$client['country'].''; ?><br />
<br />
<strong><?php echo $lang['PHONE']; ?>:</strong> <?php echo $client['phone']; ?><br />
<strong><?php echo $lang['URL']; ?>:</strong> <a href="<?php echo $client['internet']; ?>"><?php echo $client['internet']; ?></a>
</p>
</fieldset>

<?php if($this->get('admin') === true){ ?>

<a
	href="index.php?act=clients.editClient&id=<?php echo $client['id']; ?>" class="link"><?php echo $lang['EDIT']; ?></a>
<a
	href="index.php?act=clients.delClient&id=<?php echo $client['id']; ?>" class="link"><?php echo $lang['DELETE']; ?></a>

<?php } ?>

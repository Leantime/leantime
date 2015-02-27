<?php

defined( 'RESTRICTED' ) or die( 'Restricted access' );
$account = $this->get('account');

?>

<h1>Accounts</h1>

<table cellpadding="0" cellspacing="0" border="0" class="allTickets" id="allTickets">
	<thead>
		<tr>
			<th>Role</th>
			<th>Name</th>
			<th>User</th>
			<th>Phone</th>
		</tr>
	</thead>
	<tbody>
		
		<?php foreach($this->get('account') as $row) { ?>
		<tr>
			<td><?php echo $row['role']; ?></td>
			<td><a href='?act=accounts.editAccounts'><?php echo $row['firstname'] ." ". $row['lastname']; ?></a></td>
			<td><a href='?act=accounts.editAccounts'><?php echo $row['user']; ?></a></td>
			<td><?php echo $row['phone']; ?></td>
		</tr>

		<?php } ?>

	</tbody>
</table>

<div class="footerEdit">
<hr />
<p>
  <a href="index.php?act=accounts.editAccounts">Edit<?php // echo $lang['EDIT']; ?></a>
| <a href="index.php?act=accounts.deleteAccounts">Delete<?php // echo $lang['DELETE']; ?></a>
</p>
</div>
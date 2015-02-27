
<div id="menu">

<a href="index.php?act=tickets.showAll"><?php echo $lang['TICKETS']; ?></a>
<a href="index.php?act=projects.showAll"><?php echo $lang['PROJECTS']; ?></a>

<?php

if($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'employee'){
	echo'<a href="index.php?act=clients.showAll">'.$lang['CLIENTS'].'</a>';
//	echo'';
}


if($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'employee'){
	echo'<a href="index.php?act=timesheets.showMy">'.$lang['TIMESHEETS'].'</a>';
	echo'<a href="index.php?act=wiki.showAll">'.$lang['WIKI'].'</a>';
	echo'<a href="index.php?act=calendar.showMyCalendar">Kalender</a>';
}

if($_SESSION['userdata']['role'] == 'admin') {
	echo'<a href="index.php?act=users.editOwn">'.$lang['OWNDATA'].'</a>';
}

?>
<a
	href="?logout=1&sid=<? echo session::getSID() ?>"><?php echo $lang['LOGOUT']; ?></a>

</div>
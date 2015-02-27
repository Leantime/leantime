<?php
session_start();
include_once 'core/class.autoload.php';
include_once 'config/configuration.php';
$config = new config();
$con = mysql_connect($config->dbHost, $config->dbUser, $config->dbPassword);
mysql_select_db($con, $config->dbDatabase);
$id = mysql_real_escape_string($id);
$query = mysql_query("SELECT * FROM zp_export WHERE id=".$id);
$filename = "";
$userId = $_GET['userId'];
$ticketId = $_GET['ticketId'];
header('Content-type: application/ms-excel');
header('Content-Disposition: attachment, filename='.$filename);
?>
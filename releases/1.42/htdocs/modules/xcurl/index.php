<?php
include ('../../mainfile.php');
if (empty($_POST)&&empty($_GET)) {
	header('Location: '.XOOPS_URL);
	exit;
} else {
	error_reporting(0);
	require ('include/server.php');
	exit;
}
?>
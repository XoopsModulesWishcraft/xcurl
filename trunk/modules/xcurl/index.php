<?php
$xml = $HTTP_RAW_POST_DATA;
include ('../../mainfile.php');
if (empty($xml)) {
	header('Location: '.XOOPS_URL);
	exit;
} else {
	error_reporting(0);
	require ('include/server.php');
	exit;
}
?>
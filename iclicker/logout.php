<?php
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	clearLogin($conn);
	
	header("Location: home.php");
?>
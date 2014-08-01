<?php

function closeConn($conn) {
	$conn->close();
}

function connect() {
	$dbhost = 'localhost';
	$dbuser = 'root';
	$dbpass = 'root';
	//$dbname = 'iclicker';
	$dbname = 'cs147';
		
	$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname) or die ('Error connecting to mysql ' . mysqli_connect_error());
	register_shutdown_function('closeConn', $conn);
	return $conn;
}


?>
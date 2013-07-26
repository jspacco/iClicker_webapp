<?php
	function connect() {
		$dbhost = 'localhost';
		$dbuser = 'root';
		$dbpass = '';
		$dbname = 'iclicker';
		
		$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname) or die ('Error connecting to mysql ' . mysqli_connect_error());
		return $conn;
	}
?>
<?php
	function createHeader($title, $includeLogout = true, $extra = "") {
		echo "
			<html>
			<head>
				<link rel='stylesheet' type='text/css' href='stylesheet.css'>
				<title>" . $title . "</title>
				" . $extra . "
			</head>
			<header>
		";
		
		if ($includeLogout) {
			echo "
				<a href='logout.php'>Logout</a>
			";
		}
		
		echo "
			</header>
		";
	}
	
	function createFooter($goBack = false, $goBackLink = "#") {
		echo "
			<footer>
		";
		
		if ($goBack) {
			echo "
				<a href='" . $goBackLink . "'>Go Back</a>
			";
		}
		
		echo "
				<a href='home.php'>Back to Home</a>
			</footer>
			</html>
		";
	}
?>
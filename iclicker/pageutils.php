<?php
function createHead($title, $includeLogout = true, $extra = "") {
	$logoutHtml="";
	if ($includeLogout) {
		$logoutHtml="<a href='logout.php'>Logout</a>";
	}
?>
	<html>
	 <head>
		<link rel='stylesheet' type='text/css' href='css/stylesheet.css' />
		<link rel='stylesheet' type='text/css' href='css/lightbox.css' />
		<link rel='stylesheet' type='text/css' href='css/jquery-ui-1.10.3.custom.min.css' />
		<script src='js/jquery-1.10.2.min.js'></script>
		<script src='js/jquery-ui-1.10.3.custom.min.js'></script>
		<script src='js/lightbox-2.6.min.js'></script>
		<title><?=$title?></title>
		<?=$extra?>
	 </head>
	 <body>
	 <header> <?=$logoutHtml?> </header>
	 
<?php
}

function createHeader($title, $includeLogout = true, $extra = "") {
	createHead($title, $includeLogout, $extra);
	echo "<div class='main'>";
}

	function createFooter($goBack = false, $goBackLink = "#") {
		echo "
				</div>

			<footer class='main'>
		";
		
		if ($goBack) {
			echo "
				<a href='$goBackLink'>Go Back</a>
			";
		}
		
		echo "
				<a href='home.php'>Back to Home</a>
			</footer>
			</body>
			</html>
		";
	}

	function endOutput($endMessage){
		ignore_user_abort(true);
		set_time_limit(0);
		header("Connection: close");
		header("Content-Length: ".strlen($endMessage));
		echo $endMessage;
		echo str_repeat("\r\n", 10); // just to be sure
		flush();
	}

	function checkAdmin($conn) {
		if (!isCookieValidLoginWithType($conn, "admin")) {
			header("Location: home.php");
		}
	}
	
	function DateFromUTC($utc) {
		return date("l, F j, g:i a", $utc);
	}

function dayOfYear($session_date) {
	date_default_timezone_set('America/Chicago');
	$d = DateTime::createFromFormat("m/d/y H:i", $session_date);
	return date("z", $d->getTimestamp()) + 1;
}

function lastSunday($session_date) {
	date_default_timezone_set('America/Chicago');
	$d = DateTime::createFromFormat("m/d/y H:i", $session_date);
	return date('z', strtotime('Last Sunday', $d->getTimestamp())) + 1;
}

function dayOfWeek($session_date) {
	date_default_timezone_set('America/Chicago');
	$d = DateTime::createFromFormat("m/d/y H:i", $session_date);
	return date('l', $d->getTimestamp());
}

function currentWeek($session_date, $dayOne) {
	return (int)((dayOfYear($session_date) - $dayOne) / 7) + 1;
}

function td($str) {
	return "<td> $str </td>\n";
}

function th($str) {
	return "<th> $str </th>\n";
}

function tr($str) {
	return "<tr> $str </tr>\n";
}

?>
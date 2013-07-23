<?php
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();

	$user = $_POST["username"];
	$pass = $_POST["password"];
	$type = $_POST["logintype"];

	setLoginCookie($conn, $user, $pass, $type);
	
	// We can't use the cookie variables because they won't be sent to the page without a refresh
	// see: http://stackoverflow.com/questions/3230133/accessing-cookie-immediately-after-setcookie
	$pass = crypt($pass, 'a8hd9j2');
?>
<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' type='text/css' href='stylesheet.css'>
</head>
<body>
	<div>
<?php
	if (isValidLogin($conn, $user, $pass, $type)) {
		echo "Login successful!";
	} else {
		echo "Login failed.";
	}
?>
	</div>
</body>
</html>
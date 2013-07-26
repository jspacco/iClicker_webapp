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
	$pass = getEncrypted($pass);
	
	if (isValidLogin($conn, $user, $pass, $type)) {
		header("Location: home.php");
	}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' type='text/css' href='stylesheet.css'>
</head>
<body>
	<div>
		<p>Login Failed!</p>
	</div>
</body>
<?php
	$conn->close();
?>
<footer>
	<a href='home.php'>Back to Home</a>
</footer>
</html>
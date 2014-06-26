<?php
	require_once("pageutils.php");
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
	
	createHeader("End Login", false, false);
?>
<body>
	<div>
		<p>Login Failed!</p>
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>

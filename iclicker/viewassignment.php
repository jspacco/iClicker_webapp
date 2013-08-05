<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHeader("Assignment Page");
?>

<?php
	$conn->close();
	createFooter(true, "studentpage.php");
?>
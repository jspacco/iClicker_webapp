<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Student");
?>
<body>
	<div>
		<!--
			Content goes here
		!-->
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>
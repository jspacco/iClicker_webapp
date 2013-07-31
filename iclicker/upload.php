<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Upload");
?>
<body>
	<div>
		<form action="endupload.php" method="post" enctype="multipart/form-data">
			<label>Filename:</label>
			<input name="file" type="file">
			<input type="submit" value="Submit">
		</form>
	</div>
</body>
<?php
	$conn->close();
	
	createFooter();
?>
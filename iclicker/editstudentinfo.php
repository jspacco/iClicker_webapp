<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHeader("Edit Info");
?>
<body>
	<div>
<?php
	$query = "
		SELECT student_id, school_id, email FROM students WHERE
		username = ? AND
		password = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't execute 'student_id' query. " . $conn->error);
	$stmt->bind_param("ss", $_COOKIE["Username"], $_COOKIE["Password"]);
	$stmt->execute() or die("Couldn't execute 'student_id' query. " . $conn->error);
	
	$stmt->bind_result($student_id, $school_id, $email);
	$stmt->fetch();
	
	// $result = $stmt->get_result();
	// $row = $result->fetch_array(MYSQLI_ASSOC);
	
	// $student_id = $row["student_id"];
	// $email = $row["email"];
?>
	<form action="endstudentedit.php" method="post">
		School ID: <input type='text' name='school_id' value=<?php echo $school_id; ?>><br>
		Email: <input type='text' name='email' value=<?php echo $email; ?>><br>
		<input type='submit' value='Submit'>
	</form>
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>
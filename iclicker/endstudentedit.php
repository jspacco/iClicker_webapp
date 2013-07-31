<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	$email = $_POST["email"];
	
	$query = "
		SELECT student_id FROM students WHERE
		username = ? AND
		password = ?;
	";

	$stmt = $conn->prepare($query) or die("Couldn't execute 'student_id' query. " . $conn->error);
	$stmt->bind_param("ss", $_COOKIE["Username"], $_COOKIE["Password"]);
	$stmt->execute() or die("Couldn't execute 'student_id' query. " . $conn->error);

	$stmt->bind_result($student_id);
	$stmt->fetch();
	
	// $result = $stmt->get_result();
	// $row = $result->fetch_array(MYSQLI_ASSOC);
	
	// $student_id = $row["student_id"];
	
	$query = "
		UPDATE students SET email = ? WHERE student_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't execute 'email' query. " . $conn->error);
	$stmt->bind_param("si", $email, $student_id);
	$result = $stmt->execute() or die("Couldn't execute 'email' query. " . $conn->error);
	
	if ($result) {
		header("Location: home.php");
	}
	
	createHeader("End Edit");
?>
<body>
	<div>
		<p>Edit was not successful!<br></p>
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>
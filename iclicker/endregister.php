<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	createHeader("End Registration", false);
?>
<body>
	<div>
<?php
	$iclicker = $_POST["iclicker_id"];
	$iclicker_alt = "#" . $iclicker;
	
	$query = "
		SELECT distinct student_id FROM students WHERE
		iclicker_id = ? OR
		iclicker_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'select' query. " . $conn->error);
	$stmt->bind_param("ss", $iclicker, $iclicker_alt);
	$stmt->execute() or die("Couldn't execute 'select' query. " . $conn->error);
	
	$stmt->store_result();
	
	// $result = $stmt->get_result();
	
	if ($stmt->num_rows > 0) {
		$stmt->bind_result($student_id);
		$stmt->fetch();
		
		// $row = $result->fetch_array(MYSQLI_ASSOC);
		// $student_id = $row["student_id"];
		$username = $_POST["username"];
		$password = $_POST["password"];
		$password = getEncrypted($password);
		
		$query = "
			UPDATE students SET
			username = ?, password = ? WHERE
			student_id = ?;
		";
		
		$stmt = $conn->prepare($query) or die("Couldn't prepare 'update' query. " . $conn->error);
		$stmt->bind_param("ssi", $username, $password, $student_id);
		$stmt->execute() or die("Couldn't execute 'update' query. " . $conn->error);
		
		echo "Registration successful!.<br>";
	} else {
		echo "Couldn't find any student record with iClicker ID " . $iclicker . " or " . $iclicker_alt . ".<br>";
	}
?>
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>
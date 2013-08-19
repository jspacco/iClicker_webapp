<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHeader("Submitting answer...");
?>
<body>
	<div>
<?php
	$question_id = (int) $_GET["question_id"];
	$answers = $_POST["answers"];
	$start_time = $_POST["start_time"];
	$time = time();
	$answer = "";
	
	foreach ($answers as $a) {
		if ($a == 'A' ||
			$a == 'B' ||
			$a == 'C' ||
			$a == 'D' ||
			$a == 'E')
			$answer = $answer . $a . ",";
		else
			continue;
	}
	$answer = trim($answer, ",");
	
	$user = $_COOKIE["Username"];
	$pass = $_COOKIE["Password"];
	
	$query = "
		SELECT student_id FROM students WHERE
		username = ? AND
		password = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'select' query. " . $conn->error);
	$stmt->bind_param("ss", $user, $pass);
	$stmt->execute() or die("Couldn't execute 'select' query. " . $conn->error);
	
	$stmt->bind_result($student_id);
	$stmt->fetch();
	$stmt->close();
	
	// $result = $stmt->get_result();
	
	// $row = $result->fetch_array(MYSQLI_ASSOC);
	
	// $student_id = $row["student_id"];
	
	// $query = "
		// DELETE FROM onlineresponses WHERE
		// question_id = ? AND
		// student_id = ?;
	// ";
	
	// $stmt = $conn->prepare($query) or die("Couldn't prepare 'delete' query. " . $conn->error);
	// $stmt->bind_param("ii", $question_id, $student_id);
	// $stmt->execute() or die("Couldn't execute 'delete' query. " . $conn->error);
	// $stmt->close();
	
	$query = "
		INSERT INTO onlineresponses (question_id, student_id, response, start_time, end_time) VALUES (?, ?, ?, ?, ?);
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'insert' query. " . $conn->error);
	$stmt->bind_param("iisii", $question_id, $student_id, $answer, $start_time, $time);
	$stmt->execute() or die("Couldn't execute 'insert' query. " . $conn->error);
	
	echo "
		Answer submitted successfully!<br>
	";
?>
	</div>
</body>
<?php
	$conn->close();
	if (isset($_POST["assignment_id"])) {
		$assignment_id = $_POST["assignment_id"];
		createFooter(true, "viewassignment.php?assignment_id=$assignment_id");
	} else {
		createFooter();
	}
?>
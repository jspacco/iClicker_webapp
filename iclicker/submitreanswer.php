<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHeader("Submitting answer...");
	
	$assignment_id = $_POST["assignment_id"];
	$question_id = $_POST["question_id"];
	$answers = $_POST["answers"];
	$start_time = $_POST["start_time"];
	$time = time();
	$answer = "";
	
	foreach ($answers as $a) {
		if ($a == 'A' || $a == 'B' || $a == 'C' || $a == 'D' ||	$a == 'E') {
			$answer = $answer . $a . ",";
		} else {
			continue;
		}
	}
	$answer = trim($answer, ",");
	
	$student_id = getStudentIdFromCookie($conn);
	
	$query = "
		INSERT INTO onlineresponses (question_id, student_id, response, start_time, end_time) 
		VALUES (?, ?, ?, ?, ?)
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'insert' query. " . $conn->error);
	$stmt->bind_param("iisii", $question_id, $student_id, $answer, $start_time, $time);
	$result = $stmt->execute() or die("Couldn't execute 'insert' query. " . $conn->error);
	
	if ($result) {
		header("Location: questionreport.php?question_id=$question_id&assignment_id=$assignment_id");
	}
	
	logs($conn, $student_id);
	$conn->close();
	if (isset($_POST["assignment_id"])) {
		$assignment_id = $_POST["assignment_id"];
		createFooter(true, "viewassignment.php?assignment_id=$assignment_id");
	} else {
		createFooter();
	}
?>
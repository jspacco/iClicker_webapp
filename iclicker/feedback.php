<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Feedback Page");
	
	$question_id = $_GET["question_id"];
	
	$query = "
		SELECT question_name 
		FROM questions 
		WHERE question_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'question_number' query. " . $conn->error);
	$stmt->bind_param("i", $question_id);
	$stmt->execute() or die("Couldn't execute 'question_number' query. " . $conn->error);
	$stmt->bind_result($question_name);
	$stmt->fetch();
	$stmt->close();
	
	echo"
		<h1>$question_name Feedback</h1>
	";
	
	$query = "
		SELECT feedback 
		FROM onlineresponses 
		WHERE question_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'feedback' query. " . $conn->error);
	$stmt->bind_param("i", $question_id);
	$stmt->execute() or die("Couldn't execute 'feedback' query. " . $conn->error);
	$stmt->bind_result($feedback);
	
	while ($stmt->fetch()) {	
		if ($feedback != "") {
			echo"
				- $feedback
				<br>
			";
		}
	}
	$stmt->close();
?>

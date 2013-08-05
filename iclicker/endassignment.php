<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Creating Assignment...");
	
	$due = $_POST["due"];
	$questions = $_POST["questions"];
	$section_id = $_POST["section_id"];
	
	$query = "
		INSERT INTO assignments (section_id, due) VALUES (?, ?);
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'assignments' query. " . $conn->error);
	$stmt->bind_param("is", $section_id, $due);
	$stmt->execute() or die("Couldn't execute 'assignments' query. " . $conn->error);
	$stmt->close();
	
	$query = "
		SELECT assignment_id FROM assignments WHERE
		section_id = ? AND
		due = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'select' query. " . $conn->error);
	$stmt->bind_param("is", $section_id, $due);
	$stmt->execute() or die("Couldn't execute 'select' query. " . $conn->error);
	
	$stmt->bind_result($assignment_id);
	$stmt->fetch();
	$stmt->close();
	
	foreach ($questions as $question_id) {
		$query = "
			INSERT INTO assignmentstoquestions (assignment_id, question_id) VALUES (?, ?);
		";
		
		$stmt = $conn->prepare($query) or die("Couldn't prepare 'atq' query. " . $conn->error);
		$stmt->bind_param("ii", $assignment_id, $question_id);
		$stmt->execute() or die("Couldn't execute 'atq' query. " . $conn->error);
		$stmt->close();
	}
?>
	<p>Assignment created successfully!</p>
<?php
	$conn->close();
	createFooter();
?>
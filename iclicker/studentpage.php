<?php
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
?>
<html>
<head>
	<link rel='stylesheet' type='text/css' href='stylesheet.css'>	
</head>
<header>
	<a href="logout.php">Logout</a>
</header>
<body>
	<div>
<?php
	$query = "
	SELECT student_id FROM students WHERE
	username = ? AND
	password = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't execute 'student_id' query. " . $conn->error);
	$stmt->bind_param("ss", $_COOKIE["Username"], $_COOKIE["Password"]);
	$stmt->execute() or die("Couldn't execute 'student_id' query. " . $conn->error);
	
	$result = $stmt->get_result();
	$row = $result->fetch_array(MYSQLI_ASSOC);
	
	$student_id = $row["student_id"];
	
	echo "
	<table class='collection'>
	<tr>
	<th>Course</th>
	<th>#</th>
	<th>Section</th>
	<th>Session Date</th>
	<th>Question #</th>
	</tr>
	";
	
	$query = "
	SELECT course_name, course_number, section_number, session_date, question_number, responses.question_id FROM courses, sections, sessions, questions, responses WHERE
	courses.course_id = sections.course_id AND
	sections.section_id = sessions.section_id AND
	sessions.session_id = questions.session_id AND
	questions.question_id = responses.question_id AND
	responses.student_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'select' query. " . $conn->error);
	$stmt->bind_param("i", $student_id);
	$stmt->execute() or die("Couldn't execute 'select' query. " . $conn->error);
	
	$result = $stmt->get_result();
	
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		echo "
		<tr>
		<td><a href='reanswerquestion.php?question_id=" . $row["question_id"] . "'>" . $row["course_name"] . "</a></td>
		<td><a href='reanswerquestion.php?question_id=" . $row["question_id"] . "'>" . $row["course_number"] . "</a></td>
		<td><a href='reanswerquestion.php?question_id=" . $row["question_id"] . "'>" . $row["section_number"] . "</a></td>
		<td><a href='reanswerquestion.php?question_id=" . $row["question_id"] . "'>" . $row["session_date"] . "</a></td>
		<td><a href='reanswerquestion.php?question_id=" . $row["question_id"] . "'>" . $row["question_number"] . "</a></td>
		</tr>
		";
	}
	
	echo "
	</table>
	";
?>
	</div>
</body>
<?php
	$conn->close();
?>
<footer>
	<a href='home.php'>Back to Home</a>
</footer>
</html>
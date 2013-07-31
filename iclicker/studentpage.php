<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHeader("Student Page");
?>
<body>
	<div>
		<h2>Questions</h2>
<?php
	$query = "
		SELECT student_id FROM students WHERE
		username = ? AND
		password = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'student_id' query. " . $conn->error);
	$stmt->bind_param("ss", $_COOKIE["Username"], $_COOKIE["Password"]);
	$stmt->execute() or die("Couldn't execute 'student_id' query. " . $conn->error);
	
	$stmt->bind_result($student_id);
	$stmt->fetch();
	$stmt->close();
	
	// $result = $stmt->get_result();
	// $row = $result->fetch_array(MYSQLI_ASSOC);
	
	// $student_id = $row["student_id"];
?>
	<table class='collection'>
		<tr>
			<th>Course</th>
			<th>#</th>
			<th>Section</th>
			<th>Session Date</th>
			<th>Question #</th>
		</tr>
<?php
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
	
	$stmt->bind_result($course_name, $course_number, $section_number, $session_date, $question_number, $question_id);
	
	// $result = $stmt->get_result();
	
	while ($stmt->fetch()/*$row = $result->fetch_array(MYSQLI_ASSOC)*/) {
		echo "
			<tr>
				<td><a href='reanswerquestion.php?question_id=" . $question_id . "'>" . $course_name . "</a></td>
				<td><a href='reanswerquestion.php?question_id=" . $question_id . "'>" . $course_number . "</a></td>
				<td><a href='reanswerquestion.php?question_id=" . $question_id . "'>" . $section_number . "</a></td>
				<td><a href='reanswerquestion.php?question_id=" . $question_id . "'>" . $session_date . "</a></td>
				<td><a href='reanswerquestion.php?question_id=" . $question_id . "'>" . $question_number . "</a></td>
			</tr>
		";
	}
?>
		</table>
		<a href='editstudentinfo.php'>Edit Info</a>
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>
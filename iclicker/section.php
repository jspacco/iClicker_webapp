<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Section");
?>
<body>
	<div>
		<h2>Sessions</h2>
		<table class='collection'>
			<tr>
				<th>Date</th>
			</tr>
<?php
	$section_id = $_GET["section_id"];
	
	$query = "
		SELECT session_id, session_date FROM sessions WHERE
		section_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare sessions query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute sessions query. " . $conn->error);
	
	$stmt->bind_result($session_id, $session_date);
	// $result = $stmt->get_result();
	
	while ($stmt->fetch()/*$row = $result->fetch_array(MYSQLI_ASSOC)*/) {
		echo "
			<tr>
				<td><a href='session.php?session_id=" . $session_id . "'>" . $session_date . "</a></td>
			</tr>
		";
	}
	
	echo "
		</table>
		<br>
	";
	
	echo "
		<h2>Students</h2>
		<table class='collection'>
			<tr>
				<th>School ID</th>
				<th>iClicker ID</th>
				<th>Name</th>
			</tr>
	";
	
	$query = "
		SELECT distinct students.student_id, iclicker_id, school_id, first_name, last_name FROM students, sections, sessions, questions, responses WHERE
		students.student_id = responses.student_id AND
		responses.question_id = questions.question_id AND
		questions.session_id = sessions.session_id AND
		sessions.section_id = sections.section_id AND
		sections.section_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare students query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute students query. " . $conn->error);
	
	$stmt->bind_result($student_id, $iclicker_id, $school_id, $first_name, $last_name);
	
	// $result = $stmt->get_result();
	
	while ($stmt->fetch()/*$row = $result->fetch_array(MYSQLI_ASSOC)*/) {
		echo "
			<tr>
				<td><a href='student.php?student_id=" . $student_id . "'>" . $school_id . "</a></td>
				<td><a href='student.php?student_id=" . $student_id . "'>" . $iclicker_id . "</a></td>
				<td><a href='student.php?student_id=" . $student_id . "'>" . $last_name . ", " . $first_name . "</a></td>
			</tr>
		";
	}
	
	echo "</table>";
?>
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>
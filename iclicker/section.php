<?php
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
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
		<table class='collection'>
			<tr>
				<th>Sessions</th>
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
	
	$result = $stmt->get_result();
	
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		echo "
			<tr>
				<td><a href='session.php?session_id=" . $row["session_id"] . "'>" . $row["session_date"] . "</a></td>
			</tr>
		";
	}
	
	echo "
		</table>
		<br>
	";
	
	echo "
		<table class='collection'>
			<tr>
				<th colspan='3'>Students</th>
			</tr>
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
	
	$result = $stmt->get_result();
	
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		echo "
			<tr>
				<td><a href='student.php?student_id=" . $row["student_id"] . "'>" . $row["school_id"] . "</a></td>
				<td><a href='student.php?student_id=" . $row["student_id"] . "'>" . $row["iclicker_id"] . "</a></td>
				<td><a href='student.php?student_id=" . $row["student_id"] . "'>" . $row["last_name"] . ", " . $row["first_name"] . "</a></td>
			</tr>
		";
	}
	
	echo "</table>";
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
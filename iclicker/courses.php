<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}

	$query = "
		SELECT user_id 
		FROM users 
		WHERE 1
		AND username = ? 
		AND	password = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'user_id' query. " . $conn->error);
	$stmt->bind_param("ss", $_COOKIE["Username"], $_COOKIE["Password"]);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($user_id);
	$stmt->fetch();
	$stmt->close();

	createHeader("Courses");
	
?>
<body>
	<div>
		<h1>Courses</h1>
		<table class='collection'>
			<tr>
				<th>Name</th>
				<th>Number</th>
			</tr>
<?php

	$query = "
		SELECT courses.course_id, course_name, course_number 
		FROM courses, sections, adminregistrations
		WHERE 1
		AND sections.course_id = courses.course_id
		AND sections.section_id = adminregistrations.section_id
		AND user_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'user_id' query. " . $conn->error);
	$stmt->bind_param("i", $user_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($course_id, $course_name, $course_number);
	
	while ($stmt->fetch()) {
	
		echo "
			<tr>
				<td><a href=course.php?course_id=$course_id</a>$course_name</td>
				<td><a href=course.php?course_id=$course_id</a>$course_number</td>
			</tr>
			
		";
	}
	$stmt->close();

?>
		</table>
		<a href='createnewcourse.php?'>Create New Course</a>
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>
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
		<h1>Courses</h1>
		<table class='collection'>
			<tr>
				<th>Name</th>
				<th>Number</th>
			</tr>
<?php

	$query = "
		SELECT courses.course_id, course_name, course_number 
		FROM courses, adminregistrations
		WHERE 1
		AND courses.course_id = adminregistrations.course_id
		AND user_id = ?
		ORDER BY course_number
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'user_id' query. " . $conn->error);
	$stmt->bind_param("i", $user_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($course_id, $course_name, $course_number);
	
	$curr = -1;
	while ($stmt->fetch()) {	
		
		if ($curr == $course_number) {
			//If administrator teaches the same course with multiple sections, this will skip the the duplicate course
		} 
		else {
			echo "
				<tr>
					<td><a href=course.php?course_id=$course_id>$course_name</a></td>
					<td><a href=course.php?course_id=$course_id>$course_number</a></td>
				</tr>
				
			";
		}
		$curr = $course_number;
	}
	$stmt->close();

?>
		</table>
		<a href='createnewcourse.php?'>Create New Course</a>
<?php
	$conn->close();
	createFooter();
?>
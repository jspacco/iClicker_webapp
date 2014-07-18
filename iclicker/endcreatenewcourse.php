<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
		
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}

	$course_name = $_POST["course_name"];
	$course_number = $_POST["course_number"];
	$section_number = $_POST["section_number"];
	$year_offered = $_POST["year_offered"];

	$query = "
		INSERT INTO courses (course_name, course_number)
		VALUES (?, ?)
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare 'insert courses' statement" . $conn->error);
	$stmt->bind_param('si', $course_name, $course_number);
	$stmt->execute() or die("Couldn't execute 'insert courses' statement" . $conn->error);
	
	//Gets last auto incremented id
	$query = "
		SELECT LAST_INSERT_ID()
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare 'last insert id' statement" . $conn->error);
	$stmt->execute() or die("Couldn't execute 'last insert id' statement" . $conn->error);
	$stmt->bind_result($last_insert_id);
	$stmt->fetch();
	$stmt->close();
	
	$query = "
		INSERT INTO sections (section_number, year_offered, course_id)
		VALUES (?, ?, ?)
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare 'insert sections' statement" . $conn->error);
	$stmt->bind_param('iii',$section_number, $year_offered, $last_insert_id);
	$stmt->execute() or die("Couldn't execute 'insert sections' statement" . $conn->error);
	$stmt->close();
		
	$query = "
		SELECT section_id
		FROM sections
		WHERE 1
		AND section_number = ?
		AND course_id = ?
		AND year_offered = ?
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare 'section_id' statement" . $conn->error);
	$stmt->bind_param('iii', $section_number, $last_insert_id, $year_offered);
	$stmt->execute() or die("Couldn't execute 'section_id' statement" . $conn->error);
	$stmt->bind_result($section_id);
	$stmt->fetch();
	$stmt->close();
	
	$user_id = getUserIdFromCookie($conn);
	
	$query = "
		INSERT INTO adminregistrations (user_id, section_id, course_id)
		VALUES (?, ?, ?)
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare 'adminregistrations' statement" . $conn->error);
	$stmt->bind_param('iii', $user_id, $section_number, $last_insert_id);
	$stmt->execute() or die("Couldn't execute 'adminregistrations' statement" . $conn->error);
	
	header("Location: courses.php?&message=Successfully created new course!");

	$conn->close();
?>
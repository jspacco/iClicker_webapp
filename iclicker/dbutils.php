<?php

function closeConn($conn) {
}

	function connect() {
		$dbhost = 'localhost';
		$dbuser = 'root';
		$dbpass = 'root';
		$dbname = 'iclicker';
		
		$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname) or die ('Error connecting to mysql ' . mysqli_connect_error());
		register_shutdown_function('closeConn', $conn);
		return $conn;
	}

function getSectionForCourseId($conn, $course_id) {
	// Returns: $section_id
	$query = "
		SELECT section_id
		FROM sections WHERE
		course_id = ?;
	";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $course_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($section_id);
	if (!$stmt->fetch()) {
		// raise an error
		//exit("No section exists for course_id $course_id");
	}
	$stmt->close();
	return $section_id;
}

function countSectionsByCourseId($conn, $course_id) {
	// Returns: $count
	$query = "
		SELECT count(*) 
		FROM sections WHERE
		course_id = ?;
	";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $course_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($count);
	$stmt->fetch();
	return $count;
}

function lookupSessionBySessionId($conn, $session_id) {
	$query = "
		SELECT  session_id, section_id, session_date, session_tag, post_processed FROM sessions WHERE session_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'section' query. " . $conn->error);
	$stmt->bind_param("i", $session_id);
	$stmt->execute() or die("Couldn't execute 'section' query. " . $conn->error);
	
	$stmt->bind_result($session_id, $section_id, $session_date, $session_tag, $post_processed);
	$stmt->fetch();
	$stmt->close();
	return array($session_id, $section_id, $session_date, $session_tag, $post_processed);
}

function lookupCourseBySectionId($conn, $section_id) {
// Returns: list($course_id, $course_name, $course_number)

	$query = "
		SELECT courses.course_id, course_name, course_number
		FROM courses, sections
		WHERE courses.course_id = sections.course_id
		AND section_id = ?;
	";
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($course_id, $course_name, $course_number);
	$stmt->fetch();
	return array($course_id, $course_name, $course_number);
}


?>
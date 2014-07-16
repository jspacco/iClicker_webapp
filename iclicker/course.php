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
	
	$course_id = $_GET["course_id"];

	// check if there is only one section for this course
	// if so redirect directly to the proper section
	$count = countSectionsByCourseId($conn, $course_id);
	//$userCount = getUserForCourseId($conn, $course_id)
	if ($count==1) {
		// look up the section_id then redirect
		$section_id=getSectionForCourseId($conn, $course_id);
		header("Location: section.php?section_id=$section_id");
	}
	
	createHeader("Course");
?>
		<h1>Sections</h1>
		<table class='collection'>
			<tr>
				<th>Section Number</th>
				<th>Year Offered</th>
			</tr>
<?php
	$course_id = $_GET["course_id"];
	
	$query = "
		SELECT sections.section_id, section_number, year_offered 
		FROM sections, courses, adminregistrations 
		WHERE 1
		AND courses.course_id = ?
		AND sections.course_id = courses.course_id
		AND sections.section_id = adminregistrations.section_id
		AND user_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("ii", $course_id, $user_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($section_id, $section_number, $year_offered);
	$count = 0;
	while ($stmt->fetch()) {
		echo "
			<tr>
				<td><a href=section.php?section_id=$section_id>$section_number</a></td>
				<td><a href=section.php?section_id=$section_id>$year_offered</a></td>
			</tr>		
		";
		$count++;
	}
	if($count==1) {
		header("Location: section.php?section_id=$section_id");
	}
	
?>
		</table>
<?php
	$conn->close();
	createFooter(true, "courses.php");
?>

<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	$user_id = getUserIdFromCookie($conn);	
	$course_id = $_GET["course_id"];

	//Check if there is only one section for this course, if so, redirect directly to the proper section
	$count = countSectionsByCourseId($conn, $course_id);
	if ($count==1) {
		$section_id = getSectionForCourseId($conn, $course_id);
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
		AND sections.course_id = courses.course_id
		AND sections.section_id = adminregistrations.section_id		
		AND courses.course_id = ?
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
	
	echo "</table>";
	
	$conn->close();
	createFooter(true, "courses.php");
?>

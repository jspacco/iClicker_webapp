<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	$course_id = $_GET["course_id"];

	// check if there is only one section for this course
	// if so redirect directly to the proper section
	$count = countSectionsByCourseId($conn, $course_id);
	if ($count==1) {
		// look up the section_id then redirect
		$section_id=getSectionForCourseId($conn, $course_id);
		header("Location: section.php?section_id=$section_id?");
	}

	
	createHeader("Course");
?>
<body>
	<div>
		<h2>Sections</h2>
		<table class='collection'>
			<tr>
				<th>Section Number</th>
				<th>Year Offered</th>
			</tr>
<?php
	$course_id = $_GET["course_id"];
	
	$query = "
		SELECT section_id, section_number, year_offered 
		FROM sections 
		WHERE course_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $course_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($section_id, $section_number, $year_offered);

	// $result = $stmt->get_result();
	
	while ($stmt->fetch()/*$row = $result->fetch_array(MYSQLI_ASSOC)*/) {
		echo "
			<tr>
				<td><a href=section.php?section_id=$section_id>$section_number</a></td>
				<td><a href=section.php?section_id=$section_id>$year_offered</a></td>
			</tr>
			
		";
	}
?>
		</table>
	</div>
</body>
<?php
	$conn->close();
	createFooter(true, "courses.php");
?>

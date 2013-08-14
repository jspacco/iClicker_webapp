<?php
require_once("pageutils.php");
require_once("dbutils.php");

$course_name=$_GET['course_name'];
$course_id=$_GET['course_id'];

if (!isset($course_name) and !isset($course_id)) {
	return;
}

$join='AND courses.course_name=?';
if (isset($course_id)) {
	$join='AND courses.course_id=?';
}

$conn = connect();

$query="
SELECT sessions.session_date
FROM sessions, sections, courses
WHERE sessions.section_id=sections.section_id
AND sections.course_id=courses.course_id
$join
";

$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
if (isset($course_id)) {
	$stmt->bind_param("s", $course_id);
} else {
	$stmt->bind_param("s", $course_name);
}
$stmt->execute() or die("Couldn't execute query. " . $conn->error);

$stmt->bind_result($session_date);

while ($stmt->fetch()) {
	echo "$session_date\n";
}

?>
<?php
require_once("pageutils.php");
require_once("dbutils.php");

$conn = connect();

$query="
SELECT course_name, section_id
FROM courses, sections
WHERE courses.course_id=sections.course_id
";

$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
$stmt->execute() or die("Couldn't execute query. " . $conn->error);

$stmt->bind_result($course_name, $course_id);

echo "course_name\tsection_id\n";
while ($stmt->fetch()) {
	echo "\n$course_name\t$course_id\n";
}

?>
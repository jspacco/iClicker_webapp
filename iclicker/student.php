<?php
require_once("pageutils.php");
require_once("dbutils.php");
require_once("loginutils.php");
$conn = connect();
	
$student_id=$_GET['student_id'];
$section_id=$_GET['section_id'];

list($student_id, $iclicker_id, $school_id, $firstname, $lastname, $email, $username) = getStudent($conn, $student_id);

if (!isCookieValidLoginWithType($conn, "admin")) {
	header("Location: home.php");
}

createHead("Student", true, "<a href='section.php?section_id=$section_id'> Back to Sessions and Assignments </a>");

echo "<div>\n";

echo "<h2>username: $username <br> clicker: $iclicker_id</h2>";

// create a temporary table of counts
printClickerParticipation($conn, $student_id, $section_id);

$conn->close();
createFooter();

?>
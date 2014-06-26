<?php
require_once("pageutils.php");
require_once("dbutils.php");
require_once("loginutils.php");
$conn = connect();
	
createHeader("Student Info");	

//$student_id = 187;
$section_id = 4;

//if (isset($section_id)) {
$student_id=$_GET['student_id'];
//$section_id=$_GET['section_id'];
//}

list($student_id, $iclicker_id, $school_id, $firstname, $lastname, $email, $username) = getStudent($conn, $student_id);

if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHead("Student", false, false, "<a href='section.php?section_id=$section_id'> Back to Assignments </a>");

echo "<h2>username: $username <br> clicker: $iclicker_id</h2>";

printClickerParticipation($conn, $student_id, $section_id);

$conn->close();
createFooter();

?>
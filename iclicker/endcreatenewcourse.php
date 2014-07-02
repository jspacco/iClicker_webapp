<?php
require_once("pageutils.php");
require_once("dbutils.php");
require_once("loginutils.php");
$conn = connect();
	
if (!isCookieValidLoginWithType($conn, "admin")) {
	header("Location: home.php");
}

$c_name = $_POST["course_name"];
$c_number = $_POST["course_number"];
$s_number = $_POST["section_number"];
$y_offered = $_POST["year_offered"];

	$query = "
		INSERT INTO courses (course_name, course_number)
		VALUES (?, ?);
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare 'insert courses' statement" . $conn->error);
	$stmt->bind_param('si', $c_name, $c_number);
	$stmt->execute() or die("Couldn't execute 'insert courses' statement" . $conn->error);
	
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
	$stmt->bind_param('iii',$s_number, $y_offered, $last_insert_id);
	$stmt->execute() or die("Couldn't execute 'insert sections' statement" . $conn->error);
	
header("Location: courses.php?&message=Successfully created new course!");

$conn->close();
?>
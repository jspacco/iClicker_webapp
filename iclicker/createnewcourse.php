<?php
require_once("pageutils.php");
require_once("dbutils.php");
require_once("loginutils.php");

$conn = connect();
	
if (!isCookieValidLoginWithType($conn, "admin")) {
	header("Location: home.php");
}

createHeader("Add New Course");

?>
	<table>
	<h2>Create New Course</h2>
	<form action='endcreatenewcourse.php' method='post'>
		<tr>
			<th>Course Name</th>
			<td><input type='text' name='course_name'></td>
		</tr>
		<tr>
			<th>Course Number</th>
			<td><input type='text' name='course_number'></td>
		</tr>
		<tr>
			<th>Section Number</th>
			<td><input type='text' name='section_number'></td>
		</tr>
		<tr>
			<th>Year Offered</th>
			<td><input type='text' name='year_offered'></td>
		</tr>
		<tr>
			<td></td>
			<td><input type='submit' value='Create This Course'></td>
		</tr>
	</table>

<?php
	$conn->close();
	createFooter();
?>
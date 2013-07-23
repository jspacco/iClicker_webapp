<?php
	require_once("dbutils.php");
	$conn = connect();

	echo "
		<html>
		<head>
			<link rel='stylesheet' type='text/css' href='stylesheet.css'>	
		</head>
		<body>
			<div>
				<table class='collection'>
					<tr>
						<th colspan='2'>Courses</th>
					</tr>
					<tr>
						<th>Name</th>
						<th>Number</th>
					</tr>
	";
	
	$query = "
		SELECT course_id, course_name, course_number FROM courses;
	";
	
	$result = $conn->query($query) or die("Couldn't execute query. " . $conn->error);
	
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		echo "
			<tr>
				<td><a href='course.php?course_id=" . $row["course_id"] . "'>" . $row["course_name"] . "</a></td>
				<td><a href='course.php?course_id=" . $row["course_id"] . "'>" . $row["course_number"] . "</a></td>
			</tr>
		";
	}
	
	echo "
				</table>
				<br>
				<a href='upload.html'>Upload session data</a>
			</div>
		</body>
		</html>
	";
	
	$conn->close();
?>
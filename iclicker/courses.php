<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Courses");
?>
<body>
	<div>
		<h2>Courses</h2>
		<table class='collection'>
			<tr>
				<th>Name</th>
				<th>Number</th>
			</tr>
<?php
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
?>
		</table>
	</div>
</body>
<?php
	$conn->close();
	createFooter();
?>
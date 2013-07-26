<?php
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
?>
<html>
<head>
	<link rel='stylesheet' type='text/css' href='stylesheet.css'>	
</head>
<header>
	<a href="logout.php">Logout</a>
</header>
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
		<br>
		<a href='upload.php'>Upload session data</a>
	</div>
</body>
<?php
	$conn->close();
?>
<footer>
	<a href='home.php'>Back to Home</a>
</footer>
</html>
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
				<th colspan='2'>Sections</th>
			</tr>
			<tr>
				<th>Section Number</th>
				<th>Year Offered</th>
			</tr>
<?php
	$course_id = $_GET["course_id"];
	
	$query = "
		SELECT section_id, section_number, year_offered FROM sections WHERE
		course_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $course_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$result = $stmt->get_result();
	
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		echo "
			<tr>
				<td><a href='section.php?section_id=" . $row["section_id"] . "'>" . $row["section_number"] . "</a></td>
				<td><a href='section.php?section_id=" . $row["section_id"] . "'>" . $row["year_offered"] . "</a></td>
			</tr>
		";
	}
?>
		</table>
	</div>
</body>
<?php
	$conn->close();
?>
<footer>
	<a href='home.php'>Back to Home</a>
</footer>
</html>

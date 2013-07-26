<?php
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
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
<?php
	$question_id = (int) $_GET["question_id"];
	$answer = $_POST["answer"];
	$user = $_COOKIE["Username"];
	$pass = $_COOKIE["Password"];
	
	$query = "
	SELECT student_id FROM students WHERE
	username = ? AND
	password = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'select' query. " . $conn->error);
	$stmt->bind_param("ss", $user, $pass);
	$stmt->execute() or die("Couldn't execute 'select' query. " . $conn->error);
	
	$result = $stmt->get_result();
	
	$row = $result->fetch_array(MYSQLI_ASSOC);
	
	$student_id = $row["student_id"];
	
	$query = "
	DELETE FROM onlineresponses WHERE
	question_id = ? AND
	student_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'delete' query. " . $conn->error);
	$stmt->bind_param("ii", $question_id, $student_id);
	$stmt->execute() or die("Couldn't execute 'delete' query. " . $conn->error);
	
	$query = "
	INSERT INTO onlineresponses (question_id, student_id, response) VALUES (?, ?, ?);
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'insert' query. " . $conn->error);
	$stmt->bind_param("iis", $question_id, $student_id, $answer);
	$stmt->execute() or die("Couldn't execute 'insert' query. " . $conn->error);
	
	echo "
	Answer submitted successfully!<br>
	<a href='reanswerquestion.php?question_id=" . $question_id . "'>Go back</a>
	";
?>
	</div>
</body>
<?php
	$conn->close();
?>
<footer>
	<a href='home.php'>Back to Home</a>
</footer>
</html>
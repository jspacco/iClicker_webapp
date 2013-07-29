<?php
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: login.php");
	}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' type='text/css' href='stylesheet.css'>
</head>
<body>
	<div>
<?php
	$question_id = $_GET["question_id"];

	$query = "
		SELECT question_name, screen_picture FROM questions WHERE
		question_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'select' query. " . $conn->error);
	$stmt->bind_param("i", $question_id);
	$stmt->execute() or die("Couldn't execute 'select' query. " . $conn->error);
	
	$stmt->bind_result($question_name, $screen_picture);
	
	$stmt->fetch();
	
	// $result = $stmt->get_result();
	
	// $row = $result->fetch_array(MYSQLI_ASSOC);
	
	echo "
		<h1>" . $question_name . "</h1>
		<img src='pictures/" . $screen_picture . "' width='700px' height='500px'>
		<form action='submitreanswer.php?question_id=" . $question_id . "' method='post'>
			<fieldset>
				<legend>Answer</legend>
				<table>
					<tr>
						<td><input type='radio' name='answer' value='A'>A</td>
						<td></td>
					</tr>
					<tr>
						<td><input type='radio' name='answer' value='B'>B</td>
						<td></td>
					</tr>
					<tr>
						<td><input type='radio' name='answer' value='C'>C</td>
						<td></td>
					</tr>
					<tr>
						<td><input type='radio' name='answer' value='D'>D</td>
						<td></td>
					</tr>
					<tr>
						<td><input type='radio' name='answer' value='E'>E</td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td><input type='submit' value='Submit'></td>
					</tr>
				</table>
			</fieldset>
		</form>
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
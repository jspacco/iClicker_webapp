<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' type='text/css' href='stylesheet.css'>
	<script type='text/javascript' src='jquery-1.10.2.min.js'></script>
	<script type='text/javascript' src='jquery.tablesorter.min.js'></script>
</head>
<body>
	<script type='text/javascript'>
		$(document).ready(function() {
				$('#responsestable').tablesorter();
			}
		);
	</script>
	<div>
<?php
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	$login = isValidLogin($conn, "", "");

	$question_id = $_GET["question_id"];

	$query = "
		SELECT question_number, question_name, screen_picture, chart_picture, correct_answer, start_time, stop_time FROM questions WHERE
		question_id = ?;
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare questions query. " . $conn->error);
	$stmt->bind_param("i", $question_id);
	$stmt->execute() or die("Couldn't execute questions query. " . $conn->error);

	$result = $stmt->get_result();

	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		echo "
			<h1>" . $row["question_name"] . "</h1>
			<table>
				<tr>
					<th>Number</th>
					<th>Correct Answer</th>
					<th>Start Time</th>
					<th>Stop Time</th>
				</tr>
				<tr>
					<td>" . $row["question_number"] . "</td>
					<td>" . $row["correct_answer"] . "</td>
					<td>" . $row["start_time"] . "</td>
					<td>" . $row["stop_time"] . "</td>
				</tr>
			</table>
			<br>
			<img src='pictures/" . $row["chart_picture"] . "' alt='Chart of responses' width='350' height='200'>
			<img src='pictures/" . $row["screen_picture"] . "' alt='Picture of screen' width='350' height='200'>
		";
	}
?>
		<table id='responsestable' class='tablesorter' cellspacing='1'>
		<thead>
			<tr>
				<th colspan='3'>Student</th>
				<th colspan='5'>Response</th>
			</tr>
			<tr>
				<th>School ID</th>
				<th>iClicker ID</th>
				<th>Name</th>
				<th>Number of Attempts</th>
				<th>First Response</th>
				<th>Time</th>
				<th>Response</th>
				<th>Final Answer Time</th>
			</tr>
		</thead>
		<tbody>
<?php
	$query = "
		SELECT school_id, iclicker_id, last_name, first_name, number_of_attempts, first_response, time, response, final_answer_time FROM students, responses WHERE
		students.student_id = responses.student_id AND
		responses.question_id = ?;
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare responses query. " . $conn->error);
	$stmt->bind_param("i", $question_id);
	$stmt->execute() or die("Couldn't execute responses query. " . $conn->error);

	$result = $stmt->get_result();

	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		echo "
			<tr>
				<td>" . $row["school_id"] . "</td>
				<td>" . $row["iclicker_id"] . "</td>
				<td>" . $row["last_name"] . ", " . $row["first_name"] . "</td>
				<td>" . $row["number_of_attempts"] . "</td>
				<td>" . $row["first_response"] . "</td>
				<td>" . $row["time"] . "</td>
				<td>" . $row["response"] . "</td>
				<td>" . $row["final_answer_time"] . "</td>
			<tr>
		";
	}
?>
		</table>
	</div>
</body>
<footer>
	<a href='home.php'>Back to Home</a>
</footer>
</html>
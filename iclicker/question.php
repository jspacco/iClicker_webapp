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
	<script type='text/javascript' src='jquery-1.10.2.min.js'></script>
	<script type='text/javascript' src='jquery.tablesorter.min.js'></script>	
</head>
<header>
	<a href="logout.php">Logout</a>
</header>
<body>
	<script type='text/javascript'>
		$(document).ready(function() {
			$('#responsestable').tablesorter();
		});
	</script>
	<div>
<?php
	$question_id = $_GET["question_id"];
	
	$query = "
		SELECT question_number, question_name, screen_picture, chart_picture, correct_answer, start_time, stop_time FROM questions WHERE
		question_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare questions query. " . $conn->error);
	$stmt->bind_param("i", $question_id);
	$stmt->execute() or die("Couldn't execute questions query. " . $conn->error);
	
	$stmt->bind_result($question_number, $question_name, $screen_picture, $chart_picture, $correct_answer, $start_time, $stop_time);
	
	// $result = $stmt->get_result();
	
	while ($stmt->fetch()/*$row = $result->fetch_array(MYSQLI_ASSOC)*/) {
		echo "
			<h1>" . $question_name . "</h1>
			<table>
				<tr>
					<th>Number</th>
					<th>Correct Answer</th>
					<th>Start Time</th>
					<th>Stop Time</th>
				</tr>
				<tr>
					<td>" . $question_number . "</td>
					<td>" . $correct_answer . "</td>
					<td>" . $start_time . "</td>
					<td>" . $stop_time . "</td>
				</tr>
			</table>
			<br>
			<img src='pictures/" . $chart_picture . "' alt='Chart of responses' width='350' height='200'>
			<img src='pictures/" . $screen_picture . "' alt='Picture of screen' width='350' height='200'>
		";
	}
?>
<table id='responsestable' class='tablesorter' cellspacing='1'>
	<thead>
		<tr class="">
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
	
	$stmt->bind_result($school_id, $iclicker_id, $last_name, $first_name, $number_of_attempts, $first_response, $time, $response, $final_answer_time);
	
	// $result = $stmt->get_result();
	
	while ($stmt->fetch()/*$row = $result->fetch_array(MYSQLI_ASSOC)*/) {
		echo "
			<tr>
				<td>" . $school_id . "</td>
				<td>" . $iclicker_id . "</td>
				<td>" . $last_name . ", " . $first_name . "</td>
				<td>" . $number_of_attempts . "</td>
				<td>" . $first_response . "</td>
				<td>" . $time . "</td>
				<td>" . $response . "</td>
				<td>" . $final_answer_time . "</td>
			<tr>
		";
	}
?>
		</tbody>
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
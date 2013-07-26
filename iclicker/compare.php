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
		<h1>Comparison</h1>
		<table>
			<tr>
				<th>Vote Type</th>
				<th>Screen Picture</th>
				<th>Chart Picture</th>
				<th>Student Scores</th>
			</tr>
<?php
	$iv_id = $_GET["iv"];
	$gv_id = $_GET["gv"];
	
	$iv_votes = array();
	$gv_votes = array();

	$query = "
		SELECT screen_picture, chart_picture, correct_answer, response, student_id FROM questions, responses WHERE
		responses.question_id = questions.question_id AND
		questions.question_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare IV query. " . $conn->error);
	$stmt->bind_param("i", $iv_id);
	$stmt->execute() or die("Couldn't execute IV query. " . $conn->error);
	
	$iv_result = $stmt->get_result();
	$iv_row = $iv_result->fetch_array(MYSQLI_ASSOC);
	$iv_votes[$iv_row["student_id"]] = $iv_row["response"];

	while ($row = $iv_result->fetch_array(MYSQLI_ASSOC)) {
		$iv_votes[$row["student_id"]] = $row["response"];
	}
	
	$query = "
		SELECT screen_picture, chart_picture, correct_answer, response, student_id FROM questions, responses WHERE
		responses.question_id = questions.question_id AND
		questions.question_id = ?;
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare GV query. " . $conn->error);
	$stmt->bind_param("i", $gv_id);
	$stmt->execute() or die("Couldn't execute GV query. " . $conn->error);

	$gv_result = $stmt->get_result();
	$gv_row = $gv_result->fetch_array(MYSQLI_ASSOC);
	$gv_votes[$gv_row["student_id"]] = $gv_row["response"];

	while($row = $gv_result->fetch_array(MYSQLI_ASSOC)) {
		$gv_votes[$row["student_id"]] = $row["response"];
	}
	
	$iv_correct = 0;
	$gv_correct = 0;
	
	foreach ($iv_votes as $key => $value) {
		if (trim($value) == trim($iv_row["correct_answer"])) {
			$iv_correct++;
		}
	}
	foreach ($gv_votes as $key => $value) {
		if (trim($value) == trim($gv_row["correct_answer"])) {
			$gv_correct++;
		}
	}
	
	echo "
			<tr>
				<td>Individual Vote</td>
				<td><img src='pictures/" . $iv_row["screen_picture"] . "' alt='Picture of screen' width='175' height='100'></td>
				<td><img src='pictures/" . $iv_row["chart_picture"] . "' alt='Chart of responses' width='175' height='100'></td>
				<td>" . $iv_correct . "/" . count($iv_votes) . "</td>
			</tr>
			<tr>
				<td>Group Vote</td>
				<td><img src='pictures/" . $gv_row["screen_picture"] . "' alt='Picture of screen' width='175' height='100'></td>
				<td><img src='pictures/" . $gv_row["chart_picture"] . "' alt='Chart of responses' width='175' height='100'></td>
				<td>" . $gv_correct . "/" . count($gv_votes) . "</td>
			</tr>
		</table>
	";
?>
		<h2>Vote Changes</h2>
		<table class='votechanges'>
			<tr>
				<th rowspan='7'>From</th>
				<th colspan='7'>To</th>
			</tr>
			<tr>
				<th></th>	
				<th>A</th>
				<th>B</th>
				<th>C</th>
				<th>D</th>
				<th>E</th>
			</tr>
<?php	
	echo "
			<tr>
				<th>A</th>
				<td>" . countFromTo("A", "A", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("A", "B", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("A", "C", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("A", "D", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("A", "E", $iv_votes, $gv_votes) . "</td>
			</tr>
			<tr>
				<th>B</th>
				<td>" . countFromTo("B", "A", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("B", "B", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("B", "C", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("B", "D", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("B", "E", $iv_votes, $gv_votes) . "</td>
			</tr>
			<tr>
				<th>C</th>
				<td>" . countFromTo("C", "A", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("C", "B", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("C", "C", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("C", "D", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("C", "E", $iv_votes, $gv_votes) . "</td>
			</tr>
			<tr>
				<th>D</th>
				<td>" . countFromTo("D", "A", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("D", "B", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("D", "C", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("D", "D", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("D", "E", $iv_votes, $gv_votes) . "</td>
			</tr>
			<tr>
				<th>E</th>
				<td>" . countFromTo("E", "A", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("E", "B", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("E", "C", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("E", "D", $iv_votes, $gv_votes) . "</td>
				<td>" . countFromTo("E", "E", $iv_votes, $gv_votes) . "</td>
			</tr>
		</table>
	";
	
	function countFromTo($from, $to, $iv, $gv) {
		$num = 0;
		
		foreach ($iv as $key => $value) {
			if (trim($value) == $from) {
				if (trim($gv[$key]) == $to) {
					$num++;
				}
			}
		}
		
		if ($num == 0)
			return "-";
		else
			return $num;
	}
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
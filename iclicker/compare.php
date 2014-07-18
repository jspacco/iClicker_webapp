<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();

	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Compare");
?>
<h1>Comparison</h1>
<table>
	<tr>
		<th>Vote Type</th>
		<th>Screen Picture</th>
		<th>Chart Picture</th>
		<th>Answers</th>
		<th>In-Class Scores</th>
		<th>Online Scores</th>
	</tr>
<?php
	$iv_id = $_GET["iv"];
	$gv_id = $_GET["gv"];
	$section_id = $_GET["section_id"];
	
	$iv_votes = array();
	$gv_votes = array();

	$query = "
		SELECT screen_picture, chart_picture, correct_answer, response, student_id 
		FROM questions, responses 
		WHERE 1
		AND responses.question_id = questions.question_id 
		AND	questions.question_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare IV query. " . $conn->error);
	$stmt->bind_param("i", $iv_id);
	$stmt->execute() or die("Couldn't execute IV query. " . $conn->error);
	$stmt->bind_result($iv_screen_picture, $iv_chart_picture, $iv_correct_answer, $iv_response, $iv_student_id);
	
	$iv_votes[$iv_student_id] = $iv_response;
	while ($stmt->fetch()) {
		$iv_votes[$iv_student_id] = $iv_response;
	}
	
	$stmt->close();
	
	$query = "
		SELECT screen_picture, chart_picture, correct_answer, response, student_id 
		FROM questions, responses 
		WHERE 1
		AND responses.question_id = questions.question_id 
		AND	questions.question_id = ?
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare GV query. " . $conn->error);
	$stmt->bind_param("i", $gv_id);
	$stmt->execute() or die("Couldn't execute GV query. " . $conn->error);
	$stmt->bind_result($gv_screen_picture, $gv_chart_picture, $gv_correct_answer, $gv_response, $gv_student_id);
	
	$gv_votes[$gv_student_id] = $gv_response;
	while($stmt->fetch()) {
		$gv_votes[$gv_student_id] = $gv_response;
	}
	
	$stmt->close();
	
	$iv_correct = 0;
	$gv_correct = 0;
	
	foreach ($iv_votes as $key => $value) {
		foreach (explode(",", $iv_correct_answer) as $answer) {
			if (trim($value) == trim($answer)) {
				$iv_correct++;
				break;
			}
		}
	}
	foreach ($gv_votes as $key => $value) {
		foreach (explode(",", $gv_correct_answer) as $answer) {
			if (trim($value) == trim($answer)) {
				$gv_correct++;
				break;
			}
		}
	}
	
	$iv_online = array();
	$gv_online = array();
	
	$query = "
		SELECT student_id, response 
		FROM onlineresponses 
		WHERE question_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'online responses' query. " . $conn->error);
	$stmt->bind_param("i", $iv_id);
	$stmt->execute() or die("Couldn't execute 'online responses' query. " . $conn->error);
	$stmt->bind_result($online_id, $onlineresponse);
	
	while ($stmt->fetch()) {
		$iv_online[$online_id] = $onlineresponse;
	}
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'online responses' query. " . $conn->error);
	$stmt->bind_param("i", $gv_id);
	$stmt->execute() or die("Couldn't execute 'online responses' query. " . $conn->error);
	
	$stmt->bind_result($online_id, $onlineresponse);
	
	while ($stmt->fetch()) {
		$gv_online[$online_id] = $onlineresponse;
	}
	
	$stmt->close();
	
	$iv_online_correct = 0;
	$gv_online_correct = 0;
	
	foreach ($iv_online as $key => $value) {
		$exit = false;
		foreach (explode(",", $value) as $response) {
			if ($exit == true)
				break;
			foreach (explode(",", $iv_correct_answer) as $answer) {
				if (trim($response) == trim($answer)) {
					$iv_online_correct++;
					$exit = true;
					break;
				}
			}
		}
	}
	foreach ($gv_online as $key => $value) {
		$exit = false;
		foreach (explode(",", $value) as $response) {
			if ($exit == true)
			break;
			foreach (explode(",", $gv_correct_answer) as $answer) {
				if (trim($response) == trim($answer)) {
					$gv_online_correct++;
					$exit = true;
					break;
				}
			}
		}
	}
	
	echo "
			<tr>
				<td>Individual Vote</td>
				<td><a href='pictures/$section_id/" . $iv_screen_picture . "' title='Picture of screen' data-lightbox='" . $iv_id . "'><img src='pictures/$section_id/" . $iv_screen_picture . "' alt='Picture of screen' width='175' height='100'></td>
				<td><a href='pictures/$section_id/" . $iv_chart_picture . "' title='Chart of responses' data-lightbox='" . $iv_id . "'><img src='pictures/$section_id/" . $iv_chart_picture . "' alt='Chart of responses' width='175' height='100'></td>
				<td>" . $iv_correct_answer . "</td>
				<td>" . $iv_correct . "/" . count($iv_votes) . "</td>
				<td>" . $iv_online_correct . "/" . count($iv_online) . "</td>
			</tr>
			<tr>
				<td>Group Vote</td>
				<td><a href='pictures/$section_id/" . $gv_screen_picture . "' title='Picture of screen' data-lightbox='" . $gv_id . "'><img src='pictures/$section_id/" . $gv_screen_picture . "' alt='Picture of screen' width='175' height='100'></td>
				<td><a href='pictures/$section_id/" . $gv_chart_picture . "' title='Chart of responses' data-lightbox='" . $gv_id . "'><img src='pictures/$section_id/" . $gv_chart_picture . "' alt='Chart of responses' width='175' height='100'></td>
				<td>" . $gv_correct_answer . "</td>
				<td>" . $gv_correct . "/" . count($gv_votes) . "</td>
				<td>" . $gv_online_correct . "/" . count($gv_online) . "</td>
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
				" . countRow('A', $gv_correct_answer, $iv_votes, $gv_votes) . "
			</tr>
			<tr>
				" . countRow('B', $gv_correct_answer, $iv_votes, $gv_votes) . "
			</tr>
			<tr>
				" . countRow('C', $gv_correct_answer, $iv_votes, $gv_votes) . "
			</tr>
			<tr>
				" . countRow('D', $gv_correct_answer, $iv_votes, $gv_votes) . "
			</tr>
			<tr>
				" . countRow('E', $gv_correct_answer, $iv_votes, $gv_votes) . "
			</tr>
		</table>
	";
	
	function countRow($from, $answer, $iv, $gv) {
		
		$s = "
			<th>" . $from . "</th>" .
			countFromTo($from, "A", $answer, $iv, $gv) . 
			countFromTo($from, "B", $answer, $iv, $gv) . 
			countFromTo($from, "C", $answer, $iv, $gv) . 
			countFromTo($from, "D", $answer, $iv, $gv) . 
			countFromTo($from, "E", $answer, $iv, $gv);
		
		return $s;
	}
	
	function countFromTo($from, $to, $answer, $iv, $gv) {
		$num = 0;
		
		foreach ($iv as $key => $value) {
			if (trim($value) == $from) {
				if (trim($gv[$key]) == $to) {
					$num++;
				}
			}
		}
		
		if ($num == 0)
			$num = "-";
		
		if (trim($to) == trim($answer)) {
			$s = "<td class='correct'>" . $num . "</td>";
		} else if (trim($from) == trim($answer)) {
			$s = "<td class='switched'>" . $num . "</td>";
		} else if (trim($from) == trim($to)) {
			$s = "<td class='stayed'>" . $num . "</td>";
		} else {
			$s = "<td>" . $num . "</td>";
		}
		
		return $s;
	}

	$conn->close();
	createFooter();
?>
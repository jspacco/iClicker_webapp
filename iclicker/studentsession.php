<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
		
	$session_id = $_GET["session_id"];
	list($session_id, $section_id, $session_date, $session_tag, $post_processed) = lookupSessionBySessionId($conn, $session_id);
	createHeader("Session", true, "<a href='section.php?section_id=$section_id'> Back to Sessions and Assignments </a>");
	$student_id = getStudentIdFromCookie($conn);

?>
<h1>Questions <?= $session_date ?></h1>
	<table border=1 align='center'>
	<input type="hidden" name="session_id" value="<?= $_GET["session_id"] ?>"/>
	<tr>
		<th>#</th>
		<th>Type</th>
		<th>Question Picture</th>
		<th>Chart Picture</th>
		<th>Correct Answer(s)</th>
		<th>My Answer(s)</th>	
	</tr>
	
<?php

	$query = "
		SELECT questions.question_id, question_number, screen_picture, chart_picture, correct_answer, ignore_question, single_question, response
		FROM questions, responses
		WHERE 1
		AND session_id = ?
		AND student_id = ?
		AND questions.question_id = responses.question_id
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("ii", $session_id, $student_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($question_id, $question_number, $screen_picture, $chart_picture, $correct_answer, $ignore_question, $single_question, $response);
	
	$q = 1;
	$num = 1;
	$iv_id;

	while ($stmt->fetch()) {
		$type = "IV";
		if ($q % 2 === 0) {
			$type = "GV";
		}	
		
		echo "<tr>";
		$ignore = "";
		$single = "";
		
		if ($ignore_question == 1) {
			$ignore = "checked";
		}
		if ($single_question == 1) {
			$single = "checked";
		}
		
		if ($ignore_question != 1) {
			echo "
				<td>Question $num</td>
			";		
		}
		
		if ($single_question == 1) {
			echo "
				<td>SV</td>
			";
		} else {
			if ($ignore_question != 1) {
				echo "
					<td>$type</td>
				";
			}
		}
		
		$a = "";
		$b = "";
		$c = "";
		$d = "";
		$e = "";
		
		if (strpos($correct_answer, 'A') !== false) {
			$a = "checked";
		}
		if (strpos($correct_answer, 'B') !== false) {
			$b = "checked";
		}
		if (strpos($correct_answer, 'C') !== false) {
			$c = "checked";
		}
		if (strpos($correct_answer, 'D') !== false) {
			$d = "checked";
		}
		if (strpos($correct_answer, 'E') !== false) {
			$e = "checked";
		}
		
		if ($ignore_question != 1) {
			echo "
				<td><a href='pictures/$section_id/$screen_picture' title='Picture of screen' data-lightbox='$question_id'><img src='pictures/$section_id/$screen_picture' alt='Picture of screen' width='175' height='100'></td>
				<td><a href='pictures/$section_id/$chart_picture' title='Chart of responses' data-lightbox='$question_id'><img src='pictures/$section_id/$chart_picture' rel='lightbox' title='Chart of responses' width='175' height='100'></td>
			";
		}
		
		if ($ignore_question != 1) {
			echo "<td>";
			if ($a == "checked") {
				echo "<name='A[]' value='$question_id' $a>A";
			}
			if ($b == "checked") {
				echo "<name='B[]' value='$question_id' $b>B";
			}
			if ($c == "checked") {
				echo "<name='C[]' value='$question_id' $c>C";
			}
			if ($d == "checked") {
				echo "<name='D[]' value='$question_id' $d>D";
			}
			if ($e == "checked") {
				echo "<name='E[]' value='$question_id' $e>E";
			}
		}
		
		echo "</td>";	
		
		if ($ignore_question != 1) {
			echo "<td>$response</td>";
		}
		
		echo "</tr>";
		
		if ($ignore_question != 1) {
			if ($single_question == 1) {
					$q++;
			}
			if ($q % 2 === 1) {
				$iv_id = $question_id;
			} else {
				$num++;
			}
			$q++;
		}
	}
	
	$stmt->close();
		
	echo "</table>";

	logs($conn, $student_id);
	$conn->close();
	if (isset($_GET['message'])) {
		echo "<h2> $_GET[message] </h2>";
	}
	createFooter(true, "section.php?section_id=$section_id");
?>
<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	require_once("gradingutils.php");
	$conn = connect();

	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Assignment Report");
	
	$assignment_id = $_GET["assignment_id"];
?>
<?php

	$section_id = getSectionIdByAssignmentId($conn, $assignment_id);
	
	$query = "
		SELECT question_id 
		FROM assignmentstoquestions 
		WHERE assignment_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'questions' query. " . $conn->error);
	$stmt->bind_param("i", $assignment_id);
	$stmt->execute() or die("Couldn't execute 'questions' query. " . $conn->error);
	
	$stmt->bind_result($question_id);
	
	$questions = array();
	
	while ($stmt->fetch()) {
		array_push($questions, $question_id);
	}
	$stmt->close();
	
	$num_questions = 0;
?>
<h1>Assignment Report</h1>
<h2>Questions</h2>
<table>
	<tr>
		<th>Question</th>
		<th>Name</th>
		<th>Answer</th>
		<th>Picture</th>
		<th>Partially Correct</th>
		<th>Correct</th>
	</tr>
<?php
	foreach ($questions as $question_id) {
		$num_questions++;
		
		$query = "
			SELECT question_number, question_name, screen_picture, correct_answer
			FROM questions 
			WHERE question_id = $question_id;
		";
		
		$result = $conn->query($query) or die("Couldn't execute 'correct answer' query. " . $conn->error);
		$arr = $result->fetch_assoc();
		
		echo "
			<tr>
				<td>Question $arr[question_number] </td>
				<td> $arr[question_name] </td>
				<td> $arr[correct_answer] </td>
				<td><a href='pictures/$section_id/$arr[screen_picture]' title='Picture of screen' data-lightbox='$question_id'><img src='pictures/$section_id/$arr[screen_picture]' alt='Picture of screen' width='175' height='100'></td>
		";
		
		$query = "
			SELECT DISTINCT response, question_id, student_id FROM onlineresponses 
			WHERE onlineresponses.question_id = $question_id 
			AND end_time < (SELECT due FROM assignments WHERE assignment_id = $assignment_id)
			GROUP BY student_id, question_id;
		";
		
		$result = $conn->query($query) or die("Couldn't execute 'responses' query. " . $conn->error);
		
		$num_partial = 0;
		$num_correct = 0;
		$answers = 0;
		while ($row = $result->fetch_assoc()) {
			$ret = isCorrect($row["response"], $arr["correct_answer"]);
			
			if ($ret >= 1) {
				$num_partial++;
			}
			if ($ret == 2) {
				$num_correct++;
			}
			$answers++;
		}
		
		echo "
				<td>$num_partial/$answers</td>
				<td>$num_correct/$answers</td>
			</tr>
		";
	}
?>
</table>
<h2>Students</h2>
<table>
	<tr>
		<th>iClicker ID</th>
		<th>Student ID</th>
		<th>Originally Correct</th>
		<th>Partially Correct</th>
		<th>Correct</th>
		<th>Unanswered Online</th>
	</tr>
<?php
$query = "
	SELECT DISTINCT students.student_id, students.school_id, students.iclicker_id FROM students, responses, assignmentstoquestions, assignments 
	WHERE students.student_id = responses.student_id 
	AND responses.question_id = assignmentstoquestions.question_id 
	AND assignmentstoquestions.assignment_id = assignments.assignment_id 
	AND assignments.assignment_id = $assignment_id;
	";
	
$stmt=$conn->prepare($query) or die("Couldn't prepare 'students' query. " . $conn->error);
$stmt->execute();
$stmt->bind_result($student_id, $school_id, $iclicker_id);

$students = array();
	
while ($stmt->fetch()) {
	array_push($students, array($student_id, $school_id, $iclicker_id));
}

	
$query = "
	SELECT DISTINCT questions.question_id, questions.correct_answer 
	FROM questions, assignmentstoquestions 
	WHERE questions.question_id = assignmentstoquestions.question_id 
	AND assignmentstoquestions.assignment_id = $assignment_id;
	";
	
	$result = $conn->query($query) or die("Couldn't execute 'questions' query. " . $conn->error);
	
	$questions = array();
	
	while ($row = $result->fetch_assoc()) {
		$questions[$row["question_id"]] = $row["correct_answer"];
	}
	
	foreach ($students as $list) {
		list($student_id, $school_id, $iclicker_id) = $list;
		$num_questions = 0;
		$num_original = 0;
		$num_partial = 0;
		$num_correct = 0;
		$num_unanswered = sizeof($questions);
		
		foreach ($questions as $question_id => $correct_answer) {
			$query = "
				SELECT DISTINCT response 
				FROM responses 
				WHERE student_id = $student_id 
				AND question_id = $question_id;
			";
			
			$result = $conn->query($query) or die("Couldn't execute 'original answer' query. " . $conn->error);
			
			$row = $result->fetch_assoc();
			if ($row["response"] != NULL) {
				$ret = isCorrect($row["response"], $correct_answer);
				
				if ($ret >= 1) {
					$num_original++;
				}
			}
			
			$query = "
				SELECT response 
				FROM onlineresponses 
				WHERE onlineresponses.student_id = $student_id 
				AND onlineresponses.question_id = $question_id 
				AND end_time < (SELECT due FROM assignments WHERE assignment_id = $assignment_id)
				ORDER BY end_time DESC LIMIT 1;
			";
			
			$result = $conn->query($query) or die("Couldn't execute 'answer' query. " . $conn->error);
			
			$row = $result->fetch_assoc();
			if ($row["response"] != NULL) {
				$ret = isCorrect($row["response"], $correct_answer);
				
				if ($ret >= 1) {
					$num_partial++;
				}
				if ($ret == 2) {
					$num_correct++;
				}
				$num_unanswered--;
			}
			
			$num_questions++;
		}
		
		echo "
			<tr>
				<td>$iclicker_id</td>
				<td>$school_id</td>
				<td>$num_original/$num_questions</td>
				<td>$num_partial/$num_questions</td>
				<td>$num_correct/$num_questions</td>
				<td>$num_unanswered</td>
			</tr>
		";
	}
?>
</table>
<?php echo "<a href='editassignment.php?assignment_id=$assignment_id'>Edit Assignment</a>"; ?>
<?php
	$conn->close();
	createFooter();
?>
<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHeader("Question Report");
	
	$assignment_id = $_GET["assignment_id"];
	$question_id = $_GET["question_id"];
	
	$student_id = getStudentIdFromCookie($conn);	
	$section_id = getSectionIdByStudentId($conn, $student_id);
?>
<h1>Question Report</h1>
<table>
	<tr>
		<th>Picture</th>
		<th>Correct Answer</th>
		<th>In-class Answer</th>
		<th>Online Answer Before Deadline</th>
		<th>Most Recent Online Answer</th>
	</tr>
<?php
	// we do this with multiple queries in case they didn't answer the question in class
	$query = "
		SELECT screen_picture, correct_answer 
		FROM questions 
		WHERE question_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'question' query. " . $conn->error);
	$stmt->bind_param("i", $question_id);
	$stmt->execute() or die("Couldn't execute 'question' query. " . $conn->error);
	
	$stmt->bind_result($screen_picture, $correct_answer);
	$stmt->fetch();
	$stmt->close();
	
	$query = "
		SELECT response 
		FROM responses 
		WHERE 1
		AND question_id = ? 
		AND	student_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'response' query. " . $conn->error);
	$stmt->bind_param("ii", $question_id, $student_id);
	$stmt->execute() or die("Couldn't execute 'response' query. " . $conn->error);
	
	$stmt->bind_result($response);
	$stmt->fetch();
	$stmt->close();
	
	$query = "
		SELECT response 
		FROM onlineresponses 
		WHERE 1
		AND question_id = ? 
		AND	student_id = ? 
		AND	end_time < (SELECT due FROM assignments WHERE assignment_id = ?)
		ORDER BY end_time
		DESC LIMIT 1
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'beforeonlineresponses' query. " . $conn->error);
	$stmt->bind_param("iii", $question_id, $student_id, $assignment_id);
	$stmt->execute() or die("Couldn't execute 'beforeonlineresponses' query. " . $conn->error);
	$stmt->bind_result($beforeonlineresponse);
	$stmt->fetch();
	$stmt->close();
	
	$query = "
		SELECT response 
		FROM onlineresponses 
		WHERE 1
		AND question_id = ? 
		AND	student_id = ?
		ORDER BY end_time 
		DESC LIMIT 1
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'afteronlineresponses' query. " . $conn->error);
	$stmt->bind_param("ii", $question_id, $student_id);
	$stmt->execute() or die("Couldn't execute 'afteronlineresponses' query. " . $conn->error);
	
	$stmt->bind_result($afteronlineresponse);
	$stmt->fetch();
	$stmt->close();
	
	echo "
		<tr>
			<td><a href='pictures/$section_id/" . $screen_picture . "' title='Picture of screen' data-lightbox='$question_id'><img src='pictures/$section_id/" . $screen_picture . "' alt='Picture of screen' width='175' height='100'></td>
			<td>$correct_answer</td>
			<td>$response</td>
			<td>$beforeonlineresponse</td>
			<td>$afteronlineresponse</td>
		</tr>
	"
?>
</table>
<br>
<?php
	$query = "
		SELECT assignment_id, question_id 
		FROM assignmentstoquestions 
		WHERE atq_id IN (SELECT next_question FROM assignmentstoquestions WHERE assignment_id = ? AND question_id = ?);
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'next_question' query. " . $conn->error);
	$stmt->bind_param("ii", $assignment_id, $question_id);
	$stmt->execute() or die("Couldn't execute 'next_question' query. " . $conn->error);
	$stmt->bind_result($next_assignment, $next_question);
	$stmt->fetch();
	$stmt->close();
	
	$next = "";
	if ($next_assignment != NULL && $next_question != NULL) {
		$next = "<td><a href='reanswerquestion.php?question_id=$next_question&assignment_id=$next_assignment'>Next Question</a></td>";
	}

	echo "
		<table>
			<tr>
				<td><a href='reanswerquestion.php?question_id=$question_id&assignment_id=$assignment_id'>Reanswer this Question</a></td>
				" . $next . "
			</tr>
		</table>
	";
?>
<?php
	logs($conn, $student_id);
	$conn->close();
	createFooter();
?>
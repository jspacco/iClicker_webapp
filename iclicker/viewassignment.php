<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHeader("Assignment Page");
	
	$assignment_id = $_GET["assignment_id"];
	
	$query = "
		SELECT section_id, due FROM assignments WHERE assignment_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'assignments' query. " . $conn->error);
	$stmt->bind_param("i", $assignment_id);
	$stmt->execute() or die("Couldn't execute 'assignments' query. " . $conn->error);
	
	$stmt->bind_result($section_id, $due);
	$stmt->fetch();
	$stmt->close();
?>
<table>
	<tr>
		<td>Due: </td>
		<td><?php echo DateFromUTC($due); ?></td>
	</tr>
</table>
<h2>Questions</h2>
<table class="collection">
	<tr>
		<th>Number</th>
		<th>Answered</th>
	</tr>
<?php
	$query = "
		SELECT student_id FROM students WHERE
		username = ? AND
		password = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'student_id' query. " . $conn->error);
	$stmt->bind_param("ss", $_COOKIE["Username"], $_COOKIE["Password"]);
	$stmt->execute() or die("Couldn't execute 'student_id' query. " . $conn->error);
	
	$stmt->bind_result($student_id);
	$stmt->fetch();
	$stmt->close();
	
	$query = "
		SELECT question_id FROM assignmentstoquestions WHERE
		assignmentstoquestions.assignment_id = ?;
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
	
	$i = 1;
	foreach ($questions as $question_id) {
		$query = "
			SELECT response FROM onlineresponses WHERE
			question_id = ?	AND
			student_id = ?;
		";
		
		$stmt = $conn->prepare($query) or die("Couldn't prepare 'responses' query. " . $conn->error);
		$stmt->bind_param("ii", $question_id, $student_id);
		$stmt->execute() or die("Couldn't execute 'responses' query. " . $conn->error);
		$stmt->store_result();
		$num = $stmt->num_rows;
		$stmt->close();
		
		$answered = "No";
		$link = "";
		if ($num > 0) {
			$answered = "Yes";
			$link = "
				<td>
					<form action='questionreport.php' method='get'>
						<input type='hidden' value='$assignment_id' name='assignment_id'>
						<input type='hidden' value='$question_id' name='question_id'>
						<input type='submit' value='View Report'>
					</form>
				</td>
			";
		}
		
		echo "
			<tr>
				<td><a href='reanswerquestion.php?question_id=$question_id&assignment_id=$assignment_id'>Question $i</a></td>
				<td><a href='reanswerquestion.php?question_id=$question_id&assignment_id=$assignment_id'>$answered</a></td>
				" . $link . "
			</tr>
		";
		
		$i++;
	}
?>
</table>
<br>
<?php
	$conn->close();
	createFooter(true, "studentpage.php");
?>
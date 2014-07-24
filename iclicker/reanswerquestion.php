<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: login.php");
	}
	
	// create header without the <div class="main"> tag
	createHead("Reanswer Question", false);
	
	$student_id = getStudentIdFromCookie($conn);
	
	if (isset($_GET["assignment_id"])) {
		$assignment_id = $_GET["assignment_id"];
	} else {
		$assignment_id = "";
	}

	$question_id = $_GET["question_id"];

	$query = "
		SELECT question_name, screen_picture 
		FROM questions 
		WHERE question_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'select' query. " . $conn->error);
	$stmt->bind_param("i", $question_id);
	$stmt->execute() or die("Couldn't execute 'select' query. " . $conn->error);
	$stmt->bind_result($question_name, $screen_picture);	
	$stmt->fetch();
	$stmt->close();
	
	$start_time = time();
	
	$query = "
		SELECT display_screen  
		FROM sections, questions, sessions 
		WHERE 1
		AND questions.session_id = sessions.session_id
		AND sessions.section_id = sections.section_id
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($display_screen);
	$stmt->fetch();
	$stmt->close();
	
	echo "<div ";
	if ($display_screen == 'right'){
		echo "class=rightcrop";
	} else if ($display_screen == 'left'){
		echo "class=leftcrop";
	} else {
		//Do nothing -> full screen
	}
	echo ">";
	
	$section_id = getSectionIdByAssignmentId($conn, $assignment_id);
	
	$query = "
		SELECT min(atq_id)
		FROM assignmentstoquestions 
		WHERE assignment_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'atq_id' query. " . $conn->error);
	$stmt->bind_param("i", $assignment_id);
	$stmt->execute() or die("Couldn't execute 'atq_id' query. " . $conn->error);
	$stmt->bind_result($min_atq_id);
	$stmt->fetch();
	$stmt->close();	
	
	$query = "
		SELECT atq_id
		FROM assignmentstoquestions
		WHERE 1
		AND assignment_id = ? 
		AND question_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'atq_id' query. " . $conn->error);
	$stmt->bind_param("ii", $assignment_id, $question_id);
	$stmt->execute() or die("Couldn't execute 'atq_id' query. " . $conn->error);
	$stmt->bind_result($atq_id);
	$stmt->fetch();
	$stmt->close();	
	
	$query = "
		SELECT rating
		FROM onlineresponses
		WHERE 1
		AND student_id = ? 
		AND question_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'getratings' query. " . $conn->error);
	$stmt->bind_param("ii", $student_id, $question_id);
	$stmt->execute() or die("Couldn't execute 'getratings' query. " . $conn->error);
	$stmt->bind_result($rating1);
	
	$zero = "";
	$one = "";
	$two = "";
	$three = "";
	$four = "";
	$five = "";
	
	$stmt->fetch();
	$rating=$rating1;
	$begin_atq = $atq_id - $min_atq_id + 1;	
?>
	<h1><?="Question #$begin_atq"?></h1>
	<img src="<?="pictures/$section_id/$screen_picture"?>" width="700px" height="500px"/>

	<form action="submitreanswer.php" method="post">
	<fieldset>
	<legend>Select Correct Answer(s)</legend>
	<table>
	<tr>
		<td><input type="checkbox" name="answers[]" value="A">A</td>
		<td></td>
	</tr>
	<tr>
		<td><input type="checkbox" name="answers[]" value="B">B</td>
		<td></td>
	</tr>
	<tr>
		<td><input type="checkbox" name="answers[]" value="C">C</td>
		<td></td>
	</tr>
	<tr>
		<td><input type="checkbox" name="answers[]" value="D">D</td>
		<td></td>
	</tr>
	<tr>
		<td><input type="checkbox" name="answers[]" value="E">E</td>
		<td></td>
	</tr>
	<tr>	
		<td>Question Rating<br></td>
		<td>
		<input id='295' type='radio' name='rating' value='0' <?= $zero ?> ><label for='295'>0</label>
		<input id='296' type='radio' name='rating' value='1' <?= $one ?> ><label for='296'>1</label>
		<input id='297' type='radio' name='rating' value='2' <?= $two ?> ><label for='297'>2</label>
		<input id='298' type='radio' name='rating' value='3' <?= $three ?> ><label for='298'>3</label>
		<input id='299' type='radio' name='rating' value='4' <?= $four ?> ><label for='299'>4</label>
		<input id='300' type='radio' name='rating' value='5' <?= $five ?> ><label for='300'>5</label>
		</td>
	</tr>
	<tr>	
		<td>Feedback (Optional)<br></td>
		<td><input type="text" name="feedback" maxlength="255" size="255"></td>
	</tr>
	
	<tr>
		<td>
			<input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
			<input type="hidden" name="question_id" value="<?= $question_id ?>">
			<input type="hidden" name="start_time" value="<?= $start_time ?>">
		</td>
		<td><input type="submit" value="Submit"></td>
	</tr>
	</table>
	</fieldset>
	</form>
	</p>
	</div>
	</body>
</html>

<?php
	logs($conn, $student_id);
	$conn->close();
?>
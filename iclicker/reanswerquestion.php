<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: login.php");
	}
	
// create header without the <div class="main"> tag
createHead("Question Reanswer");
	
	if (isset($_GET["assignment_id"])) {
		$assignment_id = $_GET["assignment_id"];
	} else {
		$assignment_id = "";
	}

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
	
	$conn->close();

	// $result = $stmt->get_result();
	
	// $row = $result->fetch_array(MYSQLI_ASSOC);
	
	$start_time = time();
	
?>

<h1><?=$question_name?></h1>


	<div class="rightcrop">
	<img src="pictures/<?=$screen_picture?>" width="700px" height="500px"/>

	<form action="submitreanswer.php" method="post">
	<fieldset>
	<legend>Select all correct answers</legend>
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
		<td>
			<input type="hidden" name="assignment_id" value="<?=$assignment_id?>">
			<input type="hidden" name="question_id" value="<?=$question_id?>">
			<input type="hidden" name="start_time" value="<?=$start_time?>">
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

	//createFooter(false);

/*
	if (isset($assignment_id)) {
		createFooter(true, "viewassignment.php?assignment_id=$assignment_id");
	} else {
		createFooter();
	}
*/
?>
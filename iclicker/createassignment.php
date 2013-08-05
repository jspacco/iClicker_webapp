<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Create Assignment");
?>
<script type='text/javascript'>
	$(function() {
		$('#datepicker').datepicker();
	});
</script>
	<table>
		<tr>
			<th>Session Date</th>
			<th>Question #</th>
			<th>Screen Picture</th>
			<th>Chart Picture</th>
			<th>Include in Assignment?</th>
		</tr>
		<form action='endassignment.php' method='post'>
<?php
	$section_id = $_GET['section_id'];
	
	// get all the sessions
	
	$query = "
		SELECT session_id, session_date FROM sessions WHERE section_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'sessions' query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute 'sessions' query. " . $conn->error);
	
	$stmt->bind_result($session_id, $session_date);
	
	$sessions = array();
	
	while ($stmt->fetch()) {
		$sessions[$session_id] = $session_date;
	}
	$stmt->close();
	
	// create entries for each session and its questions
	foreach ($sessions as $session_id => $session_date) {
		echo "
			<tr>
				<td> " . $session_date . "</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		";
		
		$query = "
			SELECT question_id, question_number, screen_picture, chart_picture FROM questions WHERE session_id = ?;
		";
		
		$stmt = $conn->prepare($query) or die("Couldn't prepare 'questions' query. " . $conn->error);
		$stmt->bind_param("i", $session_id);
		$stmt->execute() or die("Couldn't execute 'questions' query. " . $conn->error);
		
		$stmt->bind_result($question_id, $question_number, $screen_picture, $chart_picture);
		
		while ($stmt->fetch()) {
			echo "
				<tr>
					<td></td>
					<td>" . $question_number . "</td>
					<td><a href='pictures/" . $screen_picture . "' title='Picture of screen' data-lightbox='" . $question_id . "'><img src='pictures/" . $screen_picture . "' alt='Picture of screen' width='175' height='100'></td>
					<td><a href='pictures/" . $chart_picture . "' title='Chart of responses' data-lightbox='" . $question_id . "'><img src='pictures/" . $chart_picture . "' alt='Chart of responses' width='175' height='100'></td>
					<td><input type='checkbox' name='questions[]' value='" . $question_id . "'></td>
				</tr>
			";
		}
		$stmt->close();
	}
?>
	<tr>
		<td><input type='hidden' name='section_id' value=<?php echo $section_id; ?>></td>
		<td></td>
		<td></td>
		<td>Date: <input type='text' name='due' id='datepicker' ></td>
		<td><input type='submit' value='Create Assignment'></td>
		</form>
	</tr>
</table>
<?php
	$conn->close();
	createFooter();
?>
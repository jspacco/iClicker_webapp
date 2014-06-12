<?php
require_once("pageutils.php");
require_once("dbutils.php");
require_once("loginutils.php");

$conn = connect();
	
if (!isCookieValidLoginWithType($conn, "admin")) {
	header("Location: home.php");
}
	
$assignment_week = -1;
if (isset($_GET["week"])) {
	$assignment_week = (int) $_GET["week"];
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
			<th>Week / Date</th>
			<th>Question #</th>
			<th>Screen Picture</th>
			<th>Chart Picture</th>
			<th>Include in Assignment?</th>
		</tr>
		<form action='endassignment.php' method='post'>
<?php

	$section_id = $_GET['section_id'];
	
	// Find the minimum date for the session
$stmt = $conn->prepare("SELECT min(session_date) from sessions WHERE section_id = ?") or die("Could not prepare sessions query" . $conn->error);
$stmt->bind_param("i", $section_id);
$stmt->execute() or die("Couldn't execute query to find minimum session_date" . $conn->error);
$stmt->bind_result($first_session_date);
$stmt->fetch();
$stmt->close();

$dayOne=lastSunday($first_session_date);

//echo "first: $first_session_date <br>";

	// get all the sessions
$query = "
		SELECT session_id, session_date FROM sessions WHERE section_id = ? order by session_date asc;
	";
	
$stmt = $conn->prepare($query) or die("Couldn't prepare 'sessions' query. " . $conn->error);
$stmt->bind_param("i", $section_id);
$stmt->execute() or die("Couldn't execute 'sessions' query. " . $conn->error);
$stmt->store_result();
$stmt->bind_result($session_id, $session_date);


$firstWeek=1;
$lastWeek=0;
while ($stmt->fetch()) {
	$currentWeek = currentWeek($session_date, $dayOne);
	//echo "<tr>".td("current week: $currentWeek")."</tr>";
	//echo "<tr>".td("day one: $dayOne")."</tr>";
	if ($firstWeek || $currentWeek>$lastWeek) {
		$firstWeek=0;
		echo "
			<tr>
				<th><a name='week$currentWeek'>'Week' $currentWeek</a></th>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		";
	}
	$lastWeek=$currentWeek;

	if ($currentWeek!=$assignment_week && $assignment_week > -1) {
		continue;
	}

	echo "
<tr>
	<td> $session_date </td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
</tr>
";
			
	$query = "
				SELECT question_id, question_number, screen_picture, chart_picture, ignore_question FROM questions WHERE session_id = ?;
			";
			
	$stmt2 = $conn->prepare($query) or die("Couldn't prepare 'questions' query. " . $conn->error);
	$stmt2->bind_param("i", $session_id);
	$stmt2->execute() or die("Couldn't execute 'questions' query. " . $conn->error);
			
	$stmt2->bind_result($question_id, $question_number, $screen_picture, $chart_picture, $ignore_question);
			
	$gv = false;
	while ($stmt2->fetch()) {
		$checked = "";
		if ($ignore_question == 0) {
			// only check group votes in the week we're creating an assignment for
			if ($gv && $assignment_week > -1 && $assignment_week == $currentWeek) {
				$checked = "checked";
			}
			$gv = !$gv;
		}
				
		echo "
<tr>
	<td></td>
	<td>$question_number</td>
	<td><a href='pictures/$screen_picture' title='Picture of screen' data-lightbox='$question_id'><img src='pictures/$screen_picture' alt='Picture of screen' width='175' height='100'></td>
	<td><a href='pictures/$chart_picture' title='Chart of responses' data-lightbox='$question_id'><img src='pictures/$chart_picture' alt='Chart of responses' width='175' height='100'></td>
<td><input type='checkbox' name='questions[]' value='$question_id' $checked></td>
</tr>
";
	}
	$stmt2->close();
}
$stmt->close();

?>
	<tr>
		<td><input type='hidden' name='section_id' value=<?php echo $section_id; ?>></td>
		<td></td>
		<td>Date: <input type='text' name='due' id='datepicker' ></td>
		<td><input type='text' name='hour' size='2' value='11'> : <input type='text' name='minute' size='2' value='59'></td>
		<td><input type='submit' value='Create Assignment'></td>
		</form>
	</tr>
</table>
<?php
	$conn->close();
	createFooter();
?>
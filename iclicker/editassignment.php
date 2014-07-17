<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Edit Assignment");
	
	$assignment_id = $_GET['assignment_id'];
?>
<script type='text/javascript'>
	$(function() {
		$('#datepicker').datepicker();
	});
</script>
<h1>Edit Assignment</h1>
<?php
	
	$query = "
		SELECT due 
		FROM assignments 
		WHERE assignment_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'assignments' query. " . $conn->error);
	$stmt->bind_param("i", $assignment_id);
	$stmt->execute() or die("Couldn't execute 'assignments' query. " . $conn->error);
	$stmt->bind_result($due);
	$stmt->fetch();
	$stmt->close();

	echo "
		<form action='endeditassignment.php?assignment_id=$assignment_id' method='post'>
			Due Date: <input type='text' name='due' value='$due' id='datepicker' />
			<input type='submit' value='Submit' />
		</form>
	";

	$conn->close();
	createFooter();
?>
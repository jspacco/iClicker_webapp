<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Editing Assignment...");
	
	$assignment_id = $_GET['assignment_id'];
	$due = $_POST['due'];
	
	$query = "
		UPDATE assignments SET due = ? WHERE assignment_id = ?; 
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'update' query. " . $conn->error);
	$stmt->bind_param("si", $due, $assignment_id);
	$stmt->execute() or die("Couldn't execute 'update' query. " . $conn->error);
?>
<p>Assignment edit succesful!</p>
<?php
	$conn->close();
	createFooter(true, "editassignment.php?assignment_id=$assignment_id");
?>
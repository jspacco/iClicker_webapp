<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' type='text/css' href='stylesheet.css'>
</head>
<body>
	<div>
<?php
	require_once("dbutils.php");
	$conn = connect();
	
	$query = "
	UPDATE questions SET ignore_question = 0;
	";
	
	$conn->query($query) or die("Couldn't execute 'unignore' query. " . $conn->error);
	
	if (!isset($_POST["ignore"])) {
		echo "Nothing to ignore.<br>";
	} else {		
		$ignore = $_POST["ignore"];
		$i = 1;
		
		foreach ($ignore as $question_id) {
			$query = "
				UPDATE questions SET ignore_question = ? WHERE
				question_id = ?;
			";
			
			$stmt = $conn->prepare($query) or die("Couldn't prepare 'ignore' query. " . $conn->error);
			$stmt->bind_param("ii", $i, $question_id);
			$stmt->execute() or die("Couldn't execute 'ignore' query. " . $conn->error);
			
			echo "Ignored " . $question_id . ".<br>";
		}
	}
?>
	</div>
</body>
<footer>
	<a href='home.php'>Back to Home</a>
</footer>
</html>
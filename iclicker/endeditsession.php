<?php
require_once("pageutils.php");
require_once("dbutils.php");
require_once("loginutils.php");
$conn = connect();
	
if (!isCookieValidLoginWithType($conn, "admin")) {
	header("Location: home.php");
}

$session_id=$_POST['session_id'];

	$query = "
		UPDATE questions 
		SET ignore_question = 0, single_question = 0
		WHERE session_id = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare update statement" . $conn->error);
	$stmt->bind_param('i', $session_id);
	$stmt->execute() or die("Couldn't execute update statement" . $conn->error);

	// Update ignores		
	if (!isset($_POST["ignore"])) {
		//echo "Nothing to ignore.";
	} else {		
		$ignore = $_POST["ignore"];
		$i = 1;
		
		foreach ($ignore as $question_id) {
			$query = "
				UPDATE questions 
				SET ignore_question = ? 
				WHERE question_id = ?
			";
				
			$stmt = $conn->prepare($query) or die("Couldn't prepare 'ignore' query. " . $conn->error);
			$stmt->bind_param("ii", $i, $question_id);
			$stmt->execute() or die("Couldn't execute 'ignore' query. " . $conn->error);
		}
	}
		
	// Update singles
	if (!isset($_POST["single"])) {
		//echo "Nothing for single.";
	} else {		
		$single = $_POST["single"];
		$i = 1;
			
		foreach ($single as $question_id) {
			$query = "
				UPDATE questions 
				SET single_question = ? 
				WHERE question_id = ?
			";
				
			$stmt = $conn->prepare($query) or die("Couldn't prepare 'single' query. " . $conn->error);
			$stmt->bind_param("ii", $i, $question_id);
			$stmt->execute() or die("Couldn't execute 'single' query. " . $conn->error);
		}
	}

	// Update correct answers
	if (isset($_POST["id"])) {
		//If it's not set, we can't update the answers
		$A = $B = $C = $D = $E = array();
			
		if (isset($_POST["A"]))
			$A = $_POST["A"];
		if (isset($_POST["B"]))
			$B = $_POST["B"];
		if (isset($_POST["C"]))
			$C = $_POST["C"];
		if (isset($_POST["D"]))
			$D = $_POST["D"];
		if (isset($_POST["E"]))
			$E = $_POST["E"];
			
		$ids = $_POST["id"];
			
		foreach ($ids as $id) {
			$answer = "";
				
			if (in_array($id, $A))
				$answer = $answer . 'A,';
			if (in_array($id, $B))
				$answer = $answer . 'B,';
			if (in_array($id, $C))
				$answer = $answer . 'C,';
			if (in_array($id, $D))
				$answer = $answer . 'D,';
			if (in_array($id, $E))
				$answer = $answer . 'E,';
				
			$answer = trim($answer, ",");
							
			$query = "
					UPDATE questions 
					SET correct_answer = ? 
					WHERE question_id = ?
				";
				
			$stmt = $conn->prepare($query) or die("Couldn't prepare 'update' query. " . $conn->error);
			$stmt->bind_param("si", $answer, $id);
			$stmt->execute() or die("Couldn't execute 'update' query. " . $conn->error);
			$stmt->close();
			
		}
		$query = "
			UPDATE sessions 
			SET post_processed = 1 
			WHERE session_id = ?
		";
				
		$stmt = $conn->prepare($query) or die("Couldn't prepare 'update' query for marking session as processed. " . $conn->error);
		$stmt->bind_param("i", $session_id);
		$stmt->execute() or die("Couldn't execute 'update' query for marking session as processed. " . $conn->error);
		$stmt->close();
	} 

	header("Location: session.php?session_id=$_POST[session_id]&message=Updated!");

	$conn->close();
?>
<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();

	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	$wasError = false;
	$response = "Password changed successfully!";
	
	$student_id = getStudentIdFromCookie($conn);	
	$oldpassword = getEncrypted($_POST["oldpassword"]);
	$newpassword = getEncrypted($_POST["newpassword"]);
	
	if ($oldpassword != $_COOKIE["Password"]) {
		$wasError = true;
		$response = "Invalid password, please try again.";
	} else {
		$query = "
			UPDATE students 
			SET password = ? 
			WHERE student_id = ?;
		";
		
		$stmt = $conn->prepare($query) or die("Couldn't prepare 'passwordchange' query. " . $conn->error);
		$stmt->bind_param("si", $newpassword, $student_id);
		$result = $stmt->execute() or die("Couldn't execute 'passwordchange' query. " . $conn->error);
		
		if (!$result) {
			$wasError = true;
			$response = "An error occurred. Please try again.";
		} else {
			// we have to update the cookies password
			setLoginCookie($conn, $_COOKIE["Username"], $_POST["newpassword"], "student");
		}
	}
	
	// if we want we could redirect on successful password change
	if (!$wasError) {
		header("Location: home.php");
	}

	createHeader("Changing Password...");
?>
<?php
	echo $response . "<br>";
?>
<?php
	$conn->close();
	createFooter();
?>
<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	createHeader("End Registration", false);
?>
<?php
	$iclicker = $_POST["iclicker_id"];
	$iclicker_alt = "#" . $iclicker;
	
	$username = $_POST["username"];
	$password = $_POST["password"];
	$password = getEncrypted($password);
	
	$registrationSuccess = false;
	
	if (trim($username) == "") {
		echo "Username cannot be blank.<br>";
	} else {
		
		$query = "
			SELECT distinct student_id FROM students WHERE
			iclicker_id = ? OR
			iclicker_id = ?;
		";
		
		$stmt = $conn->prepare($query) or die("Couldn't prepare 'select' query. " . $conn->error);
		$stmt->bind_param("ss", $iclicker, $iclicker_alt);
		$stmt->execute() or die("Couldn't execute 'select' query. " . $conn->error);
		
		$stmt->store_result();
		
		// $result = $stmt->get_result();
		
		if ($stmt->num_rows > 0) {
			$stmt->bind_result($student_id);
			$stmt->fetch();
			$stmt->close();
			
			// $row = $result->fetch_array(MYSQLI_ASSOC);
			// $student_id = $row["student_id"];
			
			// verify the user hasn't been registered yet
			$query = "
				SELECT username, password FROM students WHERE
				student_id = $student_id;
			";
			
			$result = $conn->query($query) or die("Couldn't execute 'namefree' query. " . $conn->error);
			$row = $result->fetch_array(MYSQLI_ASSOC);
			
			if (trim($row["username"]) == "" && trim($row["password"]) == "") {
				
				// make sure the username isn't taken
				$query = "
					SELECT username FROM students WHERE
					username = ?;
				";
				
				$stmt = $conn->prepare($query) or die("Couldn't prepare 'namecheck' query. " . $conn->error);
				$stmt->bind_param("s", $username);
				$stmt->execute() or die("Couldn't execute 'namecheck' query. " . $conn->error);
				
				$stmt->store_result();
				
				if ($stmt->num_rows == 0) {
					
					$query = "
						UPDATE students SET
						username = ?, password = ? WHERE
						student_id = ?;
					";
					
					$stmt = $conn->prepare($query) or die("Couldn't prepare 'update' query. " . $conn->error);
					$stmt->bind_param("ssi", $username, $password, $student_id);
					$stmt->execute() or die("Couldn't execute 'update' query. " . $conn->error);
					
					echo "Registration successful!.<br>";
					$registrationSuccess = true;
				} else {
					echo "That username has already been taken, please try again.<br>";
				}
			} else {
				echo "That iClicker ID has already been registered. If this an error, please contact your instructor.<br>";
			}
		} else {
			echo "Couldn't find any student record with iClicker ID " . $iclicker . " or " . $iclicker_alt . ".<br>";
		}
	}
?>
<?php
	$conn->close();
	createFooter(!$registrationSuccess, "register.php");
?>
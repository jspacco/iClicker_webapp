<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	createHeader("Reset Password", false);
	
	if (isset($_POST["username"])) {
		// they're resetting their password
		$username = $_POST["username"];
		
		$query = "
			SELECT student_id, email, password
			FROM students
			WHERE username = '$username';
		";
		
		$result = $conn->query($query) or die("Couldn't execute 'username check' query. " . $conn->error);
		
		$arr = mysqli_fetch_assoc($result);
		$student_id = $arr["student_id"];
		$email = $arr["email"];
		$oldpass = $arr["password"];
		
		if ($student_id != NULL) {
			// valid username
			if ($email != "") {
				// valid email
				$newpass = uniqid();
				$encpass = getEncrypted($newpass);
				
				$query = "
					UPDATE students
					SET password = '$encpass'
					WHERE student_id = '$student_id'
				";
				
				$dbret = $conn->query($query);
				
				if ($dbret) {
					// changed password
					$sender = "iclicker@knox.edu";
					$subject = "Password reset";
					
					$message = "
						$username,\r\n
						\r\n
						A password reset was requested on your account, your new password is:\r\n
						\r\n
						$newpass
						\r\n
						Please login and change your password.
					";
					
					$mailret = mail($email, $subject, $message, "From:$sender");
					
					if ($mailret) {
						// mail sent successfully, nothing bad happened!
						echo "Password successfully reset! An email has been sent to the email account for user \"$username\" with more details.<br>";
					} else {
						// mail failed to send
						echo "Mail failed to send, unresetting password.<br>";
						
						$query = "
							UPDATE students
							SET password = '$oldpass'
							WHERE student_id = '$student_id'
						";
						
						$ret = $conn->query($query);
						
						if ($ret) {
							// password reset undone successfully
							echo "Password unreset successfully. Please contact an administrator for help resetting your password.<br>";
						} else {
							// everything that can go wrong has
							echo "Couldn't unreset password. Please contact an administrator immediately.<br>";
						}
					}
				} else {
					// failed to change password
					echo "Couldn't change password. Please contact an administrator.<br>";
				}
			} else {
				// no email set
				echo "The email for user \"$username\" was not set, password cannot be reset. Please contact an administrator if you need help.<br>";
			}
		} else {
			// invalid username
			echo "Couldn't find user \"$username\", please check your spelling or contact an administrator.<br>";
		}
	} else {
		// they're entering their username
		echo "
			<form action='resetpassword.php' method='post'>
				<fieldset>
					<legend>Please enter your username</legend>
					<table>
						<tr>
							<td>Username: <input type='text' name='username'></td>
						</tr>
						<tr>
							<td><input type='submit' value='Reset Password'></td>
						</tr>
					</table>
				</fieldset>
			</form>
		";
	}
?>

<?php
	$conn->close();
	createFooter();
?>
<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHeader("Edit Info");
?>
<script type="text/javascript">
	jQuery(function(){
		$("#changePassword").click(function(){
			$(".error").hide();
			var hasError = false;
			var passwordVal = $("#newpassword").val();
			var checkVal = $("#password-check").val();
			if (passwordVal == '') {
				$("#newpassword").after('<span class="error">Please enter a password.</span>');
				hasError = true;
			} else if (checkVal == '') {
				$("#password-check").after('<span class="error">Please re-enter your password.</span>');
				hasError = true;
			} else if (passwordVal != checkVal ) {
				$("#password-check").after('<span class="error">Passwords do not match.</span>');
				hasError = true;
			}
			if(hasError == true) {return false;}
		});
	});
</script>
<?php
	$query = "
		SELECT student_id, school_id, email 
		FROM students 
		WHERE 1
		AND username = ? 
		AND	password = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't execute 'student_id' query. " . $conn->error);
	$stmt->bind_param("ss", $_COOKIE["Username"], $_COOKIE["Password"]);
	$stmt->execute() or die("Couldn't execute 'student_id' query. " . $conn->error);
	
	$stmt->bind_result($student_id, $school_id, $email);
	$stmt->fetch();
	$stmt->close();
	
	// $result = $stmt->get_result();
	// $row = $result->fetch_array(MYSQLI_ASSOC);
	
	// $student_id = $row["student_id"];
	// $email = $row["email"];
	
?>
<h2>Update Information</h2>
<form action="endstudentedit.php" method="post">
	School ID: <input type='text' name='school_id' value=<?php echo $school_id; ?>><br>
	Email: <input type='text' name='email' value=<?php echo $email; ?>><br>
	Select the course(s) you are enrolled in...<br>
	<table>
		<tr>
			<th>Course</th>
			<th>Section</th>
			<th>Year Offered</th>
		</tr>
	</table>
	<?php
	
		$query = "
			SELECT section_id, year_offered, course_name
			FROM sections, courses
			WHERE sections.course_id = courses.course_id
		";
		
		$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
		//$stmt->bind_param("i", $course_id);
		$stmt->execute() or die("Couldn't execute query. " . $conn->error);
		
		$stmt->bind_result($section_id, $year_offered, $course_name);
		
		$course = "";
		
		//different notepad code
		if ($course_name == 1) {
			$course = "checked";
		}
		while($stmt->fetch()){
			echo "
				<td><input type='checkbox' name='courses[]' value='$course'>$course_name</td><br>
			";
		}
	?>
	
	<!--<td><input type='checkbox' name='courses[]' value='$course_id'$course></td>-->
	
	<input type='submit' value='Update'>
</form>
<h2>Change Password</h2>
<form action="changepassword.php" method="post">
	Old Password: <input type='password' name='oldpassword'><br>
	New Password: <input type='password' name='newpassword' id='newpassword'><br>
	Verify Password: <input type='password' name='password-check' id='password-check'><br>
	<input type='submit' value='Submit'>
</form>
<?php
	$conn->close();
	createFooter();
?>
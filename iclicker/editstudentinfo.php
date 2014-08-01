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
		AND	password = ?
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't execute 'student_id' query. " . $conn->error);
	$stmt->bind_param("ss", $_COOKIE["Username"], $_COOKIE["Password"]);
	$stmt->execute() or die("Couldn't execute 'student_id' query. " . $conn->error);
	$stmt->bind_result($student_id, $school_id, $email);
	$stmt->fetch();
	$stmt->close();
?>
<h2>Update Information</h2>
<form action="endstudentedit.php" method="post">
	School ID: <input type='text' name='school_id' value=<?php echo $school_id; ?>><br>
	Email: <input type='text' name='email' value=<?php echo $email; ?>><br><br>
	Select the course(s) you are enrolled in...<br>
	<div>
	<table class='collection' border=1>
		
	<tr>
		<th>Enrolled?</th>
		<th>Course</th>
		<th>Section</th>
		<th>Year Offered</th>
	</tr>	
<?php
	$query = "
		SELECT section_id, student_id
		FROM registrations
		WHERE student_id = ?
	";
		
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $student_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($section_id, $student_id);
	
	$regsect = array();
	while ($stmt->fetch()){
		array_push($regsect, $section_id);
	};
		
	$stmt->close();
		
	$query = "
		SELECT section_id, course_name, section_number, year_offered
		FROM sections, courses
		WHERE sections.course_id = courses.course_id
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	$stmt->bind_result($section_id, $course_name, $section_number, $year_offered);
			
	while($stmt->fetch()){		
		$sect = "";
		if (in_array($section_id, $regsect)) {
				$sect = "checked";
		}
		
		echo "
			<tr>
				<td><input type='checkbox' name='checkedcourses[]' value='$section_id'$sect></td>
				<input type='hidden' name='allcourses[]' value='$section_id'>
				<td>$course_name</td>
				<td>$section_number</td>
				<td>$year_offered</td>
			</tr>
		";
	}
		
?>
	</table>
	</div>	
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
	logs($conn, $student_id);
	$conn->close();
	createFooter();
?>
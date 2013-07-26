<?php
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();

	$redirect;
	
	if (isCookieValidLogin($conn)) {
		switch ($_COOKIE["LoginType"]) {
			case "admin":
				$redirect = "courses.php";
				break;
			case "student":
				$redirect = "studentpage.php";
				break;
		}
		
		header("Location: " . $redirect);
	}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' type='text/css' href='stylesheet.css'>
</head>
<body>
	<div>
		<h1>Online i>Clicker Questions</h1>
		<p>Login to access the student/administration tools.<br></p>
		<a href="register.html">Student Registration</a><br>
		<form action='endlogin.php' method='post'>
			<table>
				<tr>
					<td>Login as </td>
					<td><input type='radio' name='logintype' value='admin'>Administrator</td>
					<td><input type='radio' name='logintype' value='student' checked>Student</td>
				</tr>
				<tr>
					<td>Username: </td>
					<td><input type='text' name='username'></td>
				</tr>
				<tr>
					<td>Password: </td>
					<td><input type='password' name='password'></td>
				</tr>
				<tr>
					<td></td>
					<td><input type='submit' value='Login'></td>
				</tr>
			</table>
		</form>
	</div>
</body>
<?php
	$conn->close();
?>
</html>
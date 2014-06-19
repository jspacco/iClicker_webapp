<?php
	require_once("pageutils.php");
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
	
	createHeader("Online i<Clicker Questions", false);
?>
<body>
	<div style="text-align: left;">
		<h1>Online i>Clicker Questions</h1>
		<img style="float: right;" src="img/logo.jpg"><br>
		<p>Login to access the student/administration tools.<br></p>
		<form action='endlogin.php' method='post'>
			<table>
				<tr>
					<td>Login as </td>
					<td><input id='349' type='radio' name='logintype' value='admin'><label for='349'>Administrator</label></td>
					<td><input id='350' type='radio' name='logintype' value='student' checked><label for='350'>Student</label></td>
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
	<div>
	<a href="register.php">Click to register your clicker</a> <font color=red> (click here if you have never used this system before) </font>
	</div>
	<div>
		<a href="resetpassword.php">Reset Password</a><br>
	</div>
</body>
<?php
	$conn->close();
?>
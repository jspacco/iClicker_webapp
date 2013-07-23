<!DOCTYPE html>
<html>
<head>
	<link rel='stylesheet' type='text/css' href='stylesheet.css'>
</head>
<body>
	<div>
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
</html>
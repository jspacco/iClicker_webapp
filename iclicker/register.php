<?php
	require_once("pageutils.php");
	createHeader("Register", false, "<script type='text/javascript' src='jquery-1.10.2.min.js'></script>");
?>
	<script type="text/javascript">
		jQuery(function(){
			$("#register").click(function(){
				$(".error").hide();
				var hasError = false;
				var iclicker = $("#iclicker_id").val();
				var username = $("#username").val();
				var passwordVal = $("#password").val();
				var checkVal = $("#password-check").val();
				if (iclicker == '') {
					$("#iclicker_id").after('<span class="error">Please enter an iClicker ID.</span>');
					hasError = true;
				}
				if (username == '') {
					$("#username").after('<span class="error">Please enter a username.</span>');
					hasError = true;
				}
				if (passwordVal == '') {
					$("#password").after('<span class="error">Please enter a password.</span>');
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
	<div>
		<form action="endregister.php" method="post">
		<fieldset>
			<legend>Register</legend>
			<table class="container">
				<tr>
					<td>iClicker ID: </td>
					<td><input type="text" name="iclicker_id" id="iclicker_id"></td>
				</tr>
				<tr>
					<td>Username: </td>
					<td><input type="text" name="username" id="username"></td>
				</tr>
				<tr>
					<td>Password: </td>
					<td><input type="password" name="password" id="password"></td>
				</tr>
				<tr>
					<td>Confirm password: </td>
					<td><input type="password" name="password-check" id="password-check"></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="submit" value="Register" id="register"></td>
				</tr>
			</table>
		</fieldset>
		</form>
	</div>
<?php
	createFooter();
?>
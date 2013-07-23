<?php
	function isCookieValidLogin($conn) {
		return isset($_COOKIE["Username"]) &&
			isset($_COOKIE["Password"]) &&
			isset($_COOKIE["LoginType"]) &&
			isValidLogin($conn, $_COOKIE["Username"], $_COOKIE["Password"], $_COOKIE["LoginType"]);
	}

	function isValidLogin($conn, $user, $pass, $type) {
		switch ($type) {
			case "admin":
				$query = "
				SELECT user_id FROM users WHERE
				username = ? AND
				password = ?;
				";
				
				$stmt = $conn->prepare($query) or die("Couldn't prepare 'login check' query. " . $conn->error);
				$user = strtolower($user);
				$stmt->bind_param("ss", $user, $pass);
				$stmt->execute() or die("Couldn't execute 'login check' query. " . $conn->error);
				
				$result = $stmt->get_result();
				
				return mysqli_num_rows($result) > 0;
			case "student":
			
				return false;
			default:
				return false;
		}
	}
	
	function setLoginCookie($conn, $user, $pass, $type) {
		$password = crypt($pass, 'a8hd9j2');
		
		setcookie("Username", strtolower($user), time() + 3600);
		setcookie("Password", $password, time() + 3600);
		setcookie("LoginType", $type, time() + 3600);
	}
?>
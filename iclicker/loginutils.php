<?php
	function isCookieValidLoginWithType($conn, $type) {
		return isCookieValidLogin($conn) &&
			$_COOKIE["LoginType"] == $type;
	}

	function isCookieValidLogin($conn) {
		return isset($_COOKIE["Username"]) &&
			isset($_COOKIE["Password"]) &&
			isset($_COOKIE["LoginType"]) &&
			isValidLogin($conn, $_COOKIE["Username"], $_COOKIE["Password"], $_COOKIE["LoginType"]);
	}

	function isValidLogin($conn, $user, $pass, $type) {
		$query;
		
		switch ($type) {
			case "admin":
				$query = "
					SELECT user_id FROM users WHERE
					username = ? AND
					password = ?;
				";
				break;
			case "student":
				$query = "
					SELECT student_id FROM students WHERE
					username = ? AND
					password = ?;
				";
				break;
			default:
				echo "Invalid login type.<br>";
				return false;
		}
		$stmt = $conn->prepare($query) or die("Couldn't prepare 'login check' query. " . $conn->error);
		$user = strtolower($user);
		$stmt->bind_param("ss", $user, $pass);
		$stmt->execute() or die("Couldn't execute 'login check' query. " . $conn->error);
		
		$stmt->store_result();
		
		// $result = $stmt->get_result();
		
		return $stmt->num_rows > 0;
	}
	
	function setLoginCookie($conn, $user, $pass, $type) {
		$password = getEncrypted($pass);
		
		setcookie("Username", strtolower($user), time() + 3600);
		setcookie("Password", $password, time() + 3600);
		setcookie("LoginType", $type, time() + 3600);
	}
	
	function clearLogin($conn) {
		setcookie("Username", "", time() - 3600);
		setcookie("Password", "", time() - 3600);
		setcookie("LoginType", "", time() - 3600);
	}
	
	function getEncrypted($pass) {
		return crypt($pass, 'a8hd9j2');
	}
?>
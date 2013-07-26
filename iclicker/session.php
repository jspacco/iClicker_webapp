<?php
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
?>
<html>
<head>
	<link rel='stylesheet' type='text/css' href='stylesheet.css'>	
</head>
<header>
	<a href="logout.php">Logout</a>
</header>
<body>
	<div>
		<h1>
			Questions
		</h1>
		<table>
		<form action='endeditsession.php' method='post'>
			<tr>
				<th>Ignore?</th>
				<th>#</th>
				<th>Type</th>
				<th>Question Picture</th>
				<th>Chart Picture</th>
				<th>Answers</th>
				<th>Compare</th>
			</tr>
<?php
	$session_id = $_GET["session_id"];
	
	$query = "
		SELECT question_id, question_number, screen_picture, chart_picture, ignore_question FROM questions WHERE
		session_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $session_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$result = $stmt->get_result();
	
	$q = 1;
	$num = 1;
	$iv_id;
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		$type = "IV";
		if ($q % 2 === 0) {
			$type = "GV";
		}
		
		echo "<tr>";
		$checked = "";
		if ($row["ignore_question"] == 1) {
			$checked = "checked";
		}
		echo "
			<td><input type='checkbox' name='ignore[]' value='" . $row["question_id"] . "'" . $checked . "></td>
			<td><a href='question.php?question_id=" . $row["question_id"] . "'>Question " . $num . "</a></td>
		";
		
		if ($row["ignore_question"] == 1) {
			echo "
				<td>Ignored</td>
			";
		} else {
			echo "
				<td>" . $type . "</td>
			";
		}
		echo "
			<td><img src='pictures/" . $row["screen_picture"] . "' alt='Picture of screen' width='175' height='100'></td>
			<td><img src='pictures/" . $row["chart_picture"] . "' alt='Chart of responses' width='175' height='100'></td>
			<td>
				<input type='checkbox' name='A[]' value='" . $row["question_id"] . "'>A
				<input type='checkbox' name='B[]' value='" . $row["question_id"] . "'>B
				<input type='checkbox' name='C[]' value='" . $row["question_id"] . "'>C
				<input type='checkbox' name='D[]' value='" . $row["question_id"] . "'>D
				<input type='checkbox' name='E[]' value='" . $row["question_id"] . "'>E
			</td>
		";
		
		if ($row["ignore_question"] != 1) {
			if ($q % 2 === 1) {
				$iv_id = $row["question_id"];
			} else {
				echo "
					<td><a href='compare.php?iv=" . $iv_id . "&gv=" . $row["question_id"] . "'>Compare</a></td>
				";
				$num++;
			}
			$q++;
		}
		echo "</tr>";
	}
?>
		</table>
			<input type='submit' value='Update'>
		</form>
	</div>
</body>
<?php
	$conn->close();
?>
<footer>
	<a href='home.php'>Back to Home</a>
</footer>
</html>